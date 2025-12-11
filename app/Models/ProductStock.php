<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'avg_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
        'qty_available' => 'decimal:2',
        'free_qty' => 'decimal:2',
        'incoming_qty' => 'decimal:2',
        'outgoing_qty' => 'decimal:2',
        'virtual_available' => 'decimal:2',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }
}
