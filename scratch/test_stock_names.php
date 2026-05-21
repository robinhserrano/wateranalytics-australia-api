<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

function callOdooRpc($model, $method, $args = [], $kwargs = []) {
    $host = rtrim(config('odoo.host'), '/');
    $db = config('odoo.database');
    $username = config('odoo.username');
    $password = config('odoo.password');

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

    $uid = $authResponse->json()['result'] ?? null;

    $response = Http::withHeaders(['Content-Type' => 'application/json'])
        ->withoutVerifying()
        ->post("{$host}/jsonrpc", [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'service' => 'object',
                'method' => 'execute_kw',
                'args' => [$db, $uid, $password, $model, $method, $args, $kwargs]
            ],
            'id' => 2
        ]);

    return $response->json()['result'] ?? $response->json();
}

$specification = [
    'id' => (object)[],
    'qty_available' => (object)[],
];

try {
    // Test A: Pass location complete names
    echo "--- Test A: location => ['SYD/Stock', 'PER/Stock'] ---\n";
    $resultA = callOdooRpc('product.product', 'web_search_read', [], [
        'specification' => $specification,
        'offset' => 0,
        'limit' => 2,
        'context' => [
            'bin_size' => true,
            'default_is_storable' => true,
            'location' => ['SYD/Stock', 'PER/Stock']
        ],
        'domain' => [['id', '=', 30]]
    ]);
    print_r($resultA);

    // Test B: Pass location IDs
    echo "\n--- Test B: location => [8, 18] ---\n";
    $resultB = callOdooRpc('product.product', 'web_search_read', [], [
        'specification' => $specification,
        'offset' => 0,
        'limit' => 2,
        'context' => [
            'bin_size' => true,
            'default_is_storable' => true,
            'location' => [8, 18]
        ],
        'domain' => [['id', '=', 30]]
    ]);
    print_r($resultB);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
