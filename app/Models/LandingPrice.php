<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandingPrice extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'internal_reference',
        'product_category',
        'installation_service',
        'supply_only',
        'effective_from',
    ];

    protected $casts = [
        'installation_service' => 'decimal:2',
        'supply_only' => 'decimal:2',
        'effective_from' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
