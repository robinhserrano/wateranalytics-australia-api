<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Set by odoo:reconcile-sales when an order no longer exists in Odoo (deleted); cleared again by the
    // sales sync if it ever comes back into scope.
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->timestamp('odoo_missing_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('odoo_missing_at');
        });
    }
};
