<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

function callOdooRpc($model, $method, $args = [], $kwargs = []) {
    $host = rtrim(config('odoo.host'), '/');
    $db = config('odoo.database');
    $username = config('odoo.username');
    $password = config('odoo.password');

    // Authenticate
    $authResponse = Http::withHeaders(['Content-Type' => 'application/json'])
        ->withoutVerifying()
        ->post("{$host}/jsonrpc", [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'service' => 'common',
                'method' => 'authenticate',
                'args' => [$db, $username, $password, []]
            ],
            'id' => 1
        ]);

    $result = $authResponse->json();
    $uid = $result['result'] ?? null;

    if (!$uid) {
        throw new \Exception("Auth failed: " . json_encode($result));
    }

    // Call object
    $response = Http::withHeaders(['Content-Type' => 'application/json'])
        ->withoutVerifying()
        ->post("{$host}/jsonrpc", [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'service' => 'object',
                'method' => 'execute_kw',
                'args' => [
                    $db,
                    $uid,
                    $password,
                    $model,
                    $method,
                    $args,
                    $kwargs
                ]
            ],
            'id' => 2
        ]);

    return $response->json()['result'] ?? $response->json();
}

try {
    echo "--- Warehouses (stock.warehouse) ---\n";
    $warehouses = callOdooRpc('stock.warehouse', 'search_read', [[]], [
        'fields' => ['id', 'name', 'code', 'lot_stock_id', 'view_location_id']
    ]);
    print_r($warehouses);

    echo "\n--- Stock Locations (stock.location) ---\n";
    // Let's search for internal locations that have "Stock" in their name or path
    $locations = callOdooRpc('stock.location', 'search_read', [
        [['usage', '=', 'internal']]
    ], [
        'fields' => ['id', 'name', 'complete_name', 'parent_path'],
        'limit' => 50
    ]);
    print_r($locations);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
