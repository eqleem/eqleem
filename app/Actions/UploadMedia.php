<?php

namespace App\Actions;

use Catalog\Media\Models\Media;
use App\Models\Tenant;
use App\Models\Page;
use App\Models\User;
use App\Models\Block;
use App\Models\Post;
 

class UploadMedia
{
    public function upload()
    {
      

        $modelType = request()->modelType ?: request()->headers->get('modelType');
        $modelId = request()->modelId ?: request()->headers->get('modelId');
        $mediaCollection = request()->mediaCollection ?: request()->headers->get('mediaCollection');
        // $tenantId = request()->tenantId ?: request()->headers->get('tenantId');
        $tenantId = tenant('id');
 
        if (request()->file('file')) {
            $file = request()->file('file');
        } else {
            $file = request()->file('upload');
        }
 
        if($modelType == 'tenant'){
            $model = Tenant::whereId($modelId)->firstOrFail();
        }
  
        if($modelType == 'block'){
            $model = Block::whereId($modelId)->firstOrFail();
        }
   
        $media = $model->addMedia(
            file: $file->getRealPath(),
            collectionName: $mediaCollection,
            name: md5($file->getClientOriginalName()),
            collectionGroup: 'tenant-'.$tenantId,
        );

        if($modelType == 'tenant'){
            $model->meta->logo = [
                'media_id' => $media->id,
                'path' => $media->getPath(),
                'disk' => $media->disk,
            ];
            $model->save();  
        }

        if($modelType == 'user' && $mediaCollection == 'logo'){
            $model->meta->logo = [
                'media_id' => $media->id,
                'path' => $media->getPath(),
                'disk' => $media->disk,
            ];
            $model->save();  
        }
  
        // $media = $model->addMedia($file)
        //     ->preservingOriginal()
        //     ->usingFileName(md5($file->getClientOriginalName()).'.'.$file->getClientOriginalExtension())
        //     ->toMediaCollection($mediaCollection);

        return response()->json([
            'success' => true,
            'message' => 'تم رفع الملف بنجاح .',
            'mediaId' =>  $media->id,
            'file' => [
                'url' =>  $media->getUrl() ,  
            ],
            'url' =>  $media->getUrl() ,
            'filePath' =>  $media->getUrl() ,
        ]);
 
    }
}
