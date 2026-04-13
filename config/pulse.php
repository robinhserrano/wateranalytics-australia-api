<?php

use Laravel\Pulse\Http\Middleware\Authorize;

return [

    /*
    |--------------------------------------------------------------------------
    | Pulse Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Pulse will be available from. If the domain
    | is set to "null", Pulse will be available from the root domain.
    |
    */

    'domain' => env('PULSE_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Pulse Path
    |--------------------------------------------------------------------------
    |
    | This is the path where Pulse will be available from. Feel free to
    | change this path to anything you like.
    |
    */

    'path' => env('PULSE_PATH', 'pulse'),

    /*
    |--------------------------------------------------------------------------
    | Pulse Enabled
    |--------------------------------------------------------------------------
    |
    | This value determines if Pulse is enabled. When disabled, Pulse will
    | not record any data or be available to the application.
    |
    */

    'enabled' => env('PULSE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Pulse Storage
    |--------------------------------------------------------------------------
    |
    | Pulse stores all of its data in a database connection. This is the 
    | connection that will be used. User requested Redis.
    |
    */

    'storage' => [
        'database' => [
            'connection' => env('PULSE_DB_CONNECTION', 'redis'),
            'chunk' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Pulse route, giving you
    | the chance to add your own middleware to this list or change any
    | of the existing middleware.
    |
    */

    'middleware' => [
        'web',
        Authorize::class,
    ],

];
