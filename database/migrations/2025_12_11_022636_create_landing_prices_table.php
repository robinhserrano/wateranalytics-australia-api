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
        Schema::create('landing_prices', function (Blueprint $table) {
            $table->id();
            
            // Product reference
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('internal_reference')->nullable();
            $table->string('product_category')->nullable();
            
            // Pricing
            $table->decimal('installation_service', 15, 2)->default(0);
            $table->decimal('supply_only', 15, 2)->default(0);
            
            // Effective date for price versioning
            $table->date('effective_from')->nullable();
            
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['product_id', 'effective_from']);
            $table->index('internal_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landing_prices');
    }
};
