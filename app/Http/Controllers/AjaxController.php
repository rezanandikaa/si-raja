<?php

namespace App\Http\Controllers;

use App\Libraries\DtsenDocumentMapper;
use App\Repositories\CompileRepository;
use App\Repositories\Master\DashboardRepository;
use App\Repositories\Master\DestitutionKkRepository;
use App\Repositories\Master\DestitutionNikRepository;
use App\Repositories\Master\RegionRepository;
use App\Repositories\System\AttachmentRepository;
use App\Repositories\System\OptionRepository;
use App\Repositories\Transaction\ProgramRealizationRepository;
use App\Repositories\Transaction\ProgramRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class AjaxController extends Controller
{
    protected $compile_repo;
    protected $destitution_nik_repo;
    protected $destitution_kk_repo;
    protected $option_repo;
    protected $dashboard_repo;
    protected $region_repo;
    protected $program_repo;
    protected $program_realization_repo;
    protected $attachment_repo;

    public function __construct(
        CompileRepository $compile_repo,
        DestitutionNikRepository $destitution_nik_repo,
        DestitutionKkRepository $destitution_kk_repo,
        OptionRepository $option_repo,
        DashboardRepository $dashboard_repo,
        RegionRepository $region_repo,
        ProgramRepository $program_repo,
        ProgramRealizationRepository $program_realization_repo,
        AttachmentRepository $attachment_repo
    ) {
        $this->compile_repo = $compile_repo;
        $this->destitution_nik_repo = $destitution_nik_repo;
        $this->destitution_kk_repo = $destitution_kk_repo;
        $this->option_repo = $option_repo;
        $this->dashboard_repo = $dashboard_repo;
        $this->region_repo = $region_repo;
        $this->program_repo = $program_repo;
        $this->program_realization_repo = $program_realization_repo;
        $this->attachment_repo = $attachment_repo;
    }

    public function data_select(Request $request)
    {
        $table_name = $request->table;
        $condition = $request->condition ?? '';
        $records = $this->compile_repo->getOptions($table_name, ' ' . $condition);
        return response()->json($records);
    }

    public function data_program(Request $request)
    {
        $program_id = $request->id ?? 0;
        $program = $this->program_repo->getRecord($program_id);
        $records = [];
        if ($program) {
            $records = $program->toArray();
        }
        return response()->json($records);
    }

    /**
     * Potongan kondisi "kolom = nilai" untuk peta.
     *
     * Kolomnya harus ada di daftar kolom sisi server, nilainya di-quote lewat
     * PDO — properti dashboard tidak boleh bisa menyusupkan SQL.
     */
    protected function mapFilterCondition($source, $table_name, $props)
    {
        $column = $props['column'] ?? null;
        $value = $props['value'] ?? null;
        if ($value === null || $value === '' || !$this->chartColumnAllowed($source, $column)) {
            return '';
        }

        return " and {$table_name}.{$column} = " . DB::getPdo()->quote((string) $value);
    }

    /**
     * Daftar kolom yang boleh disebut properti grafik untuk satu sumber.
     *
     * Selalu daftar kolom sungguhan dari sisi server, tidak pernah dari
     * request: nama kolom ikut masuk SQL, jadi asalnya harus tetap.
     */
    protected function allowedChartColumns($source)
    {
        $document_type = DtsenDocumentMapper::typeForSource($source);
        if ($document_type !== null) {
            $table_name = $document_type == DtsenDocumentMapper::TYPE_ART
                ? 'mt_destitution_nik'
                : 'mt_destitution_kk';

            // Kolom dokumen untuk grafik baru, ditambah kolom tabel supaya
            // dashboard lama yang menyebut kolom tabel tetap jalan.
            return array_merge(DtsenDocumentMapper::columns($document_type), Schema::getColumnListing($table_name));
        }
        if ($source == 'PROGRAM') {
            return Schema::getColumnListing('tr_program');
        }
        if ($source == 'REALISASI') {
            return Schema::getColumnListing('tr_program_realization');
        }

        return [];
    }

    /**
     * Kolom kosong berarti properti itu tidak menyaring apa pun, bukan
     * pelanggaran. Kolom di luar daftar ditolak.
     */
    protected function chartColumnAllowed($source, $column)
    {
        if ($column === null || $column === '') {
            return true;
        }

        return in_array($column, $this->allowedChartColumns($source), true);
    }

    /**
     * Grafik yang dikelompokkan langsung menurut kolom dokumen DTSEN.
     *
     * Dipakai kalau "Kelompokkan Berdasarkan" dikosongkan: satu query GROUP BY,
     * bukan satu query per pilihan sy_option seperti jalur lama. Kolom yang
     * tidak ada di daftar putih DtsenDocumentMapper ditolak — nilainya ikut
     * masuk SQL.
     *
     * @return array{labels: array, values: array}|null null kalau properti ini
     *         bukan mode kelompok-kolom.
     */
    protected function documentGroupedSeries($record, $props)
    {
        $document_type = DtsenDocumentMapper::typeForSource($record->source);
        if ($document_type === null || !empty($props['key'])) {
            return null;
        }

        $allowed = DtsenDocumentMapper::columns($document_type);
        $column = $props['column'] ?? null;
        if (!in_array($column, $allowed, true)) {
            return null;
        }

        $sum_column = (($props['type'] ?? null) == 'SUM' && in_array($props['value'] ?? null, $allowed, true))
            ? $props['value']
            : null;

        $table_name = $document_type == DtsenDocumentMapper::TYPE_ART
            ? 'mt_destitution_nik'
            : 'mt_destitution_kk';

        $condition = "1=1";
        // Nilai dari request dicor ke angka dulu sebelum disusun jadi kondisi.
        if (!empty($props['district_id']) && !empty($props['subdistrict_id'])) {
            $condition .= " and {$table_name}.district_id = '" . (int) $props['district_id'] . "'";
            $condition .= " and {$table_name}.subdistrict_id = '" . (int) $props['subdistrict_id'] . "'";
        }

        $repo = $document_type == DtsenDocumentMapper::TYPE_ART
            ? $this->destitution_nik_repo
            : $this->destitution_kk_repo;

        $rows = $repo->getGroupedByColumn($column, $condition, $sum_column);

        return [
            'labels' => $rows->pluck('label')->all(),
            'values' => $rows->pluck('total')->all(),
        ];
    }

    public function chart(Request $request)
    {
        $user = Auth::user();
        $id = $request->id;
        $record = $this->dashboard_repo->getRecord($id);
        $datas = [
            'map' => [],
            'mappoint' => [],
            'pie' => [],
            'bar' => [],
            'column' => [],
        ];
        $budget_year_id = $user == null ? get_preference('default_budget_year', 0) : $user->budget_year_id;

        switch ($record->type) {
            case 'PIE':
                $props = json_decode($record->properties, true);
                if (!$this->chartColumnAllowed($record->source, $props['column'] ?? null)
                    || !$this->chartColumnAllowed($record->source, $props['value'] ?? null)) {
                    break;
                }
                $grouped = $this->documentGroupedSeries($record, $props);
                if ($grouped !== null) {
                    foreach ($grouped['labels'] as $i => $label) {
                        $datas['pie'][] = [
                            'name' => $label,
                            'y' => $grouped['values'][$i],
                            'permalink' => route('bnba.detail', ['source' => 'dashboard', 'dashboard_id' => $record->id]),
                        ];
                    }
                    break;
                }
                if (count($props) > 0) {
                    $options = $this->option_repo->getRecordsByCode($props['key']);
                    $condition = "1=1";
                    if ($record->source == 'P3KE-INDIVIDU') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and mt_destitution_nik.district_id = '{$props['district_id']}'";
                            $condition .= " and mt_destitution_nik.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        foreach ($options as $option) {
                            $data = [];
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->destitution_nik_repo->getSumRecordsByColumn('mt_destitution_nik.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->destitution_nik_repo->getTotalRecordsByColumn('mt_destitution_nik.' . $props['column'], $option['id'], $condition);
                            }
                            $data['name'] = $option['value'];
                            $data['y'] = $count;
                            $data['permalink'] = route('bnba.detail', ['source' => 'dashboard', 'dashboard_id' => $record->id]);
                            array_push($datas['pie'], $data);
                        }
                    }
                    if ($record->source == 'P3KE-KEPALA-KELUARGA') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and mt_destitution_kk.district_id = '{$props['district_id']}'";
                            $condition .= " and mt_destitution_kk.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        foreach ($options as $option) {
                            $data = [];
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->destitution_kk_repo->getSumRecordsByColumn('mt_destitution_kk.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->destitution_kk_repo->getTotalRecordsByColumn('mt_destitution_kk.' . $props['column'], $option['id'], $condition);
                            }
                            $data['name'] = $option['value'];
                            $data['y'] = $count;
                            $data['permalink'] = route('bnba.detail', ['source' => 'dashboard', 'dashboard_id' => $record->id]);
                            array_push($datas['pie'], $data);
                        }
                    }
                    if ($record->source == 'PROGRAM') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and tr_program.district_id = '{$props['district_id']}'";
                            $condition .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $condition .= " and tr_program.status IN ('ACTIVE')";
                        $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                        foreach ($options as $option) {
                            $data = [];
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->program_repo->getSumRecordsByColumn('tr_program.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->program_repo->getTotalRecordsByColumn('tr_program.' . $props['column'], $option['id'], $condition);
                            }
                            $data['name'] = $option['value'];
                            $data['y'] = $count;
                            $data['permalink'] = route('bnba.detail', ['source' => 'dashboard', 'dashboard_id' => $record->id]);
                            array_push($datas['pie'], $data);
                        }
                    }
                    if ($record->source == 'REALISASI') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and tr_program.district_id = '{$props['district_id']}'";
                            $condition .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $condition .= " and tr_program.status IN ('ACTIVE')";
                        $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                        foreach ($options as $option) {
                            $data = [];
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->program_realization_repo->getSumRecordsByColumn('tr_program_realization.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->program_realization_repo->getTotalRecordsByColumn('tr_program_realization.' . $props['column'], $option['id'], $condition);
                            }
                            $data['name'] = $option['value'];
                            $data['y'] = $count;
                            $data['permalink'] = route('bnba.detail', ['source' => 'dashboard', 'dashboard_id' => $record->id]);
                            array_push($datas['pie'], $data);
                        }
                    }
                }
                break;

            case 'MAP':
                $props = json_decode($record->properties, true);
                if ($record->source == 'P3KE-INDIVIDU') {
                    $table_name = "mt_destitution_nik";
                    if (count($props) > 0) {
                        $condition = "1=1";
                        $regions = $this->region_repo->getRecordsByType($props['region']);
                        foreach ($regions as $region) {
                            $data = [];
                            if ($props['region'] == '3-KECAMATAN') {
                                $condition = "{$table_name}.district_id = '{$region->id}'";
                            } else {
                                $condition = "{$table_name}.subdistrict_id = '{$region->id}'";
                            }
                            $condition .= $this->mapFilterCondition($record->source, $table_name, $props);
                            $count = $this->destitution_nik_repo->getTotalRecordsByCond($condition);
                            $data['hc-key'] = $region->code;
                            $data['value'] = $count;
                            $data['permalink'] = route('bnba.detail', ['source' => 'nik', 'kemdagri_code' => $region->code]);
                            array_push($datas['map'], $data);
                        }
                    }
                }
                if ($record->source == 'P3KE-KEPALA-KELUARGA') {
                    $table_name = "mt_destitution_kk";
                    if (count($props) > 0) {
                        $condition = "1=1";
                        $regions = $this->region_repo->getRecordsByType($props['region']);
                        foreach ($regions as $region) {
                            $data = [];
                            if ($props['region'] == '3-KECAMATAN') {
                                $condition = "{$table_name}.district_id = '{$region->id}'";
                            } else {
                                $condition = "{$table_name}.subdistrict_id = '{$region->id}'";
                            }
                            if (isset($props['column']) && isset($props['value']) && $props['column'] != null && $props['value'] != null) {
                                $condition .= " and {$table_name}.{$props['column']} = '{$props['value']}'";
                            }
                            $count = $this->destitution_kk_repo->getTotalRecordsByCond($condition);
                            $data['hc-key'] = $region->code;
                            $data['value'] = $count;
                            $data['permalink'] = route('bnba.detail', ['source' => 'kk', 'kemdagri_code' => $region->code]);
                            array_push($datas['map'], $data);
                        }
                    }
                }
                if ($record->source == 'PROGRAM') {
                    $table_name = "tr_program";
                    if (count($props) > 0) {
                        $condition = "1=1";
                        $regions = $this->region_repo->getRecordsByType($props['region']);
                        foreach ($regions as $region) {
                            $data = [];
                            if ($props['region'] == '3-KECAMATAN') {
                                $condition = "{$table_name}.district_id = '{$region->id}'";
                            } else {
                                $condition = "{$table_name}.subdistrict_id = '{$region->id}'";
                            }
                            $condition .= " and {$table_name}.status <> 'DROPPED'";
                            if (isset($props['column']) && isset($props['value']) && $props['column'] != null && $props['value'] != null) {
                                $condition .= " and {$table_name}.{$props['column']} = '{$props['value']}'";
                            }
                            $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                            $condition .= " and tr_program.status IN ('ACTIVE')";
                            $count = $this->program_repo->getTotalRecordsByCond($condition);
                            $data['hc-key'] = $region->code;
                            $data['value'] = $count;
                            $data['permalink'] = route('bnba.detail', ['source' => 'program', 'kemdagri_code' => $region->code]);
                            array_push($datas['map'], $data);

                            if ($props['is_mappoint']) {
                                $programs = $this->program_repo->getRecords($condition);
                                foreach ($programs as $program) {
                                    $latlng = str_replace("lng", "lon", $program->marker);
                                    $geotagging = json_decode($latlng, true) ?? [];
                                    $geotagging = array_merge(['name' => $program->sub_activity, 'activity' => $program->activity, 'code' => $program->code, 'program' => $program->program, 'budget_allocation' => number_format($program->budget_allocation, 0)], $geotagging);
                                    array_push($datas['mappoint'], $geotagging);
                                }
                            }
                        }
                    }
                }
                if ($record->source == 'REALISASI') {
                    $table_name = "tr_program_realization";
                    if (count($props) > 0) {
                        $condition = "1=1";
                        $regions = $this->region_repo->getRecordsByType($props['region']);
                        foreach ($regions as $region) {
                            $data = [];
                            if ($props['region'] == '3-KECAMATAN') {
                                $condition = "tr_program.district_id = '{$region->id}'";
                            } else {
                                $condition = "tr_program.subdistrict_id = '{$region->id}'";
                            }
                            if (isset($props['column']) && isset($props['value']) && $props['column'] != null && $props['value'] != null) {
                                $condition .= " and {$table_name}.{$props['column']} = '{$props['value']}'";
                            }
                            $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                            $condition .= " and tr_program.status IN ('ACTIVE')";
                            $count = $this->program_realization_repo->getTotalRecordsByCond($condition);
                            $data['hc-key'] = $region->code;
                            $data['value'] = $count;
                            $data['permalink'] = route('bnba.detail', ['source' => 'program', 'kemdagri_code' => $region->code]);
                            array_push($datas['map'], $data);

                            if ($props['is_mappoint']) {
                                $programs = $this->program_realization_repo->getRecordsGroupBy($condition);
                                foreach ($programs as $program) {
                                    $latlng = str_replace("lng", "lon", $program->program_marker);
                                    $geotagging = json_decode($latlng, true) ?? [];
                                    $geotagging = array_merge(['name' => $program->program_sub_activity, 'activity' => $program->program_activity, 'code' => $program->program_code, 'program' => $program->program_program, 'budget_allocation' => number_format($program->program_budget_allocation, 0)], $geotagging);
                                    array_push($datas['mappoint'], $geotagging);
                                }
                            }
                        }
                    }
                }
                break;
            case 'BAR':
                $props = json_decode($record->properties, true);
                if (!$this->chartColumnAllowed($record->source, $props['column'] ?? null)
                    || !$this->chartColumnAllowed($record->source, $props['value'] ?? null)) {
                    break;
                }
                $datas['bar']['interval'] = (isset($props['interval']) ? (int) $props['interval'] : 1);
                $datas['bar']['permalink'] = route('bnba.detail', ['source' => 'dashboard', 'dashboard_id' => $record->id]);
                $grouped = $this->documentGroupedSeries($record, $props);
                if ($grouped !== null) {
                    $datas['bar']['x_axis'] = $grouped['labels'];
                    $datas['bar']['value'] = $grouped['values'];
                    break;
                }
                if (count($props) > 0) {
                    $options = $this->option_repo->getRecordsByCode($props['key']);
                    $condition = "1=1";
                    if ($record->source == 'P3KE-INDIVIDU') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and mt_destitution_nik.district_id = '{$props['district_id']}'";
                            $condition .= " and mt_destitution_nik.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->destitution_nik_repo->getSumRecordsByColumn('mt_destitution_nik.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->destitution_nik_repo->getTotalRecordsByColumn('mt_destitution_nik.' . $props['column'], $option['id'], $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data[] = $count;
                        }
                        $datas['bar']['x_axis'] = $xAxis;
                        $datas['bar']['value'] = $data;
                    }
                    if ($record->source == 'P3KE-KEPALA-KELUARGA') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and mt_destitution_kk.district_id = '{$props['district_id']}'";
                            $condition .= " and mt_destitution_kk.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->destitution_kk_repo->getSumRecordsByColumn('mt_destitution_kk.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->destitution_kk_repo->getTotalRecordsByColumn('mt_destitution_kk.' . $props['column'], $option['id'], $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data[] = $count;
                        }
                        $datas['bar']['x_axis'] = $xAxis;
                        $datas['bar']['value'] = $data;
                    }
                    if ($record->source == 'PROGRAM') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and tr_program.district_id = '{$props['district_id']}'";
                            $condition .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $condition .= " and  tr_program.status IN ('ACTIVE')";
                        $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->program_repo->getSumRecordsByColumn('tr_program.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->program_repo->getTotalRecordsByColumn('tr_program.' . $props['column'], $option['id'], $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data[] = $count;
                        }
                        $datas['bar']['x_axis'] = $xAxis;
                        $datas['bar']['value'] = $data;
                    }
                    if ($record->source == 'REALISASI') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and tr_program.district_id = '{$props['district_id']}'";
                            $condition .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                        $condition .= " and tr_program.status IN ('ACTIVE')";
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->program_realization_repo->getSumRecordsByColumn('tr_program_realization.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->program_realization_repo->getTotalRecordsByColumn('tr_program_realization.' . $props['column'], $option['id'], $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data[] = $count;
                        }
                        $datas['bar']['x_axis'] = $xAxis;
                        $datas['bar']['value'] = $data;
                    }
                }
                break;
            case 'COLUMN':
                $props = json_decode($record->properties, true);
                if (!$this->chartColumnAllowed($record->source, $props['column'] ?? null)
                    || !$this->chartColumnAllowed($record->source, $props['value'] ?? null)) {
                    break;
                }
                $datas['column']['interval'] = (isset($props['interval']) ? (int) $props['interval'] : 1);
                $datas['column']['permalink'] = route('bnba.detail', ['source' => 'dashboard', 'dashboard_id' => $record->id]);
                $grouped = $this->documentGroupedSeries($record, $props);
                if ($grouped !== null) {
                    $datas['column']['x_axis'] = $grouped['labels'];
                    $datas['column']['value'] = $grouped['values'];
                    break;
                }
                if (count($props) > 0) {
                    $options = $this->option_repo->getRecordsByCode($props['key']);
                    $condition = "1=1";
                    if ($record->source == 'P3KE-INDIVIDU') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and mt_destitution_nik.district_id = '{$props['district_id']}'";
                            $condition .= " and mt_destitution_nik.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->destitution_nik_repo->getSumRecordsByColumn('mt_destitution_nik.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->destitution_nik_repo->getTotalRecordsByColumn('mt_destitution_nik.' . $props['column'], $option['id'], $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data[] = $count;
                        }
                        $datas['column']['x_axis'] = $xAxis;
                        $datas['column']['value'] = $data;
                    }
                    if ($record->source == 'P3KE-KEPALA-KELUARGA') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and mt_destitution_kk.district_id = '{$props['district_id']}'";
                            $condition .= " and mt_destitution_kk.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->destitution_kk_repo->getSumRecordsByColumn('mt_destitution_kk.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->destitution_kk_repo->getTotalRecordsByColumn('mt_destitution_kk.' . $props['column'], $option['id'], $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data[] = $count;
                        }
                        $datas['column']['x_axis'] = $xAxis;
                        $datas['column']['value'] = $data;
                    }
                    if ($record->source == 'PROGRAM') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and tr_program.district_id = '{$props['district_id']}'";
                            $condition .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $condition .= " and  tr_program.status IN ('ACTIVE')";
                        $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->program_repo->getSumRecordsByColumn('tr_program.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->program_repo->getTotalRecordsByColumn('tr_program.' . $props['column'], $option['id'], $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data[] = $count;
                        }
                        $datas['column']['x_axis'] = $xAxis;
                        $datas['column']['value'] = $data;
                    }
                    if ($record->source == 'REALISASI') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and tr_program.district_id = '{$props['district_id']}'";
                            $condition .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                        $condition .= " and tr_program.status IN ('ACTIVE')";
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count = $this->program_realization_repo->getSumRecordsByColumn('tr_program_realization.' . $props['column'], $option['id'], $condition, $props['value']);
                            } else {
                                $count = $this->program_realization_repo->getTotalRecordsByColumn('tr_program_realization.' . $props['column'], $option['id'], $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data[] = $count;
                        }
                        $datas['column']['x_axis'] = $xAxis;
                        $datas['column']['value'] = $data;
                    }
                }
                break;

            default:
                $props = json_decode($record->properties, true);
                $datas['column']['interval'] = (isset($props['interval']) ? (int) $props['interval'] : 1);
                if (count($props) > 0) {
                    $options = $this->option_repo->getRecordsByCode($props['key']);
                    $condition = "1=1";
                    $condition .= " and tr_program.budget_year_id = '{$budget_year_id}'";
                    if ($props['name'] == 'PROGRAM-VS-REALIZATION') {
                        if (isset($props['district_id']) && isset($props['subdistrict_id']) && $props['district_id'] != null && $props['subdistrict_id'] != null) {
                            $condition .= " and tr_program.district_id = '{$props['district_id']}'";
                            $condition .= " and tr_program.subdistrict_id = '{$props['subdistrict_id']}'";
                        }
                        $condition .= " and tr_program.status IN ('ACTIVE')";
                        $xAxis = [];
                        $data = [];
                        foreach ($options as $option) {
                            if (isset($props['type']) && $props['type'] == 'SUM') {
                                $count_a = $this->program_repo->getSumRecordsByColumn('tr_program.' . $props['column'], $option['id'], $condition, 'budget_allocation');
                                $count_b = $this->program_realization_repo->getSumRecordsByColumn('tr_program_realization.' . $props['column'], $option['id'], $condition, 'budget_realization');
                            } else {
                                $count_a = $this->program_repo->getTotalRecordsByColumn('tr_program.' . $props['column'], 'id', $condition);
                                $count_b = $this->program_realization_repo->getTotalRecordsByColumn('tr_program_realization.' . $props['column'], 'id', $condition);
                            }
                            $xAxis[] = $option['value'];
                            $data['program'][] = $count_a;
                            $data['realization'][] = $count_b;
                        }
                        $datas['column']['x_axis'] = $xAxis;
                        $datas['column']['value'] = $data;
                    }
                }
                break;
        }
        return response()->json($datas);
    }

    public function chartByType(Request $request)
    {
        $type = $request->type ?? '';
        $sub_type = $request->sub_type ?? '';
        $kemdagri_code = $request->kemdagri_code ?? '3602';
        $referer = $request->referer ?? 'auth';
        $budget_cond = '';
        if ($referer == 'api') {
            switch ($sub_type) {
                case 'realization':
                    # code...
                    break;

                default:
                    $budget_cond = " and tr_program.budget_year_id = '" . get_preference('default_budget_year', 0) . "'";
                    break;
            }
        } else {
            $user = Auth::user();
            switch ($sub_type) {
                case 'realization':
                    # code...
                    break;

                default:
                    $budget_cond = " and tr_program.budget_year_id = '{$user->budget_year_id}'";
                    break;
            }
        }

        if (strlen($kemdagri_code) == 4) {
            $column = 'district_id';
            $cond = "mt_region.type = '3-KECAMATAN' and mt_region.code LIKE '{$kemdagri_code}%'";
        }

        if (strlen($kemdagri_code) == 6) {
            $column = 'subdistrict_id';
            $cond = "mt_region.type = '4-DESA-KELURAHAN' and mt_region.code LIKE '{$kemdagri_code}%'";
        }

        if (strlen($kemdagri_code) == 10) {
            $column = 'subdistrict_id';
            $kemdagri_code = substr($kemdagri_code, 0, 6);
            $cond = "mt_region.type = '4-DESA-KELURAHAN' and mt_region.code LIKE '{$kemdagri_code}%'";
        }

        // type isi nik / kk
        $datas = [
            'map' => [],
            'mappoint' => []
        ];
        if ($type == 'kk') {
            $records = $this->region_repo->getRecords($cond);
            foreach ($records as $value) {
                $data = [];
                $count = $this->destitution_kk_repo->getTotalRecordsByColumn($column, $value->id);
                $data['hc-key'] = $value->code;
                $data['drilldown'] = $value->code;
                $data['value'] = $count;

                if ($column == 'district_id') {
                    switch ($sub_type) {
                        case 'realization':
                            $cond = "tr_program.district_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                            $cond .= $budget_cond;
                            $realizations = $this->program_realization_repo->getRecordsGroupBy($cond);
                            foreach ($realizations as $realization) {
                                $latlng = str_replace("lng", "lon", $realization->program_marker);
                                $geotagging = json_decode($latlng, true) ?? [];
                                $geotagging = array_merge(['name' => $realization->program_sub_activity, 'activity' => $realization->program_activity, 'code' => $realization->program_code, 'program' => $realization->program_program, 'budget_allocation' => number_format($realization->program_budget_allocation, 0), 'sum_budget_realization' => number_format($realization->sum_budget_realization, 0)], $geotagging);
                                array_push($datas['mappoint'], $geotagging);
                            }
                            break;

                        default:
                            $cond = "tr_program.district_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                            $cond .= $budget_cond;
                            $programs = $this->program_repo->getRecords($cond);
                            foreach ($programs as $program) {
                                $latlng = str_replace("lng", "lon", $program->marker);
                                $geotagging = json_decode($latlng, true) ?? [];
                                $geotagging = array_merge(['name' => $program->sub_activity, 'activity' => $program->activity, 'code' => $program->code, 'program' => $program->program, 'budget_allocation' => number_format($program->budget_allocation, 0)], $geotagging);
                                array_push($datas['mappoint'], $geotagging);
                            }
                            break;
                    }
                }
                if ($column == 'subdistrict_id') {
                    switch ($sub_type) {
                        case 'realization':
                            $cond = "tr_program.subdistrict_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                            $cond .= $budget_cond;
                            $realizations = $this->program_realization_repo->getRecordsGroupBy($cond);
                            foreach ($realizations as $realization) {
                                $latlng = str_replace("lng", "lon", $realization->program_marker);
                                $geotagging = json_decode($latlng, true) ?? [];
                                $geotagging = array_merge(['name' => $realization->program_sub_activity], $geotagging);
                                array_push($datas['mappoint'], $geotagging);
                            }
                            break;

                        default:
                            $cond = "tr_program.subdistrict_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                            $cond .= $budget_cond;
                            $programs = $this->program_repo->getRecords($cond);
                            foreach ($programs as $program) {
                                $latlng = str_replace("lng", "lon", $program->marker);
                                $geotagging = json_decode($latlng, true) ?? [];
                                $geotagging = array_merge(['name' => $program->sub_activity], $geotagging);
                                array_push($datas['mappoint'], $geotagging);
                            }
                            break;
                    }
                }
                array_push($datas['map'], $data);
            }
        } elseif ($type == 'nik') {
            $records = $this->region_repo->getRecords($cond);
            foreach ($records as $value) {
                $data = [];
                $count = $this->destitution_nik_repo->getTotalRecordsByColumn($column, $value->id);
                $data['hc-key'] = $value->code;
                $data['drilldown'] = $value->code;
                $data['value'] = $count;

                if ($column == 'district_id') {
                    $cond = "tr_program.district_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                    $cond .= $budget_cond;
                    $programs = $this->program_repo->getRecords($cond);
                    foreach ($programs as $program) {
                        $latlng = str_replace("lng", "lon", $program->marker);
                        $geotagging = json_decode($latlng, true) ?? [];
                        $geotagging = array_merge(['name' => $program->sub_activity, 'activity' => $program->activity, 'code' => $program->code, 'program' => $program->program, 'budget_allocation' => number_format($program->budget_allocation, 0)], $geotagging);
                        array_push($datas['mappoint'], $geotagging);
                    }
                }
                if ($column == 'subdistrict_id') {
                    $cond = "tr_program.subdistrict_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                    $cond .= $budget_cond;
                    $programs = $this->program_repo->getRecords($cond);
                    foreach ($programs as $program) {
                        $latlng = str_replace("lng", "lon", $program->marker);
                        $geotagging = json_decode($latlng, true) ?? [];
                        $geotagging = array_merge(['name' => $program->sub_activity], $geotagging);
                        array_push($datas['mappoint'], $geotagging);
                    }
                }
                array_push($datas['map'], $data);
            }
        } elseif ($type == 'program') {
            $records = $this->region_repo->getRecords($cond);
            foreach ($records as $value) {
                $data = [];
                $count = $this->program_repo->getTotalRecordsByColumn($column, $value->id);
                $data['hc-key'] = $value->code;
                $data['drilldown'] = $value->code;
                $data['value'] = $count;

                if ($column == 'district_id') {
                    $cond = "tr_program.district_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                    $cond .= $budget_cond;
                    $programs = $this->program_repo->getRecords($cond);
                    foreach ($programs as $program) {
                        $latlng = str_replace("lng", "lon", $program->marker);
                        $geotagging = json_decode($latlng, true) ?? [];
                        $geotagging = array_merge(['name' => $program->sub_activity, 'activity' => $program->activity, 'code' => $program->code, 'program' => $program->program, 'budget_allocation' => number_format($program->budget_allocation, 0)], $geotagging);
                        array_push($datas['mappoint'], $geotagging);
                    }
                }
                if ($column == 'subdistrict_id') {
                    $cond = "tr_program.subdistrict_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                    $cond .= $budget_cond;
                    $programs = $this->program_repo->getRecords($cond);
                    foreach ($programs as $program) {
                        $latlng = str_replace("lng", "lon", $program->marker);
                        $geotagging = json_decode($latlng, true) ?? [];
                        $geotagging = array_merge(['name' => $program->sub_activity], $geotagging);
                        array_push($datas['mappoint'], $geotagging);
                    }
                }
                array_push($datas['map'], $data);
            }
        } elseif ($type == 'realization') {
            $records = $this->region_repo->getRecords($cond);
            foreach ($records as $value) {
                $data = [];
                $count = $this->program_repo->getTotalRecordsByColumn($column, $value->id);
                $data['hc-key'] = $value->code;
                $data['drilldown'] = $value->code;
                $data['value'] = $count;

                if ($column == 'district_id') {
                    $cond = "tr_program.district_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                    $cond .= $budget_cond;
                    $programs = $this->program_repo->getRecords($cond);
                    foreach ($programs as $program) {
                        $latlng = str_replace("lng", "lon", $program->marker);
                        $geotagging = json_decode($latlng, true) ?? [];
                        $geotagging = array_merge(['name' => $program->sub_activity, 'activity' => $program->activity, 'code' => $program->code, 'program' => $program->program, 'budget_allocation' => number_format($program->budget_allocation, 0)], $geotagging);
                        array_push($datas['mappoint'], $geotagging);
                    }
                }
                if ($column == 'subdistrict_id') {
                    $cond = "tr_program.subdistrict_id = '{$value->id}' and tr_program.status = 'ACTIVE' ";
                    $cond .= $budget_cond;
                    $programs = $this->program_repo->getRecords($cond);
                    foreach ($programs as $program) {
                        $latlng = str_replace("lng", "lon", $program->marker);
                        $geotagging = json_decode($latlng, true) ?? [];
                        $geotagging = array_merge(['name' => $program->sub_activity], $geotagging);
                        array_push($datas['mappoint'], $geotagging);
                    }
                }
                array_push($datas['map'], $data);
            }
        } else {
            $records = $this->region_repo->getRecords($cond);
            foreach ($records as $value) {
                $data = [];
                $count = $this->destitution_nik_repo->getTotalRecordsByColumn($column, $value->id);
                $data['hc-key'] = $value->code;
                $data['drilldown'] = $value->code;
                $data['value'] = $count;
                array_push($datas['map'], $data);
            }
        }
        return response()->json($datas);
    }

    public function upload(Request $request)
    {
        $result = [];
        $result['data'] = [];

        $imWidth = $request->width ?? 100;
        $imHeight = $request->height ?? 100;

        $validated = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first(),
                'data' => [
                    'id' => 0
                ]
            ];
            return response()->json($result);
        }

        DB::beginTransaction();
        try {
            $image = $request->file('file');
            $reference_name = $request->reference_name ?? '';
            $reference_id = $request->reference_id ?? 0;
            $imageName = time() . '.' . 'png';
            // now you are able to resize the instance
            $img = Image::make($image->getRealPath());            // now you are able to resize the instance
            $img->resize($imWidth, $imHeight);
            // $image->storeAs('public', $imageName);
            $path = storage_path('app/public/images') . '/' . $imageName;
            $img->save($path);
            // File::put($path, $image);
            $data = [
                'reference_name' => $reference_name,
                'reference_id' => $reference_id,
                'file_name' => $imageName,
                'file_name_original' => $imageName,
                'extension' => 'png',
                'size' => $image->getSize(),
                'path' => "storage/images/" . $imageName,
            ];
            $id = $this->attachment_repo->insertRecord($data);
            DB::commit();
            $result['status'] = 'OK';
            $result['message'] = 'Success';
            $result['data']['link'] = env('APP_URL') . "/storage/" . $imageName;
            $result['data']['id'] = $id;
            return response()->json($result);
        } catch (Exception $err) {
            DB::rollBack();
            $result['status'] = 'FAIL';
            $result['message'] = 'Failed upload image: ' . $err->getMessage();
            $result['data']['id'] = 0;
            return response()->json($result);
        }
    }
}
