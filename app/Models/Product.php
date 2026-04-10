<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'list_price' => 'decimal:2',
        'qty_available' => 'decimal:2',
        'product_properties' => 'array',
        'write_date' => 'datetime',
    ];

    public function landingPrices(): HasMany
    {
        return $this->hasMany(LandingPrice::class);
    }
}
