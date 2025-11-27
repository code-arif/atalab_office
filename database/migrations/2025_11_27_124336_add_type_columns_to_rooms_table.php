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
        Schema::table('rooms', function (Blueprint $table) {
            // Drop the existing foreign keys and unique constraint first
            $table->dropForeign(['user_one_id']);
            $table->dropForeign(['user_two_id']);
            $table->dropUnique(['user_one_id', 'user_two_id']);

            // Add type columns to identify if user is Guest or Admin
            $table->string('user_one_type')->default('App\\Models\\User')->after('user_one_id');
            $table->string('user_two_type')->default('App\\Models\\User')->after('user_two_id');

            // Add index for better performance
            $table->index(['user_one_id', 'user_one_type', 'user_two_id', 'user_two_type'], 'room_participants_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex('room_participants_index');
            $table->dropColumn(['user_one_type', 'user_two_type']);

            // Re-add foreign keys
            $table->foreign('user_one_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_two_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_one_id', 'user_two_id']);
        });
    }
};
