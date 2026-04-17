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
            $table->decimal('amount_untaxed', 15, 2)->nullable();
            $table->decimal('amount_tax', 15, 2)->nullable();
        });

        Schema::table('sales_order_lines', function (Blueprint $table) {
            $table->decimal('qty_delivered', 15, 2)->nullable();
            $table->decimal('qty_invoiced', 15, 2)->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->string('tax_names')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['amount_untaxed', 'amount_tax']);
        });

        Schema::table('sales_order_lines', function (Blueprint $table) {
            $table->dropColumn(['qty_delivered', 'qty_invoiced', 'discount', 'tax_names']);
        });
    }
};
