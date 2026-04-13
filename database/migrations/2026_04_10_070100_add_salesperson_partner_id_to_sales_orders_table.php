<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add salesperson_partner_id to sales_orders.
     * This resolves the Odoo user_id (res.users) → partner_id (res.partner) mapping,
     * allowing reliable attribution via assigned contacts.
     */
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('salesperson_partner_id')
                ->nullable()
                ->after('user_name')
                ->index()
                ->comment('Odoo partner_id (res.partner) of the salesperson, resolved from user_id (res.users)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn('salesperson_partner_id');
        });
    }
};
