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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('odoo_id')->unique(); // The ID from Odoo
            
            // Basic Info
            $table->string('name')->nullable();
            $table->string('default_code')->nullable(); // Internal Reference
            $table->integer('product_variant_count')->default(0);
            
            // Category (many2one relation)
            $table->unsignedBigInteger('categ_id')->nullable();
            $table->string('categ_name')->nullable();
            
            // Currency (many2one relation)
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('currency_name')->nullable();
            
            // UOM (Unit of Measure - many2one relation)
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->string('uom_name')->nullable();
            
            // Pricing
            $table->decimal('list_price', 15, 2)->nullable();
            
            // Quantities
            $table->decimal('qty_available', 15, 2)->nullable();
            
            // Product Properties
            $table->json('product_properties')->nullable();
            
            // Other Fields
            $table->string('type')->nullable(); // product, service, consu
            $table->string('activity_state')->nullable();
            $table->string('priority')->nullable();
            $table->boolean('show_on_hand_qty_status_button')->default(false);
            $table->dateTime('write_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
