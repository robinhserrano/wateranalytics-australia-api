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
        Schema::create('commission_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_manager_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Sales Source & Payment
            $table->enum('sales_source', ['self_gen', 'company_lead']);
            $table->string('payment_type'); // Cash, Online Payment, Finance - Brighte, etc.
            
            // Breakdown Components
            $table->decimal('selling_price', 15, 2);
            $table->decimal('additional_cost', 15, 2)->default(0);
            $table->decimal('landing_price', 15, 2)->default(0);
            $table->decimal('profit', 15, 2);
            
            // Commission Amounts
            $table->decimal('base_commission', 15, 2);
            $table->decimal('extra_commission', 15, 2);
            $table->decimal('manual_adjustment', 15, 2)->default(0);
            $table->decimal('final_commission', 15, 2);
            
            // Special Product
            $table->boolean('is_special_product')->default(false);
            
            // Status & Approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->boolean('confirmed_by_manager')->default(false);
            $table->boolean('entered_to_odoo')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Audit
            $table->json('calculation_metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['sales_manager_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_calculations');
    }
};
