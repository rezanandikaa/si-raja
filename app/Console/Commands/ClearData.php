<?php

namespace App\Console\Commands;

use App\Models\Master\Mt_destitution_kk;
use App\Models\Master\Mt_destitution_nik;
use App\Models\System\Sy_file;
use App\Models\System\Sy_import;
use App\Models\System\Sy_log_activity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baduyengine:clear-data {--option=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = 'There was no data deleted';
        DB::beginTransaction();
        try {
            $opt = $this->option('option');
            switch ($opt) {
                case 'log-only':
                    Sy_log_activity::whereRaw('1=1')->delete();
                    $message = 'Log activity was cleared all';
                    break;
                case 'p3ke-nik-only':
                    Mt_destitution_nik::where('data_id', source_data_active())->whereRaw('1=1')->delete();
                    $message = 'Master P3KE NIK was cleared all';
                    break;
                case 'p3ke-kk-only':
                    Mt_destitution_kk::where('data_id', source_data_active())->whereRaw('1=1')->delete();
                    $message = 'Master P3KE KK was cleared all';
                    break;
                case 'import-only':
                    Sy_import::whereRaw('1=1')->delete();
                    $message = 'Log import was cleared all';
                    break;
                default:
                    # code...
                    break;
            }
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            $message = "Error {$err->getCode()}: {$err->getMessage()}";
        }
        $this->info($message);
    }
}
