<?php

use Illuminate\Support\Facades\Route;
use App\Models\SalesOrder;

Route::get('/debug-order-lines/{id}', function ($id) {
    $order = SalesOrder::with(['lines.product', 'lines.landingPrice'])->findOrFail($id);
    
    return $order->lines->map(function ($line) {
        return [
            'id' => $line->id,
            'product_name' => $line->product_name ?? $line->product?->name,
            'product_id' => $line->product_id,
            'price_subtotal' => $line->price_subtotal,
            'is_installation_service' => $line->is_installation_service,
            'is_supply_only' => $line->is_supply_only,
            'has_landing_price_relation' => (bool) $line->landingPrice,
            'product_has_landing_prices' => $line->product ? $line->product->landingPrices()->exists() : false,
            'treated_as_additional_cost' => 
                !$line->landingPrice && 
                !($line->product && $line->product->landingPrices()->exists()) &&
                !$line->is_installation_service && 
                !$line->is_supply_only,
        ];
    });
});
