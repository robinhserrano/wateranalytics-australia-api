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
        Schema::table('users', function (Blueprint $table) {
            // Odoo Integration
            if (!Schema::hasColumn('users', 'odoo_user_id')) {
                $table->unsignedBigInteger('odoo_user_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('users', 'odoo_salesperson_id')) {
                $table->unsignedBigInteger('odoo_salesperson_id')->nullable()->after('odoo_user_id');
            }
            
            // Role & Hierarchy
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->after('remember_token')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'sales_manager_id')) {
                $table->foreignId('sales_manager_id')->nullable()->after('role_id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('users', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('sales_manager_id')->constrained()->onDelete('set null');
            }
            
            // Commission Configuration
            if (!Schema::hasColumn('users', 'self_gen_base')) {
                $table->decimal('self_gen_base', 10, 2)->default(1000.00)->after('team_id');
            }
            if (!Schema::hasColumn('users', 'company_lead_base')) {
                $table->decimal('company_lead_base', 10, 2)->default(500.00)->after('self_gen_base');
            }
            if (!Schema::hasColumn('users', 'commission_split')) {
                $table->decimal('commission_split', 5, 2)->default(50.00)->after('company_lead_base');
            }
            
            // Status
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('commission_split');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropForeign(['sales_manager_id']);
            $table->dropForeign(['team_id']);
            $table->dropColumn([
                'odoo_user_id',
                'odoo_salesperson_id',
                'role_id',
                'sales_manager_id',
                'team_id',
                'self_gen_base',
                'company_lead_base',
                'commission_split',
                'is_active',
            ]);
        });
    }
};
