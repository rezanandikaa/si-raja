<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mt_destitution_kk;
use App\Models\Transaction\Tr_program_realization_bnba;
use App\Repositories\CompileRepository;
use App\Repositories\Master\DestitutionKkRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DestitutionKkController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $destitution_kk_repo;

    public function __construct(
        CompileRepository $compile_repo,
        DestitutionKkRepository $destitution_kk_repo
    )
    {
        $this->route_prefix = 'master.destitution_kk.';
        $this->compile_repo = $compile_repo;
        $this->destitution_kk_repo = $destitution_kk_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar P3KE Kepala Keluarga',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar P3KE Kepala Keluarga',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Kepala Keluarga','Daftar P3KE Kepala Keluarga'],
            '_be_insert' => route('master.destitution_kk.insert')
        ];
        return view('backpage.master_destitution_kk.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Mt_destitution_kk::leftJoin('mt_user as updated_by','mt_destitution_kk.updated_by_id','updated_by.id')
                ->leftJoin('mt_region as province', 'mt_destitution_kk.province_id', 'province.id')
                ->leftJoin('mt_region as regency', 'mt_destitution_kk.regency_id', 'regency.id')
                ->leftJoin('mt_region as district', 'mt_destitution_kk.district_id', 'district.id')
                ->leftJoin('mt_region as subdistrict', 'mt_destitution_kk.subdistrict_id', 'subdistrict.id')
                ->leftJoin('sy_data', 'mt_destitution_kk.data_id', 'sy_data.id')
                ->whereNull('mt_destitution_kk.deleted_at')
                ->where('mt_destitution_kk.data_id', source_data_active())
                ->when($request->kemdagri_code != null && $request->kemdagri_code != '', function($q) use ($request) {
                    $q->where('mt_destitution_kk.kemdagri_code', 'LIKE', "{$request->kemdagri_code}%");
                })
                ->when($request->condition != null && $request->condition != '', function($q) use ($request) {
                    $q->whereRaw($request->condition);
                })
                ->when(get_preference('default_percentile', 2) > 0, function ($q){
                    $q->where('mt_destitution_kk.percentile', '<=', get_preference('default_percentile', 2));
                })
                ->select(
                    'mt_destitution_kk.*',
                    DB::raw("IFNULL(district.name,'-') as district_name"),
                    DB::raw("IFNULL(subdistrict.name,'-') as subdistrict_name"),
                    DB::raw("IFNULL(subdistrict.code,'-') as subdistrict_code"),
                    DB::raw("IFNULL(sy_data.name,'-') as data_name"),
                    'updated_by.name as updated_by_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                // ->editColumn('active_flag', function($data) {
                //     return '<span class="badge light badge-'.($data->active_flag ? 'success':'danger').'">'.($data->active_flag ? 'Aktif':'Nonaktif').'</span>';
                // })
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    // $btn = '<div class="btn-group">
                    //     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.destitution_kk.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'detail',$data->id).'" class="dropdown-item">Lihat</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function get_form()
    {
        $fields = [];

        $fields['user_access_id'] = [
            'label' => 'Hak Akses Pengguna',
            'name' => 'user_access_id',
            'placeholder' => 'Hak Akses Pengguna',
            'type' => 'data',
            'data_table' => 'mt_user',
            'data_condition' => '',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Hak Akses Pengguna wajib diisi'
        ];

        $fields['file_id'] = [
            'label' => 'Hak Akses Pengguna',
            'name' => 'file_id',
            'placeholder' => 'Hak Akses Pengguna',
            'type' => 'data',
            'data_table' => 'sy_file',
            'data_condition' => '',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Hak Akses Pengguna wajib diisi'
        ];

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah P3KE Kepala Keluarga',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan P3KE Kepala Keluarga baru',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Kepala Keluarga','Tambah P3KE Kepala Keluarga'],
            '_be_card_title' => 'Tambah P3KE Kepala Keluarga',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.destitution_kk.store'),
            '_be_home' => route('master.destitution_kk.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_destitution_kk.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'user_access_name' => 'required|max:100|unique:mt_user_access,user_access_name',
            'user_access_desc' => 'required|max:300'
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $access_module = [];
        $this->generateAccessModule($access_module, $request);

        $data['user_access_name'] = $request->user_access_name;
        $data['user_access_desc'] = $request->user_access_desc;
        $data['access_module'] = json_encode($access_module);

        try {
            DB::beginTransaction();
            $this->destitution_kk_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan P3KE Kepala Keluarga Pengguna', 201);
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

        $datas = $this->destitution_kk_repo->getRecord($id)->toArray();
        $this->getAccessModule($datas);

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah P3KE Kepala Keluarga',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit P3KE Kepala Keluarga',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Kepala Keluarga','Ubah P3KE Kepala Keluarga'],
            '_be_card_title' => 'Ubah P3KE Kepala Keluarga',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.destitution_kk.update',$id),
            '_be_home' => route('master.destitution_kk.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_destitution_kk.form',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'user_access_name' => 'required|max:100|unique:mt_user_access,user_access_name,'.$id,
            'user_access_desc' => 'required|max:300'
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $access_module = [];
        $this->generateAccessModule($access_module, $request);

        $data['user_access_name'] = $request->user_access_name;
        $data['user_access_desc'] = $request->user_access_desc;
        $data['access_module'] = json_encode($access_module);

        try {
            DB::beginTransaction();
            $this->destitution_kk_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah P3KE Kepala Keluarga Pengguna');
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
                $this->destitution_kk_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus P3KE Kepala Keluarga Pengguna');
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

    public function detail($id)
    {
        $record = $this->destitution_kk_repo->getRecord($id);
        if ($record == null) {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $data = [
            'datas' => $record->toArray(),
            '_be_page_title' => 'Detail P3KE Kepala Keluarga',
            '_be_page_title_desc' => 'Halaman ini adalah Detail P3KE Kepala Keluarga',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Kepala Keluarga','Detail P3KE Kepala Keluarga'],
            '_be_card_title' => 'Detail P3KE Kepala Keluarga',
            '_be_home' => route('master.destitution_kk.list'),
            '_parent_id' => $id,
        ];

        return view('backpage.master_destitution_kk.detail',compact('data'));
    }

    public function bnba_get_data(Request $request, $id)
    {
        $type = 'KEPALA-KELUARGA';
        if ($request->ajax()) {
            $data = Tr_program_realization_bnba::leftJoin('mt_user as updated_by','tr_program_realization_bnba.updated_by_id','updated_by.id')
                ->leftJoin('tr_program_realization', 'tr_program_realization_bnba.program_realization_id', 'tr_program_realization.id')
                ->leftJoin('mt_destitution_nik', 'tr_program_realization_bnba.nik', 'mt_destitution_nik.nik')
                ->leftJoin('tr_program', 'tr_program_realization.program_id', 'tr_program.id')
                ->leftJoin('sy_option as bnba_type', 'tr_program_realization_bnba.bnba_type_id', 'bnba_type.id')
                ->leftJoin('mt_organization', 'tr_program.organization_id', 'mt_organization.id')
                ->leftJoin('mt_budget_year', 'tr_program.budget_year_id', 'mt_budget_year.id')
                ->whereNull('tr_program_realization_bnba.deleted_at')
                ->where('mt_destitution_nik.id', $id)
                ->where('bnba_type.value', $type)
                ->select(
                    'tr_program_realization_bnba.*',
                    'tr_program.code as program_code',
                    'tr_program.program as program_program',
                    'tr_program.activity as program_activity',
                    'tr_program.sub_activity as program_sub_activity',
                    'mt_budget_year.name as budget_year_name',
                    'mt_organization.name as organization_name',
                    'updated_by.name as updated_by_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                // ->editColumn('active_flag', function($data) {
                //     return '<span class="badge light badge-'.($data->active_flag ? 'success':'danger').'">'.($data->active_flag ? 'Aktif':'Nonaktif').'</span>';
                // })
                ->addIndexColumn()
                ->rawColumns([])
                ->make(true);
        }
    }
}
