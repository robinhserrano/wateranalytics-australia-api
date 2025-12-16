<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionApproval extends Model
{
    protected $fillable = [
        'commission_calculation_id',
        'approver_id',
        'action',
        'notes',
    ];

    /**
     * Get the commission calculation that owns the approval record.
     */
    public function commissionCalculation(): BelongsTo
    {
        return $this->belongsTo(CommissionCalculation::class);
    }

    /**
     * Get the user who performed the approval action.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
