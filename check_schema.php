<?php

use Illuminate\Support\Facades\Schema;

echo "odoo_id type: " . Schema::getColumnType('sales_orders', 'odoo_id') . "\n";
echo "tax_totals type: " . Schema::getColumnType('sales_orders', 'tax_totals') . "\n";
echo "amount_total type: " . Schema::getColumnType('sales_orders', 'amount_total') . "\n";
