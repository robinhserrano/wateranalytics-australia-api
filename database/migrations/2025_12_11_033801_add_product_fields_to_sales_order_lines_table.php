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
        Schema::table('sales_order_lines', function (Blueprint $table) {
            // product_id
            if (Schema::hasColumn('sales_order_lines', 'product_id')) {
                // Column exists, just add the foreign key constraint
                // We shouldn't use foreignId() here as it tries to create the column
                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            } else {
                // Create column and foreign key
                $table->foreignId('product_id')->nullable()->after('sales_order_id')->constrained()->onDelete('set null');
            }

            // landing_price_id
            if (Schema::hasColumn('sales_order_lines', 'landing_price_id')) {
                 $table->foreign('landing_price_id')->references('id')->on('landing_prices')->onDelete('set null');
            } else {
                $table->foreignId('landing_price_id')->nullable()->after('product_id')->constrained()->onDelete('set null');
            }

            // Boolean flags and amounts
            if (!Schema::hasColumn('sales_order_lines', 'is_installation_service')) {
                $table->boolean('is_installation_service')->default(false)->after('landing_price_id');
            }
            if (!Schema::hasColumn('sales_order_lines', 'is_supply_only')) {
                $table->boolean('is_supply_only')->default(false)->after('is_installation_service');
            }
            if (!Schema::hasColumn('sales_order_lines', 'tax_exclusive_amount')) {
                $table->decimal('tax_exclusive_amount', 15, 2)->nullable()->after('is_supply_only');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_lines', function (Blueprint $table) {
            // We use array syntax to drop foreign keys, which usually relies on the standard naming convention
            // products_product_id_foreign
            
            // Check existence before dropping to be safe, though dropForeign usually handles it gracefully-ish (depends on driver version)
            // Ideally we try-catch or just attempt. 
            // Since we are "repairing" a migration that might have partially run or is conflicting with another, 
            // explicit drops are safer.

            $table->dropForeign(['product_id']);
            $table->dropForeign(['landing_price_id']);
            
            // We should only drop columns that this migration added?
            // Since 'product_id' existed before (in another migration), strictly speaking we shouldn't drop the column 'product_id', only the FK.
            // But usually 'down' is supposed to reverse 'up'. 
            // If 'up' added the column, 'down' removes it.
            // If 'up' only added the FK, 'down' should only remove the FK.
            // But we have a dynamic 'up'. 
            // For now, let's just drop the columns mentioned in the original version of this file 
            // BUT careful about product_id if it belonged to another migration.
            // The user just wants it fixed. The previous migration `create_sales_order_lines` created `product_id`.
            // So we should NOT drop `product_id` column here, ONLY the FK.
            
            // $table->dropColumn('product_id'); // Don't drop this if it belongs to table creation
            
             $table->dropColumn([
                // 'product_id', // Keep the column, it's core
                'landing_price_id',
                'is_installation_service',
                'is_supply_only',
                'tax_exclusive_amount',
            ]);
        });
    }
};
