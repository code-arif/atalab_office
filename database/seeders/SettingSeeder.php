<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            'title'         => 'DonateQ',
            'phone'         => '123456789',
            'email'         => 'info@donation.com',
            'name'          => 'atalab',
            'copyright'     => 'Copyright © 2025 DonateQ. All rights reserved.',
            'description'   => "DonateQ is a digital agency that creates and shares innovative digital product experiences tailored for startups and small businesses.
                                Through this platform, our team showcases project updates, creative work, and industry insights—giving users a behind-the-scenes look at
                                how we bring digital ideas to life.",
            'address'       => 'Cairo, Australia',
            'keywords'      => 'DonateQ',
            'author'        => 'atalab',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}
