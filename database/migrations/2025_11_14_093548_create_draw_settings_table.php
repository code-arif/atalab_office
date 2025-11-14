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
        Schema::create('draw_settings', function (Blueprint $table) {
            $table->id();

            // Total participants
            $table->integer('participants');   // e.g., 4000, 5000, 6000

            // Total pool amount in USD
            $table->decimal('total_pool', 15, 2); // e.g., 100000, 125000

            // How many recipients will be selected
            $table->integer('recipients');     // e.g., 10, 12, 15

            // Odds of selection (you can store numerical ratio)
            $table->integer('odds_numerator')->default(1); // 1
            $table->integer('odds_denominator')->default(400); // 400 (1 in 400)

            // Net per recipient amount
            $table->decimal('net_per_recipient', 15, 2); // e.g., 9250

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_settings');
    }
};
