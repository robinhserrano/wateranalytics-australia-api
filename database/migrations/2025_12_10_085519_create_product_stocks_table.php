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
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->integer('odoo_id')->unique();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('display_name');
            $table->integer('categ_id')->nullable();
            $table->string('categ_name')->nullable();
            $table->string('cost_method')->nullable();
            $table->decimal('avg_cost', 15, 2)->nullable();
            $table->decimal('total_value', 15, 2)->nullable();
            $table->decimal('qty_available', 15, 2)->default(0);
            $table->decimal('free_qty', 15, 2)->default(0);
            $table->decimal('incoming_qty', 15, 2)->default(0);
            $table->decimal('outgoing_qty', 15, 2)->default(0);
            $table->decimal('virtual_available', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
