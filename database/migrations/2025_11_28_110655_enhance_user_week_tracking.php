<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add user_week_participations table for tracking
        Schema::create('user_week_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('week_id')->constrained('weekly_draws')->onDelete('cascade');
            $table->timestamp('participated_at');
            $table->boolean('has_donated')->default(false);
            $table->timestamps();

            // CRITICAL: One participation per user per week
            $table->unique(['user_id', 'week_id'], 'unique_user_week_participation');

            // Performance indexes
            $table->index(['user_id', 'participated_at']);
            $table->index(['week_id', 'has_donated']);
        });

        // 2. Add winner exclusion tracking
        Schema::create('winner_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('winner_record_id')->constrained('draw_winners')->onDelete('cascade');
            $table->timestamp('won_at');
            $table->timestamp('exclusion_ends_at'); // won_at + 6 months
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Performance indexes for 6-month lookups
            $table->index(['user_id', 'is_active', 'exclusion_ends_at'], 'idx_user_exclusion');
            $table->index('exclusion_ends_at');
        });

        // 3. Enhance donations table with better constraints
        Schema::table('donations', function (Blueprint $table) {
            // Add donation attempt tracking
            $table->integer('attempt_number')->default(1)->after('payment_type');

            // Add unique constraint per user per week (only for completed donations)
            // This is handled at application level due to MySQL limitations

            // Better indexes for concurrent access
            $table->index(['user_id', 'week_id', 'stripe_payment_status'], 'idx_user_week_payment');
            $table->index(['stripe_payment_status', 'donated_at'], 'idx_status_donated');
        });

        // 4. Add performance optimization columns to weekly_draws
        Schema::table('weekly_draws', function (Blueprint $table) {
            $table->integer('eligible_participants')->default(0)->after('total_participants');
            $table->integer('excluded_winners_count')->default(0)->after('eligible_participants');
            $table->timestamp('last_stats_update')->nullable()->after('excluded_winners_count');

            $table->index(['status', 'start_date', 'end_date'], 'idx_draw_timeline');
        });

        // 5. Add user activity tracking for better management
        Schema::table('users', function (Blueprint $table) {
            $table->integer('total_donations_count')->default(0)->after('stripe_customer_id');
            $table->decimal('lifetime_donation_amount', 12, 2)->default(0)->after('total_donations_count');
            $table->integer('times_won')->default(0)->after('lifetime_donation_amount');
            $table->timestamp('last_donation_at')->nullable()->after('times_won');
            $table->timestamp('last_won_at')->nullable()->after('last_donation_at');

            $table->index('last_donation_at');
            $table->index(['times_won', 'last_won_at']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'total_donations_count',
                'lifetime_donation_amount',
                'times_won',
                'last_donation_at',
                'last_won_at'
            ]);
        });

        Schema::table('weekly_draws', function (Blueprint $table) {
            $table->dropColumn([
                'eligible_participants',
                'excluded_winners_count',
                'last_stats_update'
            ]);
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex('idx_user_week_payment');
            $table->dropIndex('idx_status_donated');
            $table->dropColumn('attempt_number');
        });

        Schema::dropIfExists('winner_exclusions');
        Schema::dropIfExists('user_week_participations');
    }
};
