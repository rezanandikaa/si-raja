<?php

namespace Database\Seeders;

use App\Models\System\Sy_preference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sy_preference::create([
            'name' => 'NAMA APLIKASI',
            'key' => 'app_name',
            'value' => 'SIRAJA',
            'created_by_id' => 1,
            'updated_by_id' => 1
        ]);
    }
}
