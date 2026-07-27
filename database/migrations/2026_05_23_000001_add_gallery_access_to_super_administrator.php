<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Restore Zaky to role 1 in case a previous migration set it to 0
        DB::table('mt_user')->where('email', 'user1@example.com')->update(['user_access_id' => 1]);

        $allModules = [
            'tr_dashboard',           // Grafik
            'tr_gallery',             // Galeri
            'tr_program',             // Kegiatan - Rencana
            'tr_program_realization', // Kegiatan - Realisasi
            'tr_download',            // Unduhan
            'mt_destitution_nik',     // Data Master
            'mt_destitution_kk',
            'mt_program_template',
            'mt_dashboard',
            'mt_budget_source',
            'mt_organization',
            'mt_budget_year',
            'mt_user_access',
            'mt_user',
            'sy_preference',          // Sistem
            'sy_file',
            'sy_option',
            'sy_import',
            'sy_data',
            'sy_log_activity',
        ];

        $record = DB::table('mt_user_access')->where('id', 1)->first();
        if (!$record) {
            return;
        }

        $existing = collect(json_decode($record->access_module, true) ?? []);
        $modules = $existing->toArray();

        foreach ($allModules as $moduleId) {
            if (!$existing->contains('module', $moduleId)) {
                $modules[] = ['module' => $moduleId, 'active_flag' => true, 'read_all_flag' => true];
            }
        }

        DB::table('mt_user_access')->where('id', 1)->update(['access_module' => json_encode(array_values($modules))]);
    }

    public function down(): void
    {
        $record = DB::table('mt_user_access')->where('id', 1)->first();
        if (!$record) {
            return;
        }

        $keep = ['mt_user', 'mt_user_access'];
        $modules = array_values(array_filter(
            json_decode($record->access_module, true) ?? [],
            fn($m) => in_array($m['module'], $keep)
        ));

        DB::table('mt_user_access')->where('id', 1)->update(['access_module' => json_encode($modules)]);
        DB::table('mt_user')->where('email', 'user1@example.com')->update(['user_access_id' => 1]);
    }
};
