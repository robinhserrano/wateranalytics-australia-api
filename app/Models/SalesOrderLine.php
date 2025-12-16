<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrderLine extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_installation_service' => 'boolean',
        'is_supply_only' => 'boolean',
        'tax_exclusive_amount' => 'decimal:2',
        'product_uom_qty' => 'decimal:2',
        'price_unit' => 'decimal:2',
        'price_subtotal' => 'decimal:2',
        'price_total' => 'decimal:2',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the product for this order line.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the landing price for this order line.
     */
    public function landingPrice()
    {
        return $this->belongsTo(LandingPrice::class);
    }
}
