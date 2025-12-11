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
        Schema::table('product_stocks', function (Blueprint $table) {
            // Drop the existing unique constraint on odoo_id
            $table->dropUnique(['odoo_id']);
            
            // Add a composite unique constraint on odoo_id and warehouse_id
            $table->unique(['odoo_id', 'warehouse_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique(['odoo_id', 'warehouse_id']);
            
            // Restore the original unique constraint on odoo_id
            $table->unique('odoo_id');
        });
    }
};
