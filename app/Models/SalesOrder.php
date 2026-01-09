<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $guarded = [];

    protected $casts = [
        'create_date' => 'datetime',
        'tag_ids' => 'array',
        'order_line' => 'array',
        'has_commission_calculation' => 'boolean',
        'x_studio_commission_paid' => 'boolean',
        'legacy_confirmed_by_manager' => 'boolean',
        'legacy_is_entered_odoo' => 'boolean',
    ];
    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class);
    }

    /**
     * Get the commission calculation for this sales order.
     */
    public function commissionCalculation()
    {
        return $this->hasOne(CommissionCalculation::class);
    }
}
