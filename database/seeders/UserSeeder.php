<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'MOCH DIKI WIDIANTO',
            'email' => 'diki.widianto@unilam.ac.id',
            'gender' => 'L',
            'active_flag' => true,
            'user_access_id' => 1,
            'organization_id' => 1,
            'password' => bcrypt('admin123'),
            'created_by_id' => 1,
            'updated_by_id' => 1
        ]);

        User::create([
            'name' => 'ZAKY MUJAYIN',
            'email' => 'user1@example.com',
            'gender' => 'L',
            'active_flag' => true,
            'user_access_id' => 1,
            'organization_id' => 1,
            'password' => bcrypt('admin123'),
            'created_by_id' => 1,
            'updated_by_id' => 1
        ]);
    }
}
