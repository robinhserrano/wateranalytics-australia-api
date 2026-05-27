<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    protected $guarded = [];

    protected $casts = [
        'create_date' => 'datetime',
        'write_date' => 'datetime',
        'start_date' => 'date',
        'next_invoice_date' => 'date',
        'end_date' => 'date',
        'installation_date' => 'date',
        'tag_ids' => 'array',
        'order_line' => 'array',
        'is_subscription' => 'boolean',
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

    /**
     * Get the contact/customer for this sales order.
     */
    public function partner()
    {
        return $this->belongsTo(Contact::class, 'partner_id', 'odoo_id');
    }

    /**
     * Get the installer as an object.
     */
    public function getInstallerAttribute()
    {
        if ($this->installer_id) {
            return [
                'id' => $this->installer_id,
                'display_name' => $this->installer_name,
            ];
        }
        return null;
    }
}
