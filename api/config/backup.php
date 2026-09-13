<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MySQL binaries
    |--------------------------------------------------------------------------
    |
    | The dump and the restore shell out to mysqldump and mysql. On a server
    | both are on the PATH; on Windows they are inside Laragon, so an empty
    | value falls back to autodetection and then to the bare command name.
    |
    */

    'mysqldump' => env('MYSQLDUMP_PATH'),

    'mysql' => env('MYSQL_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Seconds a dump or a restore may take before the process is killed. A big
    | database on a slow disk needs more than the default of a web request.
    |
    */

    'timeout' => (int) env('BACKUP_TIMEOUT', 600),

    /*
    |--------------------------------------------------------------------------
    | Upload limit
    |--------------------------------------------------------------------------
    |
    | Kilobytes, the unit Laravel's `max` rule uses. PHP and nginx have their
    | own limits (upload_max_filesize, post_max_size, client_max_body_size):
    | raising this one alone is not enough.
    |
    */

    'max_upload' => (int) env('BACKUP_MAX_UPLOAD', 512000),

    /*
    |--------------------------------------------------------------------------
    | Directories in the file archive
    |--------------------------------------------------------------------------
    |
    | Relative to the public disk. `media` holds every store's images and
    | `platform` the two logos of the SaaS. Anything outside this list is not
    | backed up, and is not touched when an archive is restored.
    |
    */

    'directories' => [
        'media',
        'platform',
    ],

];
