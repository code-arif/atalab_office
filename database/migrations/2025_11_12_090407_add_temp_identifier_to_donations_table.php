<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            // Make user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Add temp identifier
            $table->string('temp_identifier')->nullable()->unique()->after('user_id');

            // Performance indexes
            $table->index('temp_identifier');
            $table->index(['week_id', 'stripe_payment_status']);
            $table->index(['user_id', 'stripe_payment_status']);
        });
    }

    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropIndex(['donations_temp_identifier_index']);
            $table->dropIndex(['donations_week_id_stripe_payment_status_index']);
            $table->dropIndex(['donations_user_id_stripe_payment_status_index']);
            $table->dropColumn('temp_identifier');
        });
    }
};
