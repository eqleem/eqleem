<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class UploadedRequestFile
{
    public static function resolve(): ?UploadedFile
    {
        $file = request()->file('file') ?? request()->file('upload');

        return $file instanceof UploadedFile ? $file : null;
    }
}
