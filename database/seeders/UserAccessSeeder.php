<?php

namespace Database\Seeders;

use App\Models\Master\Mt_user_access;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Mt_user_access::create([
            'user_access_name' => 'SUPER ADMINISTRATOR',
            'user_access_desc' => 'SUPER ADMINISTRATOR',
            'access_module' => '[{"module":"mt_user","active_flag":true,"read_all_flag":true},{"module":"mt_user_access","active_flag":true,"read_all_flag":true}]',
            'created_by_id' => 1,
            'updated_by_id' => 1
        ]);
    }
}
