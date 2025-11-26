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
        // Enhanced Users Table (NO session_token, NO temp fields)
        Schema::table('users', function (Blueprint $table) {
            // Sequential donor ID (assigned ONLY after first donation)
            $table->string('donor_id', 20)->unique()->nullable()->after('address');

            // Verification timestamps
            $table->timestamp('email_verified_at')->nullable()->after('donor_id');
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');

            // Registration tracking
            $table->timestamp('registered_at')->nullable()->after('phone_verified_at');
            $table->ipAddress('registration_ip')->nullable()->after('registered_at');

            $table->string('stripe_customer_id')->nullable()->after('registration_ip');

            // Indexes
            $table->index('donor_id');
            $table->index(['email', 'email_verified_at']);
            $table->index(['phone', 'phone_verified_at']);
            $table->index('stripe_customer_id');
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
                'email_verified_at',
                'phone_verified_at',
                'registered_at',
                'registration_ip',
                'stripe_customer_id'
            ]);
        });
    }
};
