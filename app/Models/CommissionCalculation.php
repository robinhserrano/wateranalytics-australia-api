<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionCalculation extends Model
{
    protected $fillable = [
        'sales_order_id',
        'user_id',
        'sales_manager_id',
        'sales_source',
        'payment_type',
        'selling_price',
        'additional_cost',
        'landing_price',
        'profit',
        'base_commission',
        'extra_commission',
        'manual_adjustment',
        'final_commission',
        'is_special_product',
        'status',
        'confirmed_by_manager',
        'entered_to_odoo',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'paid_at',
        'calculation_metadata',
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'additional_cost' => 'decimal:2',
        'landing_price' => 'decimal:2',
        'profit' => 'decimal:2',
        'base_commission' => 'decimal:2',
        'extra_commission' => 'decimal:2',
        'manual_adjustment' => 'decimal:2',
        'final_commission' => 'decimal:2',
        'is_special_product' => 'boolean',
        'confirmed_by_manager' => 'boolean',
        'entered_to_odoo' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'paid_at' => 'datetime',
        'calculation_metadata' => 'array',
    ];

    /**
     * Get the sales order that owns the commission calculation.
     */
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    /**
     * Get the user (salesperson) that owns the commission calculation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sales manager for this commission calculation.
     */
    public function salesManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sales_manager_id');
    }

    /**
     * Get the user who approved this commission.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected this commission.
     */
    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Get all adjustments for this commission calculation.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(CommissionAdjustment::class);
    }

    /**
     * Get all approval records for this commission calculation.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(CommissionApproval::class);
    }

    /**
     * Scope a query to only include pending commissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved commissions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include paid commissions.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to filter by sales source.
     */
    public function scopeBySalesSource($query, string $source)
    {
        return $query->where('sales_source', $source);
    }
}
