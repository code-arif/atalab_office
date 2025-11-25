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
        Schema::table('donations', function (Blueprint $table) {
            // For MySQL - Modify column
            $table->string('stripe_payment_status', 50)->default('pending')->change();
        });

        // Add index for better query performance
        Schema::table('donations', function (Blueprint $table) {
            $table->index(['week_id', 'stripe_payment_status'], 'idx_week_status');
            $table->index(['user_id', 'week_id', 'stripe_payment_status'], 'idx_user_week_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex('idx_week_status');
            $table->dropIndex('idx_user_week_status');
        });
    }
};
