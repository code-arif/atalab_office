<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Donation;
use App\Models\WeeklyDraw;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestDonationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Usage:
     * php artisan db:seed --class=TestDonationsSeeder
     *
     * Or with specific count:
     * php artisan tinker
     * >>> (new \Database\Seeders\TestDonationsSeeder)->seedDonations(500);
     */
    public function run(): void
    {
        $this->command->info('Starting Test Donations Seeder...');

        // Ask for number of donations to create
        $count = $this->command->choice(
            'How many donations do you want to create?',
            ['100', '500', '1000', '5000', '10000', '20000', 'Custom'],
            0
        );

        if ($count === 'Custom') {
            $count = $this->command->ask('Enter custom amount:', 100);
        }

        $count = (int) $count;

        // Create or get active draw
        $draw = $this->getOrCreateActiveDraw();
        $this->command->info("Using Draw: Week #{$draw->week_number} (ID: {$draw->id})");

        // Seed donations
        $this->seedDonations($count, $draw);

        // Update draw statistics
        $this->updateDrawStats($draw);

        $this->command->info('Test donations created successfully!');
        $this->displayStats($draw);
    }

    /**
     * Get or create an active draw
     */
    protected function getOrCreateActiveDraw(): WeeklyDraw
    {
        $draw = WeeklyDraw::where('status', 'active')->first();

        if (!$draw) {
            $this->command->warn('No active draw found. Creating one...');
            $draw = WeeklyDraw::factory()->active()->create();
            $this->command->info("Created new draw: Week #{$draw->week_number}");
        }

        return $draw;
    }

    /**
     * Seed donations with progress bar
     */
    public function seedDonations(int $count, WeeklyDraw $draw = null): void
    {
        if (!$draw) {
            $draw = $this->getOrCreateActiveDraw();
        }

        $this->command->info("Creating {$count} test donations...");

        // Create progress bar
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        // Process in chunks to avoid memory issues
        $chunkSize = 500;
        $chunks = (int) ceil($count / $chunkSize);

        for ($i = 0; $i < $chunks; $i++) {
            $currentChunkSize = min($chunkSize, $count - ($i * $chunkSize));

            DB::transaction(function () use ($draw, $currentChunkSize, $bar) {
                // Create users with donations
                User::factory()
                    ->count($currentChunkSize)
                    ->donor()
                    ->has(
                        Donation::factory()
                            ->standard()
                            ->completed()
                            ->forWeek($draw->id),
                        'donations'
                    )
                    ->create();

                $bar->advance($currentChunkSize);
            });
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("Created {$count} donations successfully!");
    }

    /**
     * Update draw statistics
     */
    protected function updateDrawStats(WeeklyDraw $draw): void
    {
        $this->command->info('Updating draw statistics...');

        $stats = Donation::where('week_id', $draw->id)
            ->where('stripe_payment_status', 'completed')
            ->selectRaw('SUM(amount) as total, COUNT(DISTINCT user_id) as participants')
            ->first();

        $draw->update([
            'total_pool' => $stats->total ?? 0,
            'total_participants' => $stats->participants ?? 0,
        ]);

        Log::info('Draw stats updated', [
            'week_id' => $draw->id,
            'total_pool' => $stats->total,
            'participants' => $stats->participants,
        ]);
    }

    /**
     * Display statistics
     */
    protected function displayStats(WeeklyDraw $draw): void
    {
        $draw->refresh();

        $expectedWinners = (int) ceil($draw->total_participants / 400);
        $adminCommission = $draw->total_pool * 0.075;
        $distributionPool = $draw->total_pool - $adminCommission;
        $perWinner = $expectedWinners > 0 ? $distributionPool / $expectedWinners : 0;

        $this->command->newLine();
        $this->command->table(
            ['Metric', 'Value'],
            [
                ['Week Number', "Week #{$draw->week_number}"],
                ['Status', $draw->status],
                ['Total Participants', number_format($draw->total_participants)],
                ['Total Pool', '$' . number_format($draw->total_pool, 2)],
                ['Expected Winners', $expectedWinners . ' (1:400 ratio)'],
                ['Admin Commission (7.5%)', '$' . number_format($adminCommission, 2)],
                ['Distribution Pool', '$' . number_format($distributionPool, 2)],
                ['Per Winner', '$' . number_format($perWinner, 2)],
            ]
        );

        $this->command->newLine();
        $this->command->info('Ready to select winners!');
        $this->command->info('Run: php artisan tinker');
        $this->command->info('>>> $service = app(\App\Services\WeeklyDrawService::class);');
        $this->command->info(">>> \$result = \$service->selectWinners({$draw->id});");
    }
}
