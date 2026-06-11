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
        Schema::create('draw_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('weekly_draw_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_rollover')->default(false);
            $table->timestamps();

            // Ensure a user doesn't participate multiple times in the same draw (via the same logic)
            // Wait, a user could theoretically have multiple donations in the SAME week. 
            // If we allow multiple entries, we shouldn't enforce a unique constraint. 
            // Current selection logic says: "Ensure one entry per user". So unique is correct for participation.
            $table->unique(['weekly_draw_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_participants');
    }
};
