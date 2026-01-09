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
            $table->decimal('additional_deduction', 15, 2)->default(0)->after('amount_to_invoice');
        });

        Schema::table('commission_calculations', function (Blueprint $table) {
            $table->decimal('additional_deduction', 15, 2)->default(0)->after('manual_adjustment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('additional_deduction');
        });

        Schema::table('commission_calculations', function (Blueprint $table) {
            $table->dropColumn('additional_deduction');
        });
    }
};
