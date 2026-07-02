<?php

namespace App\Services;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class MediaPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $local = config('app.env') == 'local' ? 'local/' : null;
        $modelFolder = \Str::of(class_basename($media->model_type))->plural()->lower();

        return $local.'tenant-media/tenant-'.$media->tenant_id.'/'.$modelFolder.'/'.$media->collection_name.'/'.$media->model_id.'/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'c/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'/cri/';
    }
}
