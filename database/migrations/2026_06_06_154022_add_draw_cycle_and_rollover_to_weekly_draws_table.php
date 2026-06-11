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
        Schema::table('weekly_draws', function (Blueprint $table) {
            $table->foreignId('draw_cycle_id')->nullable()->constrained('draw_cycles')->nullOnDelete();
            $table->boolean('is_rolled_over')->default(false)->after('winners_selected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_draws', function (Blueprint $table) {
            $table->dropForeign(['draw_cycle_id']);
            $table->dropColumn(['draw_cycle_id', 'is_rolled_over']);
        });
    }
};
