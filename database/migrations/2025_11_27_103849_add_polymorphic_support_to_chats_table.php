<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            // Add polymorphic type columns (nullable initially)
            if (!Schema::hasColumn('chats', 'sender_type')) {
                $table->string('sender_type')->nullable()->after('sender_id');
            }
            if (!Schema::hasColumn('chats', 'receiver_type')) {
                $table->string('receiver_type')->nullable()->after('receiver_id');
            }
        });

        // Update existing chats to have User class as default type
        DB::table('chats')->update([
            'sender_type' => 'App\\Models\\User',
            'receiver_type' => 'App\\Models\\User',
        ]);

        // Now make them NOT nullable
        Schema::table('chats', function (Blueprint $table) {
            $table->string('sender_type')->nullable(false)->change();
            $table->string('receiver_type')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            $table->dropColumn(['sender_type', 'receiver_type']);
        });
    }
};
