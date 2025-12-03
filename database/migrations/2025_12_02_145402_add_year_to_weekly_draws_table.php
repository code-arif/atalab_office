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
            // Add year column after week_number
            $table->integer('year')->after('week_number')->default(2025);

            // Add composite index for efficient queries
            $table->index(['year', 'week_number'], 'idx_year_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_draws', function (Blueprint $table) {
            $table->dropIndex('idx_year_week');
            $table->dropColumn('year');
        });
    }
};
