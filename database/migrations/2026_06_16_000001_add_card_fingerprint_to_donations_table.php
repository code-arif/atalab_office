<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds card fingerprint tracking columns for duplicate card detection.
     * This enables identifying whether the same card was used across multiple donations
     * within the same draw cycle, without modifying existing payment logic.
     */
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // Stripe card fingerprint — unique identifier for the physical card
            $table->string('card_fingerprint', 100)->nullable()->after('stripe_charge_id');

            // Stripe PaymentMethod ID — the payment method used for this donation
            $table->string('stripe_payment_method_id', 100)->nullable()->after('card_fingerprint');

            // Stripe PaymentIntent ID — the payment intent associated with this donation
            $table->string('stripe_payment_intent_id', 100)->nullable()->after('stripe_payment_method_id');

            // Index for efficient duplicate card lookups by week
            $table->index(['card_fingerprint', 'week_id'], 'idx_card_fingerprint_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex('idx_card_fingerprint_week');
            $table->dropColumn([
                'card_fingerprint',
                'stripe_payment_method_id',
                'stripe_payment_intent_id',
            ]);
        });
    }
};
