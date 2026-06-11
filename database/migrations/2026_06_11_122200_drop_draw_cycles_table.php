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
        // First drop the foreign key and column from weekly_draws
        Schema::table('weekly_draws', function (Blueprint $table) {
            if (Schema::hasColumn('weekly_draws', 'draw_cycle_id')) {
                $table->dropForeign(['draw_cycle_id']);
                $table->dropColumn('draw_cycle_id');
            }
        });

        Schema::dropIfExists('draw_cycles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('draw_cycles', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
        });

        Schema::table('weekly_draws', function (Blueprint $table) {
            $table->foreignId('draw_cycle_id')->nullable()->constrained('draw_cycles')->nullOnDelete();
        });
    }
};
