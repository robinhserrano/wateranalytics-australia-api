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
    'display_name' => (object)[],
    'categ_id' => [
        'fields' => [
            'display_name' => (object)[]
        ]
    ]
];

try {
    // Query filtering by category name "Taps"
    echo "--- Query with category domain: ['categ_id.name', 'in', ['Taps']] ---\n";
    $result = callOdooRpc('product.product', 'web_search_read', [], [
        'specification' => $specification,
        'offset' => 0,
        'limit' => 5,
        'context' => [
            'bin_size' => true,
            'default_is_storable' => true
        ],
        'domain' => [
            ['is_storable', '=', true],
            ['categ_id.name', 'in', ['Taps']]
        ]
    ]);
    print_r($result);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
