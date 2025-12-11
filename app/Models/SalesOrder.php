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
        'tax_totals' => 'array',
    ];
    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class);
    }
}
