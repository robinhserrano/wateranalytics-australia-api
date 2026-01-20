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
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('command'); // e.g., odoo:sync-contacts
            $table->string('status')->default('running'); // running, completed, failed
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration')->nullable(); // in seconds
            $table->integer('records_processed')->default(0);
            $table->text('message')->nullable(); // Error message or success summary
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
