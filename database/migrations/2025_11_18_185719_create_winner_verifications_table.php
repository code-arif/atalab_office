<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('winner_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draw_winner_id')->constrained()->onDelete('cascade');
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');

            // Identity Verification
            $table->boolean('identity_verified')->default(false);
            $table->string('drivers_license')->nullable();
            $table->string('license_state')->nullable();
            $table->date('license_expiry')->nullable();
            $table->timestamp('identity_verified_at')->nullable();

            // Contact Verification
            $table->boolean('email_verified')->default(false);
            $table->boolean('phone_verified')->default(false);
            $table->string('verification_code')->nullable();
            $table->timestamp('code_expires_at')->nullable();
            $table->timestamp('contact_verified_at')->nullable();

            // Bank Account Verification
            $table->boolean('bank_verified')->default(false);
            $table->string('bank_name')->nullable();
            $table->string('account_number_last4')->nullable();
            $table->string('routing_number')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->timestamp('bank_verified_at')->nullable();

            // Overall Verification Status
            $table->enum('verification_status', [
                'pending',
                'identity_review',
                'contact_verification',
                'bank_verification',
                'approved',
                'rejected'
            ])->default('pending');

            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            $table->index('verification_status');
            $table->index('draw_winner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('winner_verifications');
    }
};
