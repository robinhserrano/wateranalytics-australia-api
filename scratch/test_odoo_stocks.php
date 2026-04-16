<?php

use Obuchmann\OdooJsonRpc\Odoo;
use Illuminate\Support\Facades\Config;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$odoo = app(Odoo::class);

echo "Testing Odoo Connection...\n";

try {
    $version = $odoo->version();
    echo "Connected! Odoo Version: " . $version['server_version'] . "\n";

    echo "Attempting to fetch product counts...\n";
    $count = $odoo->model('product.product')
        ->where('is_storable', '=', true)
        ->count();
    echo "Total storable products: $count\n";

    echo "Attempting web_search_read on product.product...\n";
    
    $fields = [
        'id', 'display_name', 'categ_id', 'qty_available', 'free_qty', 
        'incoming_qty', 'outgoing_qty', 'virtual_available', 'standard_price'
    ];
    $specification = [];
    foreach ($fields as $field) $specification[$field] = (object)[];

    $response = $odoo->executeKw('product.product', 'web_search_read', [
        [['is_storable', '=', true]],
        $specification,
        0,
        5,
        'display_name asc'
    ]);

    echo "Success! Records found: " . count($response['records'] ?? []) . "\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    if (method_exists($e, 'getResponse')) {
        echo "RESPONSE: " . $e->getResponse()->getBody()->getContents() . "\n";
    }
}
