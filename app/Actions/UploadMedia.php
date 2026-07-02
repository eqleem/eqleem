<?php

namespace App\Actions;

use App\Models\Block;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;

class UploadMedia
{
    public function upload(): JsonResponse
    {
        $modelType = request()->input('modelType') ?? request()->header('modelType');
        $modelId = request()->input('modelId') ?? request()->header('modelId');
        $mediaCollection = request()->input('mediaCollection')
            ?? request()->header('mediaCollection')
            ?? 'editor-images';

        $file = request()->file('file') ?? request()->file('upload');

        if (! $file instanceof UploadedFile) {
            return $this->error('لم يتم إرسال ملف.');
        }

        if (! filled($modelId)) {
            return $this->error('معرّف النموذج مطلوب.');
        }

        $maxFileSizeKb = (int) (config('media-library.max_file_size') / 1024);

        request()->validate([
            'file' => ['nullable', 'image', 'max:'.$maxFileSizeKb],
            'upload' => ['nullable', 'image', 'max:'.$maxFileSizeKb],
        ]);

        $model = $this->resolveModel(
            modelType: (string) $modelType,
            modelId: (int) $modelId,
        );

        if (! $model instanceof HasMedia) {
            return $this->error('نوع النموذج غير مدعوم.');
        }

        $media = $model
            ->addMedia($file)
            ->usingFileName(md5($file->getClientOriginalName()).'.'.$file->getClientOriginalExtension())
            ->toMediaCollection($mediaCollection);

        if ($model instanceof Tenant && $mediaCollection === 'logo') {
            $model->meta->set('logo', [
                'media_id' => $media->id,
                'path' => $media->getPath(),
                'disk' => $media->disk,
            ]);
            $model->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'تم رفع الملف بنجاح.',
            'mediaId' => $media->id,
            'url' => $media->getUrl(),
            'file' => [
                'url' => $media->getUrl(),
            ],
        ]);
    }

    private function resolveModel(string $modelType, int $modelId): ?Model
    {
        $modelType = match ($modelType) {
            'entry' => 'content',
            default => $modelType,
        };

        return match ($modelType) {
            'tenant' => Tenant::query()->findOrFail($modelId),
            'block' => Block::query()->findOrFail($modelId),
            'content' => Content::query()->findOrFail($modelId),
            'user' => User::query()->findOrFail($modelId),
            default => null,
        };
    }

    private function error(string $message): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $message,
            ],
        ], 422);
    }
}
