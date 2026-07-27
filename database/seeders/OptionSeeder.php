<?php

namespace Database\Seeders;

use App\Models\System\Sy_option;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        try {
            $datas = [
                // Age Group
                ['code' => 'age_group', 'label' => 'Kelompok Usia ', 'value' => 'USIA < 5 TAHUN'],
                ['code' => 'age_group', 'label' => 'Kelompok Usia ', 'value' => 'USIA 13-15 TAHUN'],
                ['code' => 'age_group', 'label' => 'Kelompok Usia ', 'value' => 'USIA 16-18 TAHUN'],
                ['code' => 'age_group', 'label' => 'Kelompok Usia ', 'value' => 'USIA 19 - 59 TAHUN'],
                ['code' => 'age_group', 'label' => 'Kelompok Usia ', 'value' => 'USIA 6-12 TAHUN'],
                ['code' => 'age_group', 'label' => 'Kelompok Usia ', 'value' => 'USIA 60+ TAHUN'],

                // Prioritas Verval
                ['code' => 'verval_priority', 'label' => 'Prioritas Verval', 'value' => 'NIK DUPLIKAT'],
                ['code' => 'verval_priority', 'label' => 'Prioritas Verval', 'value' => 'NORMAL'],

                // Prioritas Verval Keluarga
                ['code' => 'verval_priority_family', 'label' => 'Prioritas Verval', 'value' => 'KELUARGA DUPLIKAT (NIK & NAMA)'],
                ['code' => 'verval_priority_family', 'label' => 'Prioritas Verval', 'value' => 'KELUARGA NIK KOSONG'],
                ['code' => 'verval_priority_family', 'label' => 'Prioritas Verval', 'value' => 'KELUARGA TIDAK ADA USIA 17+ TAHUN'],
                ['code' => 'verval_priority_family', 'label' => 'Prioritas Verval', 'value' => 'NORMAL'],

                // Padan Dukcapil
                ['code' => 'padan_dukcapil', 'label' => 'Padan Dukcapil', 'value' => 'PADAN'],
                ['code' => 'padan_dukcapil', 'label' => 'Padan Dukcapil', 'value' => 'TIDAK PADAN'],
                ['code' => 'padan_dukcapil', 'label' => 'Padan Dukcapil', 'value' => 'KOSONG'],

                // Relationship
                ['code' => 'relationship', 'label' => 'Hubungan dengan Kepala Keluarga', 'value' => 'KEPALA KELUARGA'],
                ['code' => 'relationship', 'label' => 'Hubungan dengan Kepala Keluarga', 'value' => 'ISTRI/SUAMI'],
                ['code' => 'relationship', 'label' => 'Hubungan dengan Kepala Keluarga', 'value' => 'ANAK'],
                ['code' => 'relationship', 'label' => 'Hubungan dengan Kepala Keluarga', 'value' => 'LAINNYA'],

                // Marital Status
                ['code' => 'marital_status', 'label' => 'Status Kawin', 'value' => 'BELUM KAWIN'],
                ['code' => 'marital_status', 'label' => 'Status Kawin', 'value' => 'KAWIN'],
                ['code' => 'marital_status', 'label' => 'Status Kawin', 'value' => 'CERAI HIDUP'],
                ['code' => 'marital_status', 'label' => 'Status Kawin', 'value' => 'CERAI MATI'],

                // Job
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'NELAYAN'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'PEDAGANG'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'PEGAWAI SWASTA'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'PEJABAT NEGARA'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'PEKERJA LEPAS'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'PENSIUNAN'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'PETANI'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'PNS/TNI/POLRI'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'TIDAK/BELUM BEKERJA'],
                ['code' => 'job', 'label' => 'Pekerjaan', 'value' => 'WIRASWASTA'],

                // Job Status
                ['code' => 'job_status', 'label' => 'Status Pekerjaan', 'value' => 'BERUSAHA DIBANTU BURUH TETAP/DIBAYAR'],
                ['code' => 'job_status', 'label' => 'Status Pekerjaan', 'value' => 'BERUSAHA DIBANTU BURUH TIDAK TETAP/BURUH TIDAK DIBAYAR'],
                ['code' => 'job_status', 'label' => 'Status Pekerjaan', 'value' => 'BERUSAHA SENDIRI'],
                ['code' => 'job_status', 'label' => 'Status Pekerjaan', 'value' => 'BURUH/KARYAWAN/PEGAWAI'],
                ['code' => 'job_status', 'label' => 'Status Pekerjaan', 'value' => 'PEKERJA BEBAS'],
                ['code' => 'job_status', 'label' => 'Status Pekerjaan', 'value' => 'PEKERJA KELUARGA/TIDAK DIBAYAR'],
                ['code' => 'job_status', 'label' => 'Status Pekerjaan', 'value' => 'KOSONG'],

                // Education
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'MASIH PT/AKADEMI'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'MASIH SD/SEDERAJAT'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'MASIH SLTA/SEDERAJAT'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'MASIH SLTP/SEDERAJAT'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'TAMAT PT/AKADEMI'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'TAMAT SD/SEDERAJAT'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'TAMAT SLTA/SEDERAJAT'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'TAMAT SLTP/SEDERAJAT'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'TDK TAMAT SD/SEDERAJAT'],
                ['code' => 'education', 'label' => 'Pendidikan', 'value' => 'TIDAK/BELUM SEKOLAH'],

                // Home Ownership
                ['code' => 'home_ownership', 'label' => 'Pendidikan', 'value' => 'BEBAS SEWA/MENUMPANG'],
                ['code' => 'home_ownership', 'label' => 'Pendidikan', 'value' => 'DINAS'],
                ['code' => 'home_ownership', 'label' => 'Pendidikan', 'value' => 'KONTRAK/SEWA'],
                ['code' => 'home_ownership', 'label' => 'Pendidikan', 'value' => 'LAINNYA'],
                ['code' => 'home_ownership', 'label' => 'Pendidikan', 'value' => 'MILIK SENDIRI'],

                // Etc. Ownership
                ['code' => 'etc_ownership', 'label' => 'Memiliki Simpanan Uang/Perhiasan/Ternak/Lainnya', 'value' => 'KOSONG'],
                ['code' => 'etc_ownership', 'label' => 'Memiliki Simpanan Uang/Perhiasan/Ternak/Lainnya', 'value' => 'TIDAK'],
                ['code' => 'etc_ownership', 'label' => 'Memiliki Simpanan Uang/Perhiasan/Ternak/Lainnya', 'value' => 'YA'],

                // Roof
                ['code' => 'home_roof', 'label' => 'Jenis Atap', 'value' => 'ASBES/SENG'],
                ['code' => 'home_roof', 'label' => 'Jenis Atap', 'value' => 'BAMBU'],
                ['code' => 'home_roof', 'label' => 'Jenis Atap', 'value' => 'BETON'],
                ['code' => 'home_roof', 'label' => 'Jenis Atap', 'value' => 'GENTENG'],
                ['code' => 'home_roof', 'label' => 'Jenis Atap', 'value' => 'JERAMI/IJUK/RUMBIA/ DAUN-DAUNAN'],
                ['code' => 'home_roof', 'label' => 'Jenis Atap', 'value' => 'KAYU/SIRAP'],
                ['code' => 'home_roof', 'label' => 'Jenis Atap', 'value' => 'LAINNYA'],

                // Roof Quality
                ['code' => 'home_roof_quality', 'label' => 'Kualitas Atap', 'value' => 'BAGUS/KUALITAS TINGGI'],
                ['code' => 'home_roof_quality', 'label' => 'Kualitas Atap', 'value' => 'JELEK/KUALITAS RENDAH'],
                ['code' => 'home_roof_quality', 'label' => 'Kualitas Atap', 'value' => 'KOSONG'],

                // Wall
                ['code' => 'home_wall', 'label' => 'Jenis Dinding', 'value' => 'BAMBU'],
                ['code' => 'home_wall', 'label' => 'Jenis Dinding', 'value' => 'KAYU/PAPAN'],
                ['code' => 'home_wall', 'label' => 'Jenis Dinding', 'value' => 'LAINNYA'],
                ['code' => 'home_wall', 'label' => 'Jenis Dinding', 'value' => 'SENG'],
                ['code' => 'home_wall', 'label' => 'Jenis Dinding', 'value' => 'TEMBOK'],

                // Wall Quality
                ['code' => 'home_wall_quality', 'label' => 'Kualitas Dinding', 'value' => 'BAGUS/KUALITAS TINGGI'],
                ['code' => 'home_wall_quality', 'label' => 'Kualitas Dinding', 'value' => 'JELEK/KUALITAS RENDAH'],
                ['code' => 'home_wall_quality', 'label' => 'Kualitas Dinding', 'value' => 'KOSONG'],

                // Floor
                ['code' => 'home_floor', 'label' => 'Jenis Lantai', 'value' => 'BAMBU'],
                ['code' => 'home_floor', 'label' => 'Jenis Lantai', 'value' => 'KAYU/PAPAN'],
                ['code' => 'home_floor', 'label' => 'Jenis Lantai', 'value' => 'KERAMIK/GRANIT/MARMER/UBIN/TEGEL/TERASO'],
                ['code' => 'home_floor', 'label' => 'Jenis Lantai', 'value' => 'LAINNYA'],
                ['code' => 'home_floor', 'label' => 'Jenis Lantai', 'value' => 'SEMEN'],
                ['code' => 'home_floor', 'label' => 'Jenis Lantai', 'value' => 'TANAH'],

                // Floor Quality
                ['code' => 'home_floor_quality', 'label' => 'Kualitas Lantai', 'value' => 'BAGUS/KUALITAS TINGGI'],
                ['code' => 'home_floor_quality', 'label' => 'Kualitas Lantai', 'value' => 'JELEK/KUALITAS RENDAH'],
                ['code' => 'home_floor_quality', 'label' => 'Kualitas Lantai', 'value' => 'KOSONG'],

                // Electricity
                ['code' => 'home_electricity', 'label' => 'Sumber Penerangan', 'value' => 'BUKAN LISTRIK'],
                ['code' => 'home_electricity', 'label' => 'Sumber Penerangan', 'value' => 'LISTRIK NON-PLN'],
                ['code' => 'home_electricity', 'label' => 'Sumber Penerangan', 'value' => 'LISTRIK PLN METERAN'],
                ['code' => 'home_electricity', 'label' => 'Sumber Penerangan', 'value' => 'LISTRIK PLN NON METERAN'],

                // Electricity Power
                ['code' => 'home_electricity_power', 'label' => 'Daya Listrik Terpasang', 'value' => '=< 900 WATT'],
                ['code' => 'home_electricity_power', 'label' => 'Daya Listrik Terpasang', 'value' => '> 900 WATT'],
                ['code' => 'home_electricity_power', 'label' => 'Daya Listrik Terpasang', 'value' => 'KOSONG'],

                // Cooking
                ['code' => 'home_cooking', 'label' => 'Bahan Bakar Memasak', 'value' => 'ARANG/KAYU'],
                ['code' => 'home_cooking', 'label' => 'Bahan Bakar Memasak', 'value' => 'LISTRIK/GAS'],
                ['code' => 'home_cooking', 'label' => 'Bahan Bakar Memasak', 'value' => 'MINYAK TANAH'],
                ['code' => 'home_cooking', 'label' => 'Bahan Bakar Memasak', 'value' => 'LAINNYA'],

                // Water
                ['code' => 'home_water', 'label' => 'Sumber Air Minum', 'value' => 'AIR HUJAN'],
                ['code' => 'home_water', 'label' => 'Sumber Air Minum', 'value' => 'AIR KEMASAN/ISI ULANG'],
                ['code' => 'home_water', 'label' => 'Sumber Air Minum', 'value' => 'AIR PERMUKAAN (SUNGAI, DANAU, DLL)'],
                ['code' => 'home_water', 'label' => 'Sumber Air Minum', 'value' => 'LEDENG/PAM'],
                ['code' => 'home_water', 'label' => 'Sumber Air Minum', 'value' => 'SUMUR BOR'],
                ['code' => 'home_water', 'label' => 'Sumber Air Minum', 'value' => 'SUMUR TERLINDUNG'],
                ['code' => 'home_water', 'label' => 'Sumber Air Minum', 'value' => 'SUMUR TIDAK TERLINDUNG'],
                ['code' => 'home_water', 'label' => 'Sumber Air Minum', 'value' => 'LAINNYA'],

                // Home Toilet Ownership
                ['code' => 'home_toilet_ownership', 'label' => 'Memiliki fasilitas Buang Air Besar', 'value' => 'MILIK SENDIRI'],
                ['code' => 'home_toilet_ownership', 'label' => 'Memiliki fasilitas Buang Air Besar', 'value' => 'UMUM/BERSAMA'],
                ['code' => 'home_toilet_ownership', 'label' => 'Memiliki fasilitas Buang Air Besar', 'value' => 'KOSONG'],
                ['code' => 'home_toilet_ownership', 'label' => 'Memiliki fasilitas Buang Air Besar', 'value' => 'LAINNYA'],

                // Stunting
                ['code' => 'stunting_risk', 'label' => 'Resiko Stunting', 'value' => 'BERESIKO STUNTING'],
                ['code' => 'stunting_risk', 'label' => 'Resiko Stunting', 'value' => 'BUKAN TARGET SASARAN'],
                ['code' => 'stunting_risk', 'label' => 'Resiko Stunting', 'value' => 'TIDAK BERESIKO STUNTING'],

                // Gender
                ['code' => 'gender', 'label' => 'Jenis Kelamin', 'value' => 'LAKI-LAKI'],
                ['code' => 'gender', 'label' => 'Jenis Kelamin', 'value' => 'PEREMPUAN'],

                // Strategi Program
                ['code' => 'strategy_program', 'label' => 'Tujuan Program', 'value' => 'MENGURANGI BEBAN PENGELUARAN'],
                ['code' => 'strategy_program', 'label' => 'Tujuan Program', 'value' => 'MENINGKATKAN PENDAPATAN'],
                ['code' => 'strategy_program', 'label' => 'Tujuan Program', 'value' => 'MEMINIMALKAN WILAYAH KANTONG KEMISKINAN'],

                // BNBA Type
                ['code' => 'bnba_type', 'label' => 'Jenis BNBA', 'value' => 'INDIVIDU'],
                ['code' => 'bnba_type', 'label' => 'Jenis BNBA', 'value' => 'KEPALA-KELUARGA'],
            ];
            foreach ($datas as $data) {
                Sy_option::create($data);
            }
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info("Error {$err->getCode()}: {$err->getMessage()}");
        }
    }
}
