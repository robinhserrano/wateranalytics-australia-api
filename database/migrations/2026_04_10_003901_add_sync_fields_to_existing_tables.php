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
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('state')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->dateTime('write_date')->nullable();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dateTime('write_date')->nullable();
            $table->unsignedBigInteger('partner_shipping_id')->nullable();
            $table->string('partner_shipping_name')->nullable();
            $table->text('partner_shipping_address')->nullable();
            $table->string('partner_shipping_state')->nullable();
            $table->decimal('recurring_total', 15, 2)->nullable();
            $table->string('plan_name')->nullable();
            $table->unsignedBigInteger('subscription_plan_id')->nullable();
            $table->boolean('is_subscription')->default(false);
            $table->string('subscription_state')->nullable();
            $table->date('start_date')->nullable();
            $table->date('next_invoice_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['state', 'phone', 'email', 'write_date']);
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn([
                'write_date', 'partner_shipping_id', 'partner_shipping_name', 
                'partner_shipping_address', 'partner_shipping_state', 'recurring_total', 
                'plan_name', 'subscription_plan_id', 'is_subscription', 
                'subscription_state', 'start_date', 'next_invoice_date', 'end_date'
            ]);
        });
    }
};
