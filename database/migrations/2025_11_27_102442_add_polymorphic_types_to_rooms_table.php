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
        Schema::table('rooms', function (Blueprint $table) {
            // Add polymorphic type columns (nullable for existing data)
            $table->string('user_one_type')->nullable()->after('user_one_id');
            $table->string('user_two_type')->nullable()->after('user_two_id');
        });

        // Update existing rooms to have User class as default type
        DB::table('rooms')->update([
            'user_one_type' => 'App\\Models\\User',
            'user_two_type' => 'App\\Models\\User',
        ]);

        // Now make them NOT nullable
        Schema::table('rooms', function (Blueprint $table) {
            $table->string('user_one_type')->nullable(false)->change();
            $table->string('user_two_type')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['user_one_type', 'user_two_type']);
        });
    }
};
