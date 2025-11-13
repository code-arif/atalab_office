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
        Schema::create('weekly_draws', function (Blueprint $table) {
            $table->id();
            $table->integer('week_number')->unique();

            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('countdown_ends_at')->nullable();
            $table->timestamp('claim_deadline')->nullable();

            $table->enum('status', ['active', 'claiming', 'completed'])->default('active');
            $table->decimal('total_pool', 12, 2)->default(0);
            $table->integer('total_participants')->default(0);
            $table->integer('total_recipients')->default(0);
            $table->decimal('admin_commission', 12, 2)->default(0);
            $table->boolean('winners_selected')->default(false);
            $table->timestamps();

            $table->index('status');
            $table->index('week_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_draws');
    }
};
