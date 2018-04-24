<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auth config
    |--------------------------------------------------------------------------
    |
    | Here you can specify laradmin auth configs
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | User config
    |--------------------------------------------------------------------------
    |
    | Here you can specify laradmin user configs
    |
    */

    'user' => [
        'default_role' => 'user',
        'model' => 'App\\User',
        'default_avatar' => 'users/default.png'
    ],

    /*
    |--------------------------------------------------------------------------
    | Controllers config
    |--------------------------------------------------------------------------
    |
    | Here you can specify laradmin controller settings
    |
    */

    'controllers' => [
        'namespace' => 'Shemi\\Laradmin\\Http\\Controllers'
    ],

    /*
    |--------------------------------------------------------------------------
    | Models config
    |--------------------------------------------------------------------------
    |
    | Here you can specify default model namespace when creating BREAD.
    | Must include trailing backslashes. If not defined the default application
    | namespace will be used.
    |
    */

    'models' => [
        'namespace' => 'App\\',
    ],

    /*
    |--------------------------------------------------------------------------
    | Path to the laradmin Assets
    |--------------------------------------------------------------------------
    |
    | Here you can specify the location of the laradmin assets path
    |
    */

    'assets_path' => '/vendor/laradmin/assets',

    /*
    |--------------------------------------------------------------------------
    | Storage Config
    |--------------------------------------------------------------------------
    |
    | Here you can specify attributes related to your application file system
    |
    */

    'storage' => [
        'disk' => 'public',
    ],

];