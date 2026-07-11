<?php

namespace App\API\Menu;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Menu\Concerns\ResolvesMenuItem;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Uploads an inline editor image for a menu item (CKEditor).
 */
class UploadMenuEditorImage
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesMenuItem;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:60,1',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxFileSizeKb = (int) (config('media-library.max_file_size') / 1024);

        return [
            'upload' => ['nullable', 'image', 'max:'.$maxFileSizeKb],
            'file' => ['nullable', 'image', 'max:'.$maxFileSizeKb],
        ];
    }

    public function handle(Tenant $tenant, string $uuid, UploadedFile $file): array
    {
        setCurrentTenant($tenant);

        $content = $this->findMenuItem($uuid);

        $media = $content
            ->addMedia($file)
            ->usingFileName(md5($file->getClientOriginalName()).'.'.$file->getClientOriginalExtension())
            ->toMediaCollection('editor-images');

        return [
            'url' => $media->getUrl(),
            'mediaId' => $media->id,
            'file' => [
                'url' => $media->getUrl(),
            ],
        ];
    }

    public function asController(ActionRequest $request, string $uuid): JsonResponse
    {
        $tenant = $this->currentDashboardTenant($request);

        $file = $request->file('upload') ?? $request->file('file');

        if (! $file instanceof UploadedFile) {
            return response()->json([
                'error' => [
                    'message' => 'لم يتم إرسال ملف.',
                ],
            ], 422);
        }

        $result = $this->handle($tenant, $uuid, $file);

        return response()->json($result);
    }
}
