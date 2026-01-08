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
        if (Schema::hasColumn('sales_orders', 'tax_totals')) {
            Schema::table('sales_orders', function (Blueprint $table) {
                $table->dropColumn('tax_totals');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->json('tax_totals')->nullable();
            // $table->dropColumn('base_amount');
        });
    }
};
