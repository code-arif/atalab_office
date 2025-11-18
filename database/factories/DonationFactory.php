<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\User;
use App\Models\WeeklyDraw;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Donation>
 */
class DonationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Donation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'week_id' => WeeklyDraw::factory(),
            'amount' => 25.00, // Standard donation
            'stripe_payment_id' => 'cs_test_' . Str::random(40),
            'stripe_payment_status' => 'completed',
            'stripe_charge_id' => 'ch_test_' . Str::random(24),
            'is_eligible_for_draw' => true,
            'payment_type' => 'standard',
            'donated_at' => now(),
        ];
    }

    /**
     * Indicate that the donation is standard ($25).
     */
    public function standard(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => 25.00,
            'payment_type' => 'standard',
        ]);
    }

    /**
     * Indicate that the donation is custom amount.
     */
    public function custom(float $amount = null): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount ?? fake()->randomFloat(2, 26, 1000),
            'payment_type' => 'custom',
        ]);
    }

    /**
     * Indicate that the donation is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_payment_status' => 'pending',
            'stripe_charge_id' => null,
            'is_eligible_for_draw' => false,
        ]);
    }

    /**
     * Indicate that the donation is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_payment_status' => 'completed',
            'is_eligible_for_draw' => true,
        ]);
    }

    /**
     * Indicate that the donation failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_payment_status' => 'failed',
            'stripe_charge_id' => null,
            'is_eligible_for_draw' => false,
        ]);
    }

    /**
     * Set donation for specific week.
     */
    public function forWeek(int $weekId): static
    {
        return $this->state(fn (array $attributes) => [
            'week_id' => $weekId,
        ]);
    }

    /**
     * Set donation for specific user.
     */
    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }
}
