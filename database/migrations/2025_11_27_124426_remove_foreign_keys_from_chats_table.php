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
        Schema::table('chats', function (Blueprint $table) {
            // Drop existing foreign keys
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);

            // Make sender_type and receiver_type NOT NULL
            $table->string('sender_type')->default('App\\Models\\User')->change();
            $table->string('receiver_type')->default('App\\Models\\User')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            // Re-add foreign keys
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('sender_type')->nullable()->change();
            $table->string('receiver_type')->nullable()->change();
        });
    }
};
