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
        // Add composite indexes for better query performance
        Schema::table('draw_winners', function (Blueprint $table) {
            // For filtering by claim status
            $table->index(['claimed', 'payout_status'], 'idx_claim_payout');

            // For date range queries
            $table->index('created_at', 'idx_created_at');
            $table->index('claimed_at', 'idx_claimed_at');

            // For amount filtering
            $table->index('amount_won', 'idx_amount_won');

            // Composite index for common queries
            $table->index(['weekly_draw_id', 'claimed', 'payout_status'], 'idx_week_claim_payout');
        });

        // Optimize weekly_draws table
        Schema::table('weekly_draws', function (Blueprint $table) {
            // Already has index on status and week_number, add composite
            $table->index(['status', 'week_number'], 'idx_status_week');
        });

        // Optimize donations table
        Schema::table('donations', function (Blueprint $table) {
            // Composite index for eligibility queries
            $table->index(['week_id', 'is_eligible_for_draw'], 'idx_week_eligible');

            // Index for payment status queries
            $table->index('stripe_payment_status', 'idx_payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draw_winners', function (Blueprint $table) {
            $table->dropIndex('idx_claim_payout');
            $table->dropIndex('idx_created_at');
            $table->dropIndex('idx_claimed_at');
            $table->dropIndex('idx_amount_won');
            $table->dropIndex('idx_week_claim_payout');
        });

        Schema::table('weekly_draws', function (Blueprint $table) {
            $table->dropIndex('idx_status_week');
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex('idx_week_eligible');
            $table->dropIndex('idx_payment_status');
        });
    }
};
