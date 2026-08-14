<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDF Lock Password
    |--------------------------------------------------------------------------
    |
    | Default password used for PDF lock operations. Overridable per-request
    | via FILE_LOCK_PASSWORD env variable.
    |
    */

    'lock_password' => env('FILE_LOCK_PASSWORD', 'secret123'),

];
