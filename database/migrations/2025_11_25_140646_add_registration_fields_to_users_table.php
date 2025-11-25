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
        Schema::table('users', function (Blueprint $table) {
            //
        });

        // Add registration fields to users table
        Schema::table('users', function (Blueprint $table) {
            // Unique donor ID (sequential)
            $table->string('donor_id', 20)->unique()->nullable()->after('id');

            // OTP verification
            $table->string('otp_code', 4)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // Registration tracking
            $table->timestamp('registered_at')->nullable();
            $table->ipAddress('registration_ip')->nullable();

            // Indexes for performance
            $table->index('donor_id');
            $table->index(['email', 'email_verified_at']);
            $table->index(['phone', 'phone_verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'donor_id',
                'otp_code',
                'otp_expires_at',
                'email_verified_at',
                'phone_verified_at',
                'registered_at',
                'registration_ip'
            ]);
        });
    }
};
