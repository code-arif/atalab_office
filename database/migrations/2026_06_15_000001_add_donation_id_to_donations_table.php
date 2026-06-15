<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add donation_id column with unique sequential format: DONATION-0000000001
     */
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('donation_id', 30)->unique()->nullable()->after('id');
        });

        // Populate existing donations with sequential donation IDs
        $donations = DB::table('donations')->orderBy('id')->get();
        $counter = 1;
        foreach ($donations as $donation) {
            $donationId = 'DONATION-' . str_pad($counter, 10, '0', STR_PAD_LEFT);
            DB::table('donations')
                ->where('id', $donation->id)
                ->update(['donation_id' => $donationId]);
            $counter++;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('donation_id');
        });
    }
};
