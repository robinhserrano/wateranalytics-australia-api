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
        Schema::table('commission_approvals', function (Blueprint $table) {
            $table->enum('action', ['approved', 'rejected', 'confirmed', 'entered_to_odoo', 'reset_confirm', 'reset_odoo'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_approvals', function (Blueprint $table) {
            $table->enum('action', ['approved', 'rejected', 'confirmed', 'entered_to_odoo'])->change();
        });
    }
};
