<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Repositories\Master\DestitutionKkRepository;
use App\Repositories\Master\DestitutionNikRepository;
use App\Repositories\System\PreferenceRepository;
use App\Repositories\Master\DashboardRepository;
use App\Repositories\Transaction\GalleryRepository;

class MainController extends Controller
{
    protected $dashboard_repo;
    protected $destitution_nik_repo;
    protected $destitution_kk_repo;
    protected $preference_repo;
    protected $gallery_repo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        DashboardRepository $dashboard_repo,
        DestitutionNikRepository $destitution_nik_repo,
        DestitutionKkRepository $destitution_kk_repo,
        PreferenceRepository $preference_repo,
        GalleryRepository $gallery_repo
    ) {
        // $this->middleware('auth');
        $this->dashboard_repo = $dashboard_repo;
        $this->destitution_nik_repo = $destitution_nik_repo;
        $this->destitution_kk_repo = $destitution_kk_repo;
        $this->preference_repo = $preference_repo;
        $this->gallery_repo = $gallery_repo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $stat = $this->preference_repo->getValueByPrefix('app_stats');
        if ($stat == '') {
            $total['destitution_nik'] = 0;
            $total['destitution_kk'] = 0;
            $total['destitution_kk_stunting'] = 0;
            $total['destitution_kk_district'] = 0;
            $total['destitution_kk_district_total'] = 0;
            $total['updated_at'] = '-';
        } else {
            $record = json_decode($stat, true);
            $total['updated_at'] = $record['updated_at'];
            $record = $record['statistics'];
            $total['destitution_nik'] = $record['total_nik'];
            $total['destitution_kk'] = $record['total_kk'];
            $total['destitution_kk_stunting'] = $record['total_kk_stunting'];
            $total['destitution_kk_district'] = $record['total_kk_by_district'];
            $total['destitution_kk_district_total'] = $record['total_kk_by_district_total'];
        }

        $galleries = $this->gallery_repo->getRecords('active_flag = 1');
        if ($galleries->count() > 0) {
            $galleries->toArray();
            for ($i = 0; $i < count($galleries); $i++) {
                $galleries[$i]['image_id'] = get_image($galleries[$i]['image_id']);
            }
        } else {
            $galleries = [];
        }


        $charts = [];
        $data_charts = get_preference('default_dashboard', '[]');
        $data_charts = json_decode($data_charts, true);
        if (count($data_charts) > 0) {
            $cond = "mt_dashboard.id IN ('" . implode("','", $data_charts) . "')";
            $records = $this->dashboard_repo->getOptionRecords($cond);
            if ($records->count() > 0) {
                $charts = $records->toArray();
                $collect = collect($charts);
                $charts = $collect->map(function ($item) {
                    $item['dashboard_id'] = $item['id'];
                    $item['status'] = true;
                    $item['route_ajax'] = route('web.ajax.chart');
                    return $item;
                });
            }
        }

        $data = [
            'total' => $total,
            'gallery' => $galleries,
            'charts' => $charts
        ];

        return view('welcome', compact('data'));
    }
}
