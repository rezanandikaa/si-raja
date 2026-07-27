<?php

namespace Database\Seeders;

use App\Models\System\Sy_data;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'name' => 'BNBA P3KE 2023',
            'description' => 'BNBA P3KE 2023',
            'active_flag' => true
        ];
        Sy_data::create($data);
    }
}
