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
        // OTP Logs Table (for audit)
        Schema::create('otp_logs', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('otp_code', 4);
            $table->enum('type', ['email', 'phone', 'both'])->default('both');
            $table->enum('status', ['sent', 'verified', 'expired', 'failed'])->default('sent');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['email', 'status']);
            $table->index(['phone', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_logs');
    }
};
