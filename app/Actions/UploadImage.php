<?php

namespace App\Actions;

use App\Support\UploadedRequestFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UploadImage
{
    public function upload(): JsonResponse
    {
        $mediaCollection = request()->input('mediaCollection')
            ?? request()->header('mediaCollection')
            ?? 'tenant-media/'.(tenant('uuid') ?? 'shared').'/uploads';

        $file = UploadedRequestFile::resolve();

        if (! $file) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم إرسال ملف.',
            ], 422);
        }

        $filePath = $file->storePublicly($mediaCollection, 'spaces');
        $url = Storage::disk('spaces')->url($filePath);

        return response()->json([
            'success' => true,
            'message' => 'تم رفع الملف بنجاح .',
            'file' => [
                'url' => $url,
            ],
            'url' => $url,
            'filePath' => $filePath,
        ]);
    }
}
