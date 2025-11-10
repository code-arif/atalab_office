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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('week_id')->constrained('weekly_draws')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('stripe_payment_id')->unique();
            $table->string('stripe_payment_status')->default('pending');
            $table->string('stripe_charge_id')->nullable();
            $table->boolean('is_eligible_for_draw')->default(true);
            $table->string('payment_type')->default('standard'); // standard or custom
            $table->timestamp('donated_at');
            $table->timestamps();

            $table->index(['user_id', 'week_id']);
            $table->index('is_eligible_for_draw');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
