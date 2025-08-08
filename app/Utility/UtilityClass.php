<?php

namespace App\Utility;

use File;
use Intervention\Image\Facades\Image;
use Str;

class UtilityClass
{
    ///*---------------------------------------------------------------
    /* Utility Facade                                                  |
    /* This Facade is use for all application to make common function  |
    /*  use Helper; Helper::yourfunctionName();                        |                                          |
    /*-----------------------------------------------------------------|
    */
    public function checkBase64Image($base64)
    {
        $base64 = explode('/', $base64);
        if ($base64[0] == 'data:image') {
            return true;
        }
        
        return false;
    }
    
    public function documentUpload($document, $id, $type, $location = null, $getPath = 0)
    {
        
        switch ($type) {
            case 'tabPhoto':
                $location = config('global.tabPhotoUpload').$id.'/files';
                break;
            case 'visit_report_upload':
                $location = config('global.visit_report_upload').$id.'/files';
                break;
        }
        if ($getPath) {
            return $location;
        }
        if (! File::exists($location)) {
            File::makeDirectory($location, 0755, true);
        }
        $docFile = explode(',', $document);

        if (isset($docFile[1]) && base64_encode(base64_decode($docFile[1], true)) === $docFile[1]) {
            $decodedContent = base64_decode($docFile[1]);
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($decodedContent);
            $mimeToExtension = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'application/pdf' => 'pdf',
            ];
            $extension = isset($mimeToExtension[$mimeType]) ? $mimeToExtension[$mimeType] : 'txt';
            $filename = Str::random(20).'-'.time().'.'.$extension;
            $path = File::put($location.'/'.$filename, $decodedContent);
            //   file_put_contents($location, base64_decode($docFile['document']['base64']));
        } else {
            $filenameWithExt = $document->getClientOriginalName();
            $extension = $document->getClientOriginalExtension();
            $filename = Str::random(20).'-'.time().'.'.$extension;
            $path = $document->move($location, $filename);
        }

        return $filename;
    }

    public function arraySortByColumn(&$arr, $col, $dir = SORT_ASC)
    {
        $sort_col = [];
        foreach ($arr as $key => $row) {
            $sort_col[$key] = $row[$col];
        }
        array_multisort($sort_col, $dir, $arr);

        return $arr;
    }
}
