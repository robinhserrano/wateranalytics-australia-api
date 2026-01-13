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
            $table->decimal('legacy_additional_deduction', 10, 2)->nullable()->after('legacy_is_entered_odoo');
            $table->text('legacy_manual_notes')->nullable()->after('legacy_additional_deduction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['legacy_additional_deduction', 'legacy_manual_notes']);
        });
    }
};
