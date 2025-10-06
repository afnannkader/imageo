<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Author Information
    |--------------------------------------------------------------------------
    |
    | Set the details of the package author.
    |
     */

    'author' => [
        'name' => 'Vironeer',
        'email' => 'support@vironeer.com',
        'website' => 'https://vironeer.com',
        'profile' => 'https://codecanyon.net/user/vironeer',
    ],

    /*
    |--------------------------------------------------------------------------
    | Item Information
    |--------------------------------------------------------------------------
    |
    | Define information about the package item.
    |
     */

    'item' => [
        'alias' => 'imgurai',
        'version' => '1.7',
    ],

    /*
    |--------------------------------------------------------------------------
    | Demo Mode
    |--------------------------------------------------------------------------
    |
    | Enable or disable the system demo mode.
    |
     */

    'demo_mode' => env('DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | License Information
    |--------------------------------------------------------------------------
    |
    | Set the API endpoint and type for license validation.
    |
     */

    'license' => [
        'api' => 'http://license.vironeer.com/api/v1/license',
        'type' => env('LICENSE_TYPE', 1),
    ],

    /*
    |--------------------------------------------------------------------------
    | Installation Settings
    |--------------------------------------------------------------------------
    |
    | Configure various installation settings.
    |
     */

    'install' => [
        'requirements' => env('VR_REQUIREMENTS', false),
        'file_permissions' => env('VR_FILEPERMISSIONS', false),
        'license' => env('VR_LICENSE', false),
        'database_info' => env('VR_DATABASEINFO', false),
        'database_import' => env('VR_DATABASEIMPORT', false),
        'complete' => env('VR_COMPLETE', false),
    ],
];
