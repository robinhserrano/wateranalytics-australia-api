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
            $table->boolean('has_commission_calculation')->default(false)->after('tax_totals');
            $table->foreignId('commission_calculation_id')->nullable()->after('has_commission_calculation')->constrained()->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropForeign(['commission_calculation_id']);
            $table->dropColumn(['has_commission_calculation', 'commission_calculation_id']);
        });
    }
};
