<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(UserAccessSeeder::class);
        $this->call(KemdagriRegionLebakSeeder::class);
        $this->call(OptionSeeder::class);
        $this->call(DataSeeder::class);
        $this->call(PreferenceSeeder::class);
        $this->call(PuskesmasSeeder::class);
    }
}
