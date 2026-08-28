<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Superadmin seed credentials
    |--------------------------------------------------------------------------
    |
    | Used by SuperadminSeeder to create the platform owner during local
    | development. In production create the account with the artisan
    | command "superadmin:create" instead of storing the password here.
    |
    */

    'name' => env('SUPERADMIN_NAME', 'Super Admin'),
    'username' => env('SUPERADMIN_USERNAME', 'superadmin'),
    'email' => env('SUPERADMIN_EMAIL'),
    'password' => env('SUPERADMIN_PASSWORD'),

];
