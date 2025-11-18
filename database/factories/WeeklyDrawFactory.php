<?php

namespace Database\Factories;

use App\Models\WeeklyDraw;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WeeklyDraw>
 */
class WeeklyDrawFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WeeklyDraw::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->setTime(0, 0, 0);
        $endDate = $startDate->copy()->endOfWeek(Carbon::SUNDAY)->setTime(17, 0, 0);
        $countdownEndsAt = $endDate->copy();
        $claimDeadline = $endDate->copy()->addDay()->setTime(5, 0, 0);

        return [
            'week_number' => WeeklyDraw::max('week_number') + 1 ?? 1,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'countdown_ends_at' => $countdownEndsAt,
            'claim_deadline' => $claimDeadline,
            'status' => 'active',
            'total_pool' => 0,
            'total_participants' => 0,
            'total_recipients' => 0,
            'admin_commission' => 0,
            'winners_selected' => false,
        ];
    }

    /**
     * Indicate that the draw is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'winners_selected' => false,
        ]);
    }

    /**
     * Indicate that the draw is in claiming status.
     */
    public function claiming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'claiming',
        ]);
    }

    /**
     * Indicate that the draw is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'winners_selected' => true,
        ]);
    }
}
