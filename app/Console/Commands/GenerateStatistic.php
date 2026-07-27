<?php

namespace App\Console\Commands;

use App\Models\Master\Mt_destitution_kk;
use App\Repositories\Master\DestitutionKkRepository;
use App\Repositories\Master\DestitutionNikRepository;
use App\Repositories\System\PreferenceRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateStatistic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baduyengine:statistic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For Generate Statistic Home Page';
    protected $preference_repo;
    protected $destitution_nik_repo;
    protected $destitution_kk_repo;

    public function __construct(PreferenceRepository $preference_repo,
        DestitutionNikRepository $destitution_nik_repo,
        DestitutionKkRepository $destitution_kk_repo)
    {
        parent::__construct();
        $this->preference_repo = $preference_repo;
        $this->destitution_nik_repo = $destitution_nik_repo;
        $this->destitution_kk_repo = $destitution_kk_repo;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $district = Mt_destitution_kk::leftJoin('mt_region', 'mt_destitution_kk.district_id', 'mt_region.id')
            ->when(get_preference('default_percentile', 2) > 0, function ($q){
                $q->where('mt_destitution_kk.percentile', '<=', get_preference('default_percentile', 2));
            })
            ->groupBy('mt_destitution_kk.district_id')
            ->selectRaw(
                'mt_region.name as region_name,
                COUNT(*) as count'
            )
            ->orderByDesc('count')
            ->first();

        $subdistrict = Mt_destitution_kk::leftJoin('mt_region', 'mt_destitution_kk.subdistrict_id', 'mt_region.id')
            ->when(get_preference('default_percentile', 2) > 0, function ($q){
                $q->where('mt_destitution_kk.percentile', '<=', get_preference('default_percentile', 2));
            })
            ->groupBy('mt_destitution_kk.subdistrict_id')
            ->selectRaw(
                'mt_region.name as region_name,
                COUNT(*) as count'
            )
            ->orderByDesc('count')
            ->first();

        $stats = [];
        $stats['total_nik'] = $this->destitution_nik_repo->getTotalRecordsByCond();
        $stats['total_kk'] = $this->destitution_kk_repo->getTotalRecordsByCond();
        $stats['total_kk_stunting'] = $this->destitution_kk_repo->getTotalRecordsByCond('option_stunting_risk.value = "BERESIKO STUNTING"');

        $stats['total_kk_by_district'] = $district ? ucwords(strtolower($district->region_name)) : null;
        $stats['total_kk_by_district_total'] = $district ? $district->count : 0;

        $stats['total_kk_by_subdistrict'] = $subdistrict ? ucwords(strtolower($subdistrict->region_name)) : null;
        $stats['total_kk_by_subdistrict_total'] = $subdistrict ? $subdistrict->count : 0;

        $value = [
            'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'statistics' => $stats
        ];

        DB::beginTransaction();
        try {
            $messages = "Successfully Update Statistics";
            $preference = $this->preference_repo->getValueByPrefix('app_stats', 'id');
            if($preference != '') {
                $this->preference_repo->updateRecord($preference, ['value' => json_encode($value)]);
            } else {
                $this->preference_repo->insertRecord([
                    'name' => 'AUTO STATISTIK',
                    'key' => 'app_stats',
                    'value' => json_encode($value),
                    'type' => 'hidden'
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            $messages = "Error Generate Statistics: {$e->getMessage()}";
            Log::info($messages);
        }
        $this->info($messages);
    }
}
