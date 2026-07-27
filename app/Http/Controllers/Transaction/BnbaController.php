<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Repositories\Master\DashboardRepository;
use App\Repositories\Master\DestitutionKkRepository;
use App\Repositories\Master\DestitutionNikRepository;
use App\Repositories\Master\RegionRepository;
use App\Repositories\Master\UserAccessRepository;
use App\Repositories\System\OptionRepository;
use App\Repositories\Transaction\ProgramRealizationRepository;
use App\Repositories\Transaction\ProgramRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BnbaController extends Controller
{
    protected $destitution_nik_repo;
    protected $destitution_kk_repo;
    protected $program_repo;
    protected $program_realization_repo;
    protected $option_repo;
    protected $region_repo;
    protected $user_access_repo;
    protected $dashboard_template_repo;

    public function __construct(
        DestitutionNikRepository $destitution_nik_repo,
        DestitutionKkRepository $destitution_kk_repo,
        ProgramRepository $program_repo,
        ProgramRealizationRepository $program_realization_repo,
        OptionRepository $option_repo,
        DashboardRepository $dashboard_template_repo,
        RegionRepository $region_repo,
        UserAccessRepository $user_access_repo
    ) {
        $this->destitution_nik_repo = $destitution_nik_repo;
        $this->destitution_kk_repo = $destitution_kk_repo;
        $this->program_repo = $program_repo;
        $this->program_realization_repo = $program_realization_repo;
        $this->region_repo = $region_repo;
        $this->option_repo = $option_repo;
        $this->dashboard_template_repo = $dashboard_template_repo;
        $this->user_access_repo = $user_access_repo;
    }

    public function detail(Request $request, $source)
    {
        $user = Auth::user();
        $blade_name = '';
        $data = [];
        $map = "P3KE_LEBAK.geojson.json";
        $map_region_type = "3-KECAMATAN";
        $kemdagri_code = $request->kemdagri_code ?? '3602';
        $dashboard_id = $request->dashboard_id ?? 0;
        $back_kemdagri_code = strlen($kemdagri_code) == '6' ? substr($kemdagri_code, 0, 4) : '3602';
        if ($source == 'nik') {
            $subtitle = "SEMUA KECAMATAN";
            if ($kemdagri_code != null) {
                $back_kemdagri_code = strlen($kemdagri_code) == '10' ? substr($kemdagri_code, 0, 6) : '3602';
                $cond = 'mt_destitution_nik.kemdagri_code LIKE "' . $kemdagri_code . '%"';
                $record = $this->region_repo->getRecordByColumn('code', $kemdagri_code);
                if ($record) {
                    $subtitle = substr($record->type, 2) . " " . $record->name;
                    if (substr($record->type, 0, 1) > 2) {
                        $map = "P3KE_" . substr($kemdagri_code, 0, 6) . '.geojson.json';
                        $map_region_type = "4-DESA-KELURAHAN";
                    }
                } else {
                    return redirect(route('home'));
                }
            } else {
                $cond = '1=1';
            }

            $all_data = $this->destitution_nik_repo->getTotalRecordsByCond($cond);
            $bpnt_data = $this->destitution_nik_repo->getTotalRecordsByColumn('is_bpnt', true, $cond);
            $bpum_data = $this->destitution_nik_repo->getTotalRecordsByColumn('is_bpum', true, $cond);
            $bst_data = $this->destitution_nik_repo->getTotalRecordsByColumn('is_bst', true, $cond);
            $pkh_data = $this->destitution_nik_repo->getTotalRecordsByColumn('is_pkh', true, $cond);
            $sembako_data = $this->destitution_nik_repo->getTotalRecordsByColumn('is_sembako', true, $cond);
            $prakerja_data = $this->destitution_nik_repo->getTotalRecordsByColumn('is_prakerja', true, $cond);
            $kur_data = $this->destitution_nik_repo->getTotalRecordsByColumn('is_kur', true, $cond);

            $datas = [
                'total' => number_format($all_data),
                'total_bpnt' => number_format($bpnt_data),
                'total_bpum' => number_format($bpum_data),
                'total_bst' => number_format($bst_data),
                'total_pkh' => number_format($pkh_data),
                'total_sembako' => number_format($sembako_data),
                'total_prakerja' => number_format($prakerja_data),
                'total_kur' => number_format($kur_data),
                'kemdagri_code' => $kemdagri_code,
                'back_kemdagri_code' => $back_kemdagri_code,
                'map' => asset('assets/geojson/' . $map),
                'map_region_type' => $map_region_type,
            ];

            $bnba_flag = false;
            $user = Auth::user();
            if ($user->user_access_id != 0) {
                $user_access = $this->user_access_repo->getRecord($user->user_access_id);
                if ($user_access->bnba_flag) {
                    $bnba_flag = true;
                }
            }

            $data = [
                '_be_page_title' => 'P3KE BNBA INDIVIDU',
                '_be_page_subtitle' => $subtitle,
                '_be_bnba_flag' => $bnba_flag,
                'data' => $datas

            ];
            $blade_name = 'backpage.bnba.detail';
        } elseif ($source == 'kk') {
            $subtitle = "SEMUA KECAMATAN";
            if ($kemdagri_code != null) {
                $back_kemdagri_code = strlen($kemdagri_code) == '10' ? substr($kemdagri_code, 0, 6) : '3602';
                $cond = 'mt_destitution_kk.kemdagri_code LIKE "' . $kemdagri_code . '%"';
                $record = $this->region_repo->getRecordByColumn('code', $kemdagri_code);
                if ($record) {
                    $subtitle = substr($record->type, 2) . " " . $record->name;
                    if (substr($record->type, 0, 1) > 2) {
                        $map = "P3KE_" . substr($kemdagri_code, 0, 6) . '.geojson.json';
                        $map_region_type = "4-DESA-KELURAHAN";
                    }
                } else {
                    return redirect(route('home'));
                }
            } else {
                $cond = '1=1';
            }

            $all_data = $this->destitution_kk_repo->getTotalRecordsByCond($cond);
            $bpnt_data = $this->destitution_kk_repo->getTotalRecordsByColumn('is_bpnt', true, $cond);
            $bpum_data = $this->destitution_kk_repo->getTotalRecordsByColumn('is_bpum', true, $cond);
            $bst_data = $this->destitution_kk_repo->getTotalRecordsByColumn('is_bst', true, $cond);
            $pkh_data = $this->destitution_kk_repo->getTotalRecordsByColumn('is_pkh', true, $cond);
            $sembako_data = $this->destitution_kk_repo->getTotalRecordsByColumn('is_sembako', true, $cond);
            $prakerja_data = $this->destitution_kk_repo->getTotalRecordsByColumn('is_prakerja', true, $cond);
            $kur_data = $this->destitution_kk_repo->getTotalRecordsByColumn('is_kur', true, $cond);

            $datas = [
                'total' => number_format($all_data),
                'total_bpnt' => number_format($bpnt_data),
                'total_bpum' => number_format($bpum_data),
                'total_bst' => number_format($bst_data),
                'total_pkh' => number_format($pkh_data),
                'total_sembako' => number_format($sembako_data),
                'total_prakerja' => number_format($prakerja_data),
                'total_kur' => number_format($kur_data),
                'kemdagri_code' => $kemdagri_code,
                'back_kemdagri_code' => $back_kemdagri_code,
                'map' => asset('assets/geojson/' . $map),
                'map_region_type' => $map_region_type,
            ];

            $bnba_flag = false;
            $user = Auth::user();
            if ($user->user_access_id != 0) {
                $user_access = $this->user_access_repo->getRecord($user->user_access_id);
                if ($user_access->bnba_flag) {
                    $bnba_flag = true;
                }
            }

            $data = [
                '_be_page_title' => 'P3KE BNBA KEPALA KELUARGA',
                '_be_page_subtitle' => $subtitle,
                '_be_bnba_flag' => $bnba_flag,
                'data' => $datas

            ];
            $blade_name = 'backpage.bnba.detail_kk';
        } elseif ($source == 'program') {
            $subtitle = "SEMUA KECAMATAN";
            if ($kemdagri_code != null) {
                $back_kemdagri_code = strlen($kemdagri_code) == '10' ? substr($kemdagri_code, 0, 6) : '3602';
                $cond = 'subdistrict.code LIKE "' . $kemdagri_code . '%"';
                $record = $this->region_repo->getRecordByColumn('code', $kemdagri_code);
                if ($record) {
                    $subtitle = substr($record->type, 2) . " " . $record->name;
                    if (substr($record->type, 0, 1) > 2) {
                        $map = "P3KE_" . substr($kemdagri_code, 0, 6) . '.geojson.json';
                        $map_region_type = "4-DESA-KELURAHAN";
                    }
                } else {
                    return redirect(route('home'));
                }
            } else {
                $cond = '1=1';
            }
            $cond .= " and tr_program.status <> 'DROPPED'";
            $cond .= " and tr_program.deleted_at IS NULL";
            if ($user) {
                $cond .= " and tr_program.budget_year_id = '{$user->budget_year_id}'";
            }

            $all_data = $this->program_repo->getTotalRecordsByCond($cond);

            $datas = [
                'total' => number_format($all_data),
                'total_strategy_1' => number_format(0),
                'total_strategy_2' => number_format(0),
                'total_strategy_3' => number_format(0),
                'kemdagri_code' => $kemdagri_code,
                'back_kemdagri_code' => $back_kemdagri_code,
                'map' => asset('assets/geojson/' . $map),
                'map_region_type' => $map_region_type,
            ];


            $options = $this->option_repo->getRecordsByCode('strategy_program');
            foreach ($options as $key => $value) {
                $total_strategy = $this->program_repo->getTotalRecordsByColumn('program_goal_id', $value->id, $cond);
                $datas['total_strategy_' . $key + 1] = number_format($total_strategy);
            }

            $bnba_flag = false;
            $user = Auth::user();
            if ($user->user_access_id != 0) {
                $user_access = $this->user_access_repo->getRecord($user->user_access_id);
                if ($user_access->bnba_flag) {
                    $bnba_flag = true;
                }
            }

            $data = [
                '_be_page_title' => 'PROGRAM / KEGIATAN / SUB KEGIATAN',
                '_be_page_subtitle' => $subtitle,
                '_be_bnba_flag' => $bnba_flag,
                'strategies' => $options,
                'data' => $datas
            ];
            $blade_name = 'backpage.bnba.detail_program';
        } elseif ($source == 'dashboard' && $dashboard_id <> 0) {
            $datas = [];
            $cond = '1=1';
            $dashboard = $this->dashboard_template_repo->getRecord($dashboard_id);
            $props = json_decode($dashboard->properties, true);

            if (!isset($props['key'])) {
                return redirect(route('home'));
            }
            $options = $this->option_repo->getRecordsByCode($props['key']);
            $subtitle = '';
            $route = route('home');
            $headers = [];
            $column = $props['column'];
            if ($dashboard->source == 'P3KE-INDIVIDU') {
                if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                    $cond .= " and mt_destitution_nik.district_id = '{$props['district_id']}'";
                    $cond .= " and mt_destitution_nik.subdistrict_id = '{$props['subdistrict_id']}'";
                }
                $datas['total'] = 0;
                foreach ($options as $key => $option) {
                    if (isset($props['type']) && $props['type'] == 'SUM') {
                        $count = $this->destitution_nik_repo->getSumRecordsByColumn('mt_destitution_nik.' . $props['column'], $option['id'], $cond, $props['value']);
                    } else {
                        $count = $this->destitution_nik_repo->getTotalRecordsByColumn('mt_destitution_nik.' . $props['column'], $option['id'], $cond);
                    }
                    $datas['total_' . $key + 1] = number_format($count);
                    $datas['total'] += $count;
                    $subtitle = $option->label;
                }
                $datas['total'] = number_format($datas['total']);
                $route = route('master.destitution_nik.get_data');
                $headers = [
                    ["label" => "Sumber Data", "data" => "data_name", "name" => "sy_data.name"],
                    ["label" => "ID P3KE", "data" => "p3ke", "name" => "mt_destitution_nik.p3ke"],
                    ["label" => "Dimuktakhirkan Tahun", "data" => "last_update_year", "name" => "mt_destitution_nik.last_update_year"],
                    ["label" => "Kode Kemdagri", "data" => "subdistrict_code", "name" => "subdistrict.code"],
                    ["label" => "Kecamatan", "data" => "district_name", "name" => "district.name"],
                    ["label" => "Desa/Kelurahan", "data" => "subdistrict_name", "name" => "subdistrict.name"],
                    ["label" => "NIK", "data" => "nik", "name" => "mt_destitution_nik.nik"],
                    ["label" => "Nama", "data" => "name", "name" => "mt_destitution_nik.name"],
                    ["label" => "Desil Kesejahteraan", "data" => "decile", "name" => "mt_destitution_nik.decile"],
                    ["label" => "Persentil", "data" => "percentile", "name" => "mt_destitution_nik.percentile"],
                    ["label" => "Diubah oleh", "data" => "updated_by_name", "name" => "updated_by.name"],
                    ["label" => "Diubah pada", "data" => "updated_at", "name" => "mt_destitution_nik.updated_at"],
                ];
                $column = 'mt_destitution_nik.' . $props['column'];
            } elseif ($dashboard->source == 'P3KE-KEPALA-KELUARGA') {
                if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                    $cond .= " and mt_destitution_kk.district_id = '{$props['district_id']}'";
                    $cond .= " and mt_destitution_kk.subdistrict_id = '{$props['subdistrict_id']}'";
                }
                $datas['total'] = 0;
                foreach ($options as $key => $option) {
                    if (isset($props['type']) && $props['type'] == 'SUM') {
                        $count = $this->destitution_kk_repo->getSumRecordsByColumn('mt_destitution_kk.' . $props['column'], $option['id'], $cond, $props['value']);
                    } else {
                        $count = $this->destitution_kk_repo->getTotalRecordsByColumn('mt_destitution_kk.' . $props['column'], $option['id'], $cond);
                    }
                    $datas['total_' . $key + 1] = number_format($count);
                    $datas['total'] += $count;
                    $subtitle = $option->label;
                }
                $datas['total'] = number_format($datas['total']);
                $route = route('master.destitution_kk.get_data');
                $headers = [
                    ["label" => "Sumber Data", "data" => "data_name", "name" => "sy_data.name"],
                    ["label" => "ID P3KE", "data" => "p3ke", "name" => "mt_destitution_kk.p3ke"],
                    ["label" => "Dimuktakhirkan Tahun", "data" => "last_update_year", "name" => "mt_destitution_kk.last_update_year"],
                    ["label" => "Kode Kemdagri", "data" => "subdistrict_code", "name" => "subdistrict.code"],
                    ["label" => "Kecamatan", "data" => "district_name", "name" => "district.name"],
                    ["label" => "Desa/Kelurahan", "data" => "subdistrict_name", "name" => "subdistrict.name"],
                    ["label" => "NIK", "data" => "nik", "name" => "mt_destitution_kk.nik"],
                    ["label" => "Nama", "data" => "name", "name" => "mt_destitution_kk.name"],
                    ["label" => "Desil Kesejahteraan", "data" => "decile", "name" => "mt_destitution_kk.decile"],
                    ["label" => "Persentil", "data" => "percentile", "name" => "mt_destitution_kk.percentile"],
                    ["label" => "Diubah oleh", "data" => "updated_by_name", "name" => "updated_by.name"],
                    ["label" => "Diubah pada", "data" => "updated_at", "name" => "mt_destitution_kk.updated_at"],
                ];
                $column = 'mt_destitution_kk.' . $props['column'];
            } elseif ($dashboard->source == 'PROGRAM') {
                if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                    $cond .= " and tr_program.district_id = '{$props['district_id']}'";
                    $cond .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                }
                $cond .= " and tr_program.status IN ('ACTIVE')";
                $cond .= " and tr_program.budget_year_id = '{$user->budget_year_id}'";
                $datas['total'] = 0;
                foreach ($options as $key => $option) {
                    if (isset($props['type']) && $props['type'] == 'SUM') {
                        $count = $this->program_repo->getSumRecordsByColumn('tr_program.' . $props['column'], $option['id'], $cond, $props['value']);
                    } else {
                        $count = $this->program_repo->getTotalRecordsByColumn('tr_program.' . $props['column'], $option['id'], $cond);
                    }
                    $datas['total_' . $key + 1] = number_format($count);
                    $datas['total'] += $count;
                    $subtitle = $option->label;
                }
                $datas['total'] = number_format($datas['total']);
                $route = route('program.get_data', ['limit_org' => false, 'condition' => $cond]);
                $headers = [
                    ["label" => "Tahun Anggaran", "data" => "budget_year_name", 'name' => "mt_budget_year.name"],
                    ["label" => "Nomenklatur", "data" => "code", 'name' => "tr_program.code"],
                    ["label" => "Kecamatan", "data" => "district_name", 'name' => "district.name"],
                    ["label" => "Desa/Kelurahan", "data" => "subdistrict_name", 'name' => "subdistrict.name"],
                    ["label" => "Strategi", "data" => "strategy_program_name", 'name' => "program_goal.value"],
                    ["label" => "Perangkat Daerah", "data" => "organization_name", 'name' => "mt_organization.name"],
                    ["label" => "Program", "data" => "program", 'name' => "tr_program.program"],
                    ["label" => "Kegiatan", "data" => "activity", 'name' => "tr_program.activity"],
                    ["label" => "Sub Kegiatan", "data" => "sub_activity", 'name' => "tr_program.sub_activity"],
                    ["label" => "Status", "data" => "status", 'name' => "tr_program.status"],
                    ["label" => "Pagu", "data" => "budget_allocation", 'name' => "tr_program.budget_allocation"],
                    ["label" => "Realisasi", "data" => "budget_realization", 'name' => "tr_program.budget_realization"],
                    ["label" => "Persentase", "data" => "percentage", 'name' => "percentage"],
                    ["label" => "Diubah oleh", "data" => "updated_by_name", 'name' => "updated_by.name"],
                    ["label" => "Diubah pada", "data" => "updated_at", 'name' => "tr_program.updated_at"],
                ];
                $column = 'tr_program.' . $props['column'];
            } elseif ($dashboard->source == 'REALISASI') {
                if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                    $cond .= " and tr_program.district_id = '{$props['district_id']}'";
                    $cond .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                }
                $cond .= " and tr_program.status IN ('ACTIVE')";
                $cond .= " and tr_program.budget_year_id = '{$user->budget_year_id}'";

                $datas['total'] = 0;
                foreach ($options as $key => $option) {
                    if (isset($props['type']) && $props['type'] == 'SUM') {
                        $count = $this->program_realization_repo->getSumRecordsByColumn('tr_program.' . $props['column'], $option['id'], $cond, $props['value']);
                    } else {
                        $count = $this->program_realization_repo->getTotalRecordsByColumn('tr_program.' . $props['column'], $option['id'], $cond);
                    }
                    $datas['total_' . $key + 1] = number_format($count);
                    $datas['total'] += $count;
                    $subtitle = $option->label;
                }
                $datas['total'] = number_format($datas['total']);
                $route = route('program.realization.get_data', ['condition' => $cond, 'source' => 'chart']);
                $headers = [
                    ["label" => "Tahun Anggaran", "data" => "budget_year_name", "name" => "mt_budget_year.name"],
                    ["label" => "Nomenklatur", "data" => "program_code", "name" => "tr_program.code"],
                    ["label" => "Kecamatan", "data" => "district_name", "name" => "district.name"],
                    ["label" => "Desa/Kelurahan", "data" => "subdistrict_name", "name" => "subdistrict.name"],
                    ["label" => "Strategi", "data" => "strategy_program_name", "name" => "program_goal.value"],
                    ["label" => "Perangkat Daerah", "data" => "organization_name", "name" => "mt_organization.name"],
                    ["label" => "Program", "data" => "program_program", "name" => "tr_program.program"],
                    ["label" => "Kegiatan", "data" => "program_activity", "name" => "tr_program.activity"],
                    ["label" => "Sub Kegiatan", "data" => "program_sub_activity", "name" => "tr_program.sub_activity"],
                    ["label" => "Triwulan", "data" => "quarterly", "name" => "tr_program_realization.quarterly"],
                    ["label" => "Pagu", "data" => "program_budget_allocation", "name" => "tr_program.budget_allocation"],
                    ["label" => "Realisasi", "data" => "budget_realization", "name" => "tr_program.budget_realization"],
                    ["label" => "Persentase", "data" => "percentage", "name" => "percentage"],
                    ["label" => "Sasaran Penerima Manfaat", "data" => "target", "name" => "tr_program_realization.target"],
                    ["label" => "Kendala Pelaksanaan", "data" => "implementation_obstacle", "name" => "tr_program_realization.implementation_obstacle"],
                    ["label" => "Besaran Manfaat", "data" => "benefit", "name" => "tr_program_realization.benefit"],
                    ["label" => "Durasi Pemberian Bantuan", "data" => "duration_note", "name" => "tr_program_realization.duration_note"],
                    ["label" => "Diubah oleh", "data" => "updated_by_name", "name" => "updated_by.name"],
                    ["label" => "Diubah pada", "data" => "updated_at", "name" => "tr_program.updated_at"],
                ];
                $column = 'tr_program.' . $props['column'];
            }

            $bnba_flag = false;
            $user = Auth::user();
            if ($user->user_access_id != 0) {
                $user_access = $this->user_access_repo->getRecord($user->user_access_id);
                if ($user_access->bnba_flag) {
                    $bnba_flag = true;
                }
            }

            $data = [
                '_be_page_title' => $dashboard->title,
                '_be_page_subtitle' => $subtitle,
                '_be_bnba_flag' => $bnba_flag,
                '_datatable_headers' => $headers,
                '_datatable_route' => $route,
                '_datatable_column' => $column,
                'options' => $options,
                'data' => $datas
            ];
            // dd($data);
            $blade_name = 'backpage.bnba.detail_dashboard';
        } else {
            return redirect(route('home'));
        }
        return view($blade_name, compact('data'));
    }
}
