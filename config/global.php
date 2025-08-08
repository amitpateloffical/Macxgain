<?php
$domain = isset($_SERVER['SERVER_NAME'])? $_SERVER['SERVER_NAME'] : '';
$path=str_replace('.','_', $domain);
return [
    'linkexpirationduration' =>24,
    'APP_URL' => trim(env('APP_URL') ?? ''),
    'UPLOAD_FOLDER' => trim(env('UPLOAD_FOLDER') ?? ''),

    'productUpload'=>  trim(base_path() ?? '').'/../'.env('UPLOAD_FOLDER').'/product/',
    'productImageGet'=> trim(env('APP_URL') ?? '').'/'.env('UPLOAD_FOLDER').'/product/',

    
];
