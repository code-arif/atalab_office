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
        Schema::create('stripe_settings', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_key')->nullable();
            $table->string('stripe_secret')->nullable();
            $table->string('stripe_webhook_secret')->nullable();
            $table->decimal('admin_percentage', 8, 2)->default(0);
            $table->decimal('ach_flat_fee', 8, 2)->default(0);
            $table->decimal('card_fee_percentage', 8, 2)->default(0);
            $table->decimal('card_fixed_fee', 8, 2)->default(0);
            $table->decimal('donation_amount', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_settings');
    }
};
