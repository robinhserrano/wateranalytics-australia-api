<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Odoo Host
    | Must start with http:// or https:// and include port
    |--------------------------------------------------------------------------
    */
    'host' => env('ODOO_HOST', ''),

    /*
    |--------------------------------------------------------------------------
    | Database name
    |--------------------------------------------------------------------------
    */
    'database' => env('ODOO_DATABASE', ''),

    /*
    |--------------------------------------------------------------------------
    | User name
    |--------------------------------------------------------------------------
    */
    'username' => env('ODOO_USERNAME', ''),

    /*
    |--------------------------------------------------------------------------
    | User password
    |--------------------------------------------------------------------------
    */
    'password' => env('ODOO_PASSWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | App\Services\OdooClient
    | The same client as waa_filter_subscriptions, which names its settings
    | url/db/login/api_key. Mapped onto the ODOO_* vars above so no .env changes
    | are needed. The password works as the "api key" (same authenticate call).
    | as_objects: return decoded objects ($order->partner_id->id), the shape the
    | sync commands were written against with obuchmann/odoo-jsonrpc.
    |--------------------------------------------------------------------------
    */
    'url' => env('ODOO_HOST', ''),
    'db' => env('ODOO_DATABASE', ''),
    'login' => env('ODOO_USERNAME', ''),
    'api_key' => env('ODOO_PASSWORD', ''),
    'as_objects' => true,
];
