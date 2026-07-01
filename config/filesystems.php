<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'spaces'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],


        'spaces' => [
            'driver' => 's3',
            'key' => env('SPACES_KEY'),
            'secret' => env('SPACES_SECRET'),
            'endpoint' => env('SPACES_ENDPOINT'),
            // Public CDN or R2 custom domain — used by Storage::url() for readable assets.
            'url' => env('SPACES_URL'),
            // Laravel uses `root`, not `folder`, as the bucket key prefix for S3-compatible disks.
            'root' => env('SPACES_ROOT'),
            // Cloudflare R2 typically uses `auto`; use the value from your provider if required.
            'region' => env('SPACES_REGION', 'auto'),
            'bucket' => env('SPACES_BUCKET'),
            'visibility' => 'public',
            'use_path_style_endpoint' => env('SPACES_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

       // Cloudflare R2 S3: use region `auto` for SigV4 (Cloudflare aws-sdk-php example). The
        // R2 API token must allow this bucket (not only the main app bucket), or ListObjects returns 403.
        'spaces-backup' => [
            'driver' => 's3',
            'key' => env('SPACES_BACKUP_KEY'),
            'secret' => env('SPACES_BACKUP_SECRET'),
            'endpoint' => env('SPACES_BACKUP_ENDPOINT'),
            'bucket' => env('SPACES_BACKUP_BUCKET'),
            'region' => env('SPACES_BACKUP_REGION', 'auto'),
            'use_path_style_endpoint' => env('SPACES_BACKUP_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],

        
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
