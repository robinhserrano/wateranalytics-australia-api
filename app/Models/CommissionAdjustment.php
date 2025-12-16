<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionAdjustment extends Model
{
    protected $fillable = [
        'commission_calculation_id',
        'adjusted_by',
        'adjustment_amount',
        'reason',
    ];

    protected $casts = [
        'adjustment_amount' => 'decimal:2',
    ];

    /**
     * Get the commission calculation that owns the adjustment.
     */
    public function commissionCalculation(): BelongsTo
    {
        return $this->belongsTo(CommissionCalculation::class);
    }

    /**
     * Get the user who made the adjustment.
     */
    public function adjuster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
