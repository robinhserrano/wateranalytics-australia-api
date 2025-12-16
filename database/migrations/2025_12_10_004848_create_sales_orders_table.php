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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('odoo_id')->unique(); // The ID from Odoo
            $table->string('name')->nullable();
            $table->dateTime('create_date')->nullable();

            // Relations (storing Odoo ID and Name/Display)
            // Odoo returns [id, "Name"] for many2one
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->string('partner_name')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();

            $table->unsignedBigInteger('team_id')->nullable();
            $table->string('team_name')->nullable();

            // Custom Studio Fields
            $table->string('x_studio_sales_rep_1')->nullable();
            $table->string('x_studio_sales_source')->nullable();
            $table->string('x_studio_commission_paid')->nullable();
            $table->string('x_studio_referred_by')->nullable();
            $table->string('x_studio_referrer_processed')->nullable();
            $table->string('x_studio_payment_type')->nullable();

            // Financials
            $table->decimal('amount_total', 15, 2)->nullable();
            $table->decimal('amount_to_invoice', 15, 2)->nullable();

            // Statuses
            $table->string('delivery_status')->nullable();
            $table->string('x_studio_invoice_payment_status')->nullable();
            $table->string('state')->nullable();

            // Notes
            // $table->text('internal_note_display')->nullable();

            // Arrays/JSON
            $table->json('tag_ids')->nullable();
            $table->json('order_line')->nullable();
            $table->json('tax_totals')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
