<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mt_dashboard;
use App\Repositories\CompileRepository;
use App\Repositories\Master\DashboardRepository;
use App\Repositories\Transaction\DashboardRepository as TransactionDashboardRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $dashboard_repo;
    protected $dashboard_template_repo;

    public function __construct(
        CompileRepository $compile_repo,
        DashboardRepository $dashboard_template_repo,
        TransactionDashboardRepository $dashboard_repo
    )
    {
        $this->route_prefix = 'master.dashboard.';
        $this->compile_repo = $compile_repo;
        $this->dashboard_template_repo = $dashboard_template_repo;
        $this->dashboard_repo = $dashboard_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Data Grafis',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Data Grafis',
            '_be_breadcrumbs' => ['Master Data','Master Data Grafis','Daftar Data Grafis'],
            '_be_insert' => route('master.dashboard.insert')
        ];
        return view('backpage.master_dashboard.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Mt_dashboard::leftJoin('mt_user as updated_by','mt_dashboard.updated_by_id','updated_by.id')
                ->whereNull('mt_dashboard.deleted_at')
                ->select(
                    'mt_dashboard.*',
                    'updated_by.name as updated_by_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->editColumn('active_flag', function($data) {
                    return '<span class="badge light badge-'.($data->active_flag ? 'success':'danger').'">'.($data->active_flag ? 'Aktif':'Nonaktif').'</span>';
                })
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    // $btn = '<div class="btn-group">
                    //     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a href="'.route($this->route_prefix.'edit_properties',$data->id).'" class="btn btn-sm btn-warning"><i class="fa fa-cogs"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.dashboard.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',$data->id).'" class="dropdown-item">Ubah</a>
                            <a href="'.route($this->route_prefix.'edit_properties',$data->id).'" class="dropdown-item">Properti</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('master.dashboard.delete').'" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['active_flag','action'])
                ->make(true);
        }
    }

    public function get_form()
    {
        $fields = [];

        $fields['title'] = [
            'label' => 'Judul Info Grafis',
            'name' => 'title',
            'placeholder' => 'Judul Info Grafis',
            'type' => 'text',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Judul Info Grafis wajib diisi'
        ];

        $fields['source'] = [
            'label' => 'Sumber Data',
            'name' => 'source',
            'placeholder' => 'Sumber Data',
            'type' => 'select',
            'options' => ['P3KE-INDIVIDU', 'P3KE-KEPALA-KELUARGA', 'PROGRAM', 'REALISASI'],
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Sumber Data wajib diisi'
        ];

        $fields['type'] = [
            'label' => 'Jenis Bagan',
            'name' => 'type',
            'placeholder' => 'Jenis Bagan',
            'type' => 'select',
            'options' => ['PIE', 'MAP', 'BAR', 'COLUMN', 'CUSTOM'],
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Jenis Bagan wajib diisi'
        ];

        $fields['active_flag'] = [
            'label' => 'Status',
            'name' => 'active_flag',
            'placeholder' => 'Status',
            'type' => 'checkbox',
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Status wajib diisi'
        ];

        return $fields;
    }

    public function get_form_props($record)
    {
        $fields = [];
        $columns = [];
        if ($record['source'] == 'P3KE-INDIVIDU') {
            $columns = Schema::getColumnListing('mt_destitution_nik');
        }
        if ($record['source'] == 'P3KE-KEPALA-KELUARGA') {
            $columns = Schema::getColumnListing('mt_destitution_kk');
        }
        if ($record['source'] == 'PROGRAM') {
            $columns = Schema::getColumnListing('tr_program');
        }
        if ($record['source'] == 'REALISASI') {
            $columns = Schema::getColumnListing('tr_program_realization');
        }

        switch ($record['type']) {
            case 'PIE':
            case 'BAR':
            case 'COLUMN':
                $fields['key'] = [
                    'label' => 'Label Data',
                    'name' => 'key',
                    'placeholder' => 'Label Data',
                    'type' => 'data',
                    'data_table' => 'custom_options',
                    'data_condition' => '',
                    'data_extra' => '',
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Label Data wajib diisi'
                ];
                $fields['column'] = [
                    'label' => 'Nama Kolom',
                    'name' => 'column',
                    'placeholder' => 'Nama Kolom',
                    'type' => 'select',
                    'options' => $columns,
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Nama Kolom wajib diisi'
                ];
                $fields['type'] = [
                    'label' => 'Jenis',
                    'name' => 'type',
                    'placeholder' => 'Jenis',
                    'type' => 'select',
                    'options' => ['SUM','COUNT'],
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Nilai wajib diisi'
                ];
                $fields['value'] = [
                    'label' => 'Nilai',
                    'name' => 'value',
                    'placeholder' => 'Nilai',
                    'type' => 'select',
                    'options' => $columns,
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Nilai wajib diisi'
                ];
                $fields['district_id'] = [
                    'label' => 'Kecamatan',
                    'name' => 'district_id',
                    'placeholder' => 'Kecamatan',
                    'type' => 'data',
                    'data_table' => 'mt_region',
                    'data_condition' => ' and type = "3-KECAMATAN"',
                    'data_extra' => '',
                    'required' => false,
                    'show_only' => false,
                    'validate_message' => 'Kecamatan wajib diisi'
                ];
                $fields['subdistrict_id'] = [
                    'label' => 'Desa/Kelurahan',
                    'name' => 'subdistrict_id',
                    'placeholder' => 'Desa/Kelurahan',
                    'type' => 'data',
                    'data_table' => 'mt_region',
                    'data_condition' => ' and type = "4-DESA-KELURAHAN"',
                    'data_extra' => '',
                    'required' => false,
                    'show_only' => false,
                    'validate_message' => 'Desa/Kelurahan wajib diisi'
                ];
                if (in_array($record['type'], ['BAR', 'COLUMN'])){
                    $fields['interval'] = [
                        'label' => 'Jarak Antar Data',
                        'name' => 'interval',
                        'placeholder' => 'Jarak Antar Data',
                        'type' => 'number',
                        'required' => true,
                        'show_only' => false,
                        'validate_message' => 'Jarak Antar Data wajib diisi'
                    ];
                }
                break;
            case 'CUSTOM':
                $fields['name'] = [
                    'label' => 'Grafik',
                    'name' => 'name',
                    'placeholder' => 'Grafik',
                    'type' => 'select',
                    'options' => ['PROGRAM-VS-REALIZATION'],
                    'data_extra' => '',
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Grafik wajib diisi'
                ];
                $fields['key'] = [
                    'label' => 'Label Data',
                    'name' => 'key',
                    'placeholder' => 'Label Data',
                    'type' => 'data',
                    'data_table' => 'custom_options',
                    'data_condition' => '',
                    'data_extra' => '',
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Label Data wajib diisi'
                ];
                $fields['column'] = [
                    'label' => 'Nama Kolom',
                    'name' => 'column',
                    'placeholder' => 'Nama Kolom',
                    'type' => 'select',
                    'options' => $columns,
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Nama Kolom wajib diisi'
                ];
                $fields['type'] = [
                    'label' => 'Jenis',
                    'name' => 'type',
                    'placeholder' => 'Jenis',
                    'type' => 'select',
                    'options' => ['SUM','COUNT'],
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Nilai wajib diisi'
                ];
                $fields['value'] = [
                    'label' => 'Nilai',
                    'name' => 'value',
                    'placeholder' => 'Nilai',
                    'type' => 'select',
                    'options' => $columns,
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Nilai wajib diisi'
                ];
                $fields['district_id'] = [
                    'label' => 'Kecamatan',
                    'name' => 'district_id',
                    'placeholder' => 'Kecamatan',
                    'type' => 'data',
                    'data_table' => 'mt_region',
                    'data_condition' => ' and type = "3-KECAMATAN"',
                    'data_extra' => '',
                    'required' => false,
                    'show_only' => false,
                    'validate_message' => 'Kecamatan wajib diisi'
                ];
                $fields['subdistrict_id'] = [
                    'label' => 'Desa/Kelurahan',
                    'name' => 'subdistrict_id',
                    'placeholder' => 'Desa/Kelurahan',
                    'type' => 'data',
                    'data_table' => 'mt_region',
                    'data_condition' => ' and type = "4-DESA-KELURAHAN"',
                    'data_extra' => '',
                    'required' => false,
                    'show_only' => false,
                    'validate_message' => 'Desa/Kelurahan wajib diisi'
                ];
                $fields['interval'] = [
                    'label' => 'Jarak Antar Data',
                    'name' => 'interval',
                    'placeholder' => 'Jarak Antar Data',
                    'type' => 'number',
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Jarak Antar Data wajib diisi'
                ];
                break;
            case 'MAP':
                $fields['region'] = [
                    'label' => 'Wilayah',
                    'name' => 'region',
                    'placeholder' => 'Wilayah',
                    'type' => 'select',
                    'options' => ['3-KECAMATAN', '4-DESA-KELURAHAN'],
                    'required' => true,
                    'show_only' => false,
                    'validate_message' => 'Wilayah wajib diisi'
                ];
                $fields['column'] = [
                    'label' => 'Nama Kolom',
                    'name' => 'column',
                    'placeholder' => 'Nama Kolom',
                    'type' => 'select',
                    'options' => $columns,
                    'required' => false,
                    'show_only' => false,
                    'validate_message' => 'Nama Kolom wajib diisi'
                ];
                $fields['value'] = [
                    'label' => 'Nilai',
                    'name' => 'value',
                    'placeholder' => 'Nilai',
                    'type' => 'data',
                    'data_table' => 'sy_option',
                    'data_condition' => '',
                    'data_extra' => '',
                    'required' => false,
                    'show_only' => false,
                    'validate_message' => 'Nilai wajib diisi'
                ];
                if(in_array($record['source'], ['PROGRAM', 'REALISASI'])) {
                    $fields['is_mappoint'] = [
                        'label' => 'Marker Point Map',
                        'name' => 'is_mappoint',
                        'placeholder' => 'Marker Point Map',
                        'type' => 'checkbox',
                        'required' => false,
                        'show_only' => false,
                        'validate_message' => 'Marker Point Map wajib diisi'
                    ];
                }
                break;

            default:
                // return redirect()->back();
                break;
        }

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Data Grafis',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Data Grafis baru',
            '_be_breadcrumbs' => ['Master Data','Master Data Grafis','Tambah Data Grafis'],
            '_be_card_title' => 'Tambah Data Grafis',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.dashboard.store'),
            '_be_home' => route('master.dashboard.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_dashboard.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), $this->compile_repo->validateRule($this->get_form()));
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['title'] = $request->title;
        $data['source'] = $request->source;
        $data['type'] = $request->type;
        $data['active_flag'] = $request->active_flag ?? 0;
        $data['properties'] = json_encode([]);

        try {
            DB::beginTransaction();
            $this->dashboard_template_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Data Grafis Pengguna', 201);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logbook($e->getMessage(), $e->getCode());
            $result = [
                'status' => 'FAIL',
                'message' => $e->getMessage()
            ];
        }
        return response()->json($result);
    }

    public function edit($id)
    {
        $data = [];
        $id = (int) $id;
        if($id == 0){
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $datas = $this->dashboard_template_repo->getRecord($id)->toArray();

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah Data Grafis',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Data Grafis',
            '_be_breadcrumbs' => ['Master Data','Master Data Grafis','Ubah Data Grafis'],
            '_be_card_title' => 'Ubah Data Grafis',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.dashboard.update',$id),
            '_be_home' => route('master.dashboard.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_dashboard.form',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), $this->compile_repo->validateRule($this->get_form()));
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['title'] = $request->title;
        $data['source'] = $request->source;
        $data['type'] = $request->type;
        $data['active_flag'] = $request->active_flag ?? 0;

        try {
            DB::beginTransaction();
            $record = $this->dashboard_template_repo->getRecord($id);
            if ($record->type != $data['type'] || $record->source != $data['source']){
                $data['properties'] = json_encode([]);
            }
            $this->dashboard_template_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Data Grafis Pengguna');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logbook($e->getMessage(), $e->getCode());
            $result = [
                'status' => 'FAIL',
                'message' => $e->getMessage()
            ];
        }
        return response()->json($result);
    }

    public function destroy(Request $request)
    {
        $id = $request->id ?? 0;
        if($id != 0){
            try {
                DB::beginTransaction();
                $this->dashboard_template_repo->deleteRecord($id);
                $this->dashboard_repo->deleteRecordByDashboardId($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Data Grafis Pengguna');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                logbook($e->getMessage(), $e->getCode());
                $result = [
                    'status' => 'FAIL',
                    'message' => $e->getMessage()
                ];
            }
        }
        return response()->json($result);
    }

    public function edit_properties($id)
    {
        $data = [];
        $id = (int) $id;
        if($id == 0){
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $datas = [];
        $record = $this->dashboard_template_repo->getRecord($id);
        if ($record) {
            $record = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $datas = json_decode($record['properties'], true);
        $forms = $this->get_form_props($record);
        if (in_array($record['type'], ['BAR','COLUMN','PIE','CUSTOM']) && isset($datas['district_id']) && $datas['district_id'] != 0 && $datas['district_id'] != '') {
            $forms['subdistrict_id']['data_condition'] = $forms['subdistrict_id']['data_condition'] . ' and mt_region.parent_id = "'.$datas['district_id'].'"';
        }

        $data = [
            'fields' => $forms,
            'datas' => $datas,
            '_be_page_title' => 'Ubah Data Grafis',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Data Grafis',
            '_be_breadcrumbs' => ['Master Data','Master Data Grafis','Ubah Data Grafis'],
            '_be_card_title' => 'Ubah Data Grafis',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.dashboard.update_properties',$id),
            '_be_home' => route('master.dashboard.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_dashboard.form',compact('data'));
    }

    public function update_properties(Request $request, $id)
    {
        $record = $this->dashboard_template_repo->getRecord($id);
        if ($record) {
            $record = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $validated = Validator::make($request->all(), $this->compile_repo->validateRule($this->get_form_props($record)));
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $record = $this->dashboard_template_repo->getRecord($id);

        $data = [];
        switch ($record->type) {
            case 'CUSTOM':
                $data['name'] = $request->name;
            case 'PIE':
            case 'BAR':
            case 'COLUMN':
                $data['key'] = $request->key;
                $data['column'] = $request->column;
                $data['type'] = $request->type;
                $data['value'] = $request->value;
                $data['district_id'] = $request->district_id;
                $data['subdistrict_id'] = $request->subdistrict_id;
                if (in_array($record->type, ['BAR', 'COLUMN', 'CUSTOM'])) {
                    $data['interval'] = $request->interval;
                }
                break;
            case 'MAP':
                $data['region'] = $request->region;
                $data['column'] = $request->column;
                $data['value'] = $request->value;
                $data['is_mappoint'] = (bool) $request->is_mappoint;
                break;

            default:
                # code...
                break;
        }

        try {
            DB::beginTransaction();
            if (isset($data['interval']) && $data['interval'] <= 0){
                $data['interval'] = 1;
            }
            $this->dashboard_template_repo->updateRecord($id, ['properties' => json_encode($data)]);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Data Grafis Pengguna');
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logbook($e->getMessage(), $e->getCode());
            $result = [
                'status' => 'FAIL',
                'message' => $e->getMessage()
            ];
        }
        return response()->json($result);
    }
}
