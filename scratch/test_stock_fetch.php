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

$specification = [
    'id' => (object)[],
    'display_name' => (object)[],
    'categ_id' => [
        'fields' => [
            'display_name' => (object)[]
        ]
    ],
    'cost_method' => (object)[],
    'avg_cost' => (object)[],
    'total_value' => (object)[],
    'qty_available' => (object)[],
    'free_qty' => (object)[],
    'incoming_qty' => (object)[],
    'outgoing_qty' => (object)[],
    'virtual_available' => (object)[]
];

$domain = [['is_storable', '=', true]];

try {
    // Test 1: Using "location" => ["/stock"] literally
    echo "--- Test 1: location => ['/stock'] ---\n";
    $context1 = [
        'lang' => 'en_US',
        'tz' => 'Australia/Sydney',
        'bin_size' => true,
        'default_is_storable' => true,
        'location' => ['/stock']
    ];
    $result1 = callOdooRpc('product.product', 'web_search_read', [], [
        'specification' => $specification,
        'offset' => 0,
        'limit' => 3,
        'order' => 'name asc',
        'context' => $context1,
        'domain' => $domain
    ]);
    print_r($result1);

    // Test 2: Using warehouse => 1 (Sydney)
    echo "\n--- Test 2: warehouse => 1 (Sydney) ---\n";
    $context2 = [
        'lang' => 'en_US',
        'tz' => 'Australia/Sydney',
        'bin_size' => true,
        'default_is_storable' => true,
        'warehouse' => 1
    ];
    $result2 = callOdooRpc('product.product', 'web_search_read', [], [
        'specification' => $specification,
        'offset' => 0,
        'limit' => 3,
        'order' => 'name asc',
        'context' => $context2,
        'domain' => $domain
    ]);
    print_r($result2);

    // Test 3: Using location => [8, 18, 24, 64] (Active stock location IDs: SYD, PER, BNE, MMAIN)
    echo "\n--- Test 3: location => [8, 18, 24, 64] ---\n";
    $context3 = [
        'lang' => 'en_US',
        'tz' => 'Australia/Sydney',
        'bin_size' => true,
        'default_is_storable' => true,
        'location' => [8, 18, 24, 64]
    ];
    $result3 = callOdooRpc('product.product', 'web_search_read', [], [
        'specification' => $specification,
        'offset' => 0,
        'limit' => 3,
        'order' => 'name asc',
        'context' => $context3,
        'domain' => $domain
    ]);
    print_r($result3);

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
