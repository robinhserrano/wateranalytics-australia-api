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
        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('odoo_id')->unique();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade'); // Local ID
            $table->unsignedBigInteger('odoo_order_id'); // Odoo Order ID (for reference/linking)
            
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name')->nullable();
            
            $table->text('name')->nullable(); // Description
            $table->decimal('product_uom_qty', 15, 2)->nullable();
            $table->decimal('price_unit', 15, 2)->nullable();
            $table->decimal('price_subtotal', 15, 2)->nullable();
            $table->decimal('price_total', 15, 2)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_lines');
    }
};
