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
        Schema::create('draw_automate_settings', function (Blueprint $table) {
            $table->id();
            
            // Core Settings
            $table->decimal('admin_fee_percentage', 5, 2)->default(7.50);
            $table->integer('odds_ratio')->default(400);
            $table->integer('minimum_participants')->default(100);
            $table->integer('winner_exclusion_months')->default(6);

            // Schedule Settings
            $table->string('draw_start_day')->default('Monday');
            $table->time('draw_start_time')->default('00:00:00');
            $table->string('draw_end_day')->default('Sunday');
            $table->time('draw_end_time')->default('17:00:00');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_automate_settings');
    }
};
