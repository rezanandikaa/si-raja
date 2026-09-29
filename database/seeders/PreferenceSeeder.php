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

        // Ambang persentil untuk daftar P3KE. Baris dengan persentil di atas nilai
        // ini disembunyikan dari tabel (lihat DestitutionKkRepository::getRecord).
        // Kalau baris ini tidak ada, get_preference() jatuh ke default 2 dan
        // hampir semua data hasil input baru ikut hilang dari daftar.
        // 20 sejalan dengan warna progress bar di detail.blade.php.
        Sy_preference::create([
            'name' => 'BATAS PERSENTIL DATA P3KE',
            'key' => 'default_percentile',
            'value' => '20',
            'created_by_id' => 1,
            'updated_by_id' => 1
        ]);
    }
}
