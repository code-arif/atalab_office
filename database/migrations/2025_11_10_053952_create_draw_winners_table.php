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
        Schema::create('draw_winners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_draw_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('donation_id')->constrained()->onDelete('cascade');
            $table->decimal('amount_won', 10, 2);
            $table->boolean('claimed')->default(false);
            $table->timestamp('claimed_at')->nullable();
            $table->string('payout_stripe_id')->nullable();
            $table->string('payout_status')->default('pending'); // pending, processing, completed, failed
            $table->timestamps();

            $table->index(['user_id', 'claimed']);
            $table->index('weekly_draw_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_winners');
    }
};
