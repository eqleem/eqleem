<?php

namespace App\Actions;

use Catalog\Media\Models\Media;
use App\Models\Tenant;
use App\Models\Page;
use App\Models\User;
use App\Models\Block;
use App\Models\Post;
 

class UploadImage
{
    public function upload()
    {
        $modelType = request()->modelType ?: request()->headers->get('modelType');
        $modelId = request()->modelId ?: request()->headers->get('modelId');
        $mediaCollection = request()->mediaCollection ?: request()->headers->get('mediaCollection');
        $tenantId = request()->tenantId ?: request()->headers->get('tenantId');
 
        if (request()->file('file')) {
            $file = request()->file('file');
        } else {
            $file = request()->file('upload');
        }
  
        $filePath = $file->store($mediaCollection);
        
        return response()->json([
            'success' => true,
            'message' => 'تم رفع الملف بنجاح .',
            'file' => [
                'url' =>  $filePath ,  
            ],
            'url' =>  $filePath ,
            'filePath' => $filePath ,
        ]);
 
    }
}
