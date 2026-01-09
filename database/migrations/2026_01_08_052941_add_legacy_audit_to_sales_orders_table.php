<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_last_confirmed_by')->nullable()->after('commission_calculation_id');
            $table->unsignedBigInteger('legacy_last_entered_odoo_by')->nullable()->after('legacy_last_confirmed_by');
            $table->unsignedBigInteger('legacy_last_manual_add_by')->nullable()->after('legacy_last_entered_odoo_by');
            $table->boolean('legacy_confirmed_by_manager')->nullable()->after('legacy_last_manual_add_by');
            $table->boolean('legacy_is_entered_odoo')->nullable()->after('legacy_confirmed_by_manager');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'legacy_last_confirmed_by',
                'legacy_last_entered_odoo_by',
                'legacy_last_manual_add_by',
                'legacy_confirmed_by_manager',
                'legacy_is_entered_odoo',
            ]);
        });
    }
};
