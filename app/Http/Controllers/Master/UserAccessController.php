<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mt_user_access;
use App\Repositories\CompileRepository;
use App\Repositories\Master\UserAccessRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class UserAccessController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $user_access_repo;

    public function __construct(
        CompileRepository $compile_repo,
        UserAccessRepository $user_access_repo
    )
    {
        $this->route_prefix = 'master.user_access.';
        $this->compile_repo = $compile_repo;
        $this->user_access_repo = $user_access_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Hak Akses',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar hak akses',
            '_be_breadcrumbs' => ['Master Data','Master Hak Akses','Daftar Hak Akses'],
            '_be_insert' => route('master.user_access.insert')
        ];
        return view('backpage.master_user_access.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Mt_user_access::leftJoin('mt_user as updated_by','mt_user_access.updated_by_id','updated_by.id')
                ->select(
                    'mt_user_access.*',
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
                    //     <a href="'.route($this->route_prefix.'edit_modules',$data->id).'" class="btn btn-sm btn-warning"><i class="fa fa-cogs"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.user_access.delete').'" class="delete btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></a></div>
                    // ';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',$data->id).'" class="dropdown-item">Ubah</a>
                            <a href="'.route($this->route_prefix.'edit_modules',$data->id).'" class="dropdown-item">Pilihan Modul</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('master.user_access.delete').'" class="delete dropdown-item">Hapus</a>
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

        $fields['user_access_name'] = [
            'label' => 'Nama Hak Pengguna',
            'name' => 'user_access_name',
            'placeholder' => 'Nama Hak Pengguna',
            'type' => 'text',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Nama Hak Pengguna wajib diisi'
        ];

        $fields['user_access_desc'] = [
            'label' => 'Deskripsi Hak Pengguna',
            'name' => 'user_access_desc',
            'placeholder' => 'Deskripsi Hak Pengguna',
            'type' => 'text-area',
            'maxlength' => 300,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Deskripsi Hak Pengguna wajib diisi'
        ];

        $fields['bnba_flag'] = [
            'label' => 'Ijinkan Akses BNBA',
            'name' => 'bnba_flag',
            'placeholder' => 'Ijinkan Akses BNBA',
            'type' => 'checkbox',
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Ijinkan Akses BNBA wajib diisi'
        ];

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Hak Akses',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan hak akses baru',
            '_be_breadcrumbs' => ['Master Data','Master Hak Akses','Tambah Hak Akses'],
            '_be_card_title' => 'Tambah Hak Akses',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.user_access.store'),
            '_be_home' => route('master.user_access.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_user_access.form',compact('data'));
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

        $data['user_access_name'] = $request->user_access_name;
        $data['user_access_desc'] = $request->user_access_desc;
        $data['bnba_flag'] = (bool) $request->bnba_flag;
        $data['access_module'] = json_encode($access_module);

        try {
            DB::beginTransaction();
            $this->user_access_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Hak Akses Pengguna', 201);
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

        $datas = [];
        $record = $this->user_access_repo->getRecord($id);
        if ($record) {
            $datas = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah Hak Akses',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit hak akses',
            '_be_breadcrumbs' => ['Master Data','Master Hak Akses','Ubah Hak Akses'],
            '_be_card_title' => 'Ubah Hak Akses',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.user_access.update',$id),
            '_be_home' => route('master.user_access.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_user_access.form',compact('data'));
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
        $data['bnba_flag'] = (bool) $request->bnba_flag;
        // $data['access_module'] = json_encode($access_module);

        try {
            DB::beginTransaction();
            $this->user_access_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Hak Akses Pengguna');
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
                $this->user_access_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Hak Akses Pengguna');
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

    public function generateAccessModule(&$access_modules, $request)
    {
        $forms = $this->get_form();
        foreach ($forms as $key => $value) {
            if($value['type'] == 'checkbox'){
                $access_value = (bool) $request->$key ?? false;
                $access_module = [
                    'module' => $key,
                    'active_flag' => $access_value
                ];
                array_push($access_modules, $access_module);
            }
        }
    }

    public function getAccessModule(&$datas)
    {
        $access_modules = json_decode($datas['access_module'], true);
        $forms = $this->get_form();
        foreach ($forms as $key => $value) {
            if($value['type'] == 'checkbox'){
                // $datas[$key] = 0;
                foreach ($access_modules as $access_module) {
                    if($access_module['module'] == $key){
                        $datas[$key] = $access_module['active_flag'] ? 1 : 0;
                    }
                }
            }
        }
        unset($datas['access_module']);
    }

    public function edit_modules($id)
    {
        $id = (int) $id;
        if($id == 0){
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $datas = [];
        $record = $this->user_access_repo->getRecord($id);
        if ($record) {
            $datas = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $access_module = json_decode($record['access_module'], true);

        $modules = [
            ['name' => 'Master Pengguna', 'value' => 'mt_user'],
            ['name' => 'Master Hak Akses', 'value' => 'mt_user_access'],
            ['name' => 'Master Perangkat Daerah', 'value' => 'mt_organization'],
            ['name' => 'Master Templat Grafik', 'value' => 'mt_dashboard'],
            ['name' => 'Master Tahun Anggaran', 'value' => 'mt_budget_year'],
            ['name' => 'Master P3KE Individu', 'value' => 'mt_destitution_nik'],
            ['name' => 'Master P3KE Kepala Keluarga', 'value' => 'mt_destitution_kk'],
            ['name' => 'Master Sumber Pembiayaan', 'value' => 'mt_budget_source'],
            ['name' => 'Master Templat Program', 'value' => 'mt_program_template'],
            ['name' => 'Galeri', 'value' => 'tr_gallery'],
            ['name' => 'Kegiatan', 'value' => 'tr_program'],
            ['name' => 'Realisasi', 'value' => 'tr_program_realization'],
            ['name' => 'Unduh Laporan', 'value' => 'tr_download'],
            ['name' => 'Grafik', 'value' => 'tr_dashboard'],
            ['name' => 'Tool - Cek Program', 'value' => 'to_program'],
            ['name' => 'Sistem - Preferensi', 'value' => 'sy_preference'],
            ['name' => 'Sistem - Preferensi', 'value' => 'sy_preference'],
            ['name' => 'Sistem - Berkas', 'value' => 'sy_file'],
            ['name' => 'Sistem - Data', 'value' => 'sy_data'],
            ['name' => 'Sistem - Log Impor', 'value' => 'sy_import'],
            ['name' => 'Sistem - Pilihan', 'value' => 'sy_option'],
            ['name' => 'Sistem - Log Aktivitas', 'value' => 'sy_log_activity']
        ];

        $accesses = [];
        foreach ($modules as $module) {
            foreach ($access_module as $value) {
                if ($module['value'] == $value['module']) {
                    $accesses[$value['module']]['active_flag'] = $value['active_flag'];
                    $accesses[$value['module']]['read_all_flag'] = $value['read_all_flag'];
                }
            }
        }
        // dd($accesses);

        $data = [
            'modules' => $modules,
            'datas' => $accesses,
            '_be_page_title' => 'Detail Hak Akses',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit hak akses',
            '_be_breadcrumbs' => ['Master Data','Master Hak Akses','Detail Hak Akses'],
            '_be_card_title' => 'Detail Hak Akses',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.user_access.update_modules',$id),
            '_be_home' => route('master.user_access.list')
        ];
        return view('backpage.master_user_access.edit_module',compact('data'));
    }

    public function update_modules(Request $request, $id)
    {
        $datas = [];

        $id = (int) $id;
        if($id == 0){
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $record = $this->user_access_repo->getRecord($id);
        if ($record) {
            $record = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        // dd($request->all());
        foreach ($request->name as $module) {
            $data = [
                'module' => $module,
                'active_flag' => false,
                'read_all_flag' => false,
            ];
            if (isset($request->active_flag)) {
                foreach ($request->active_flag as $key => $value) {
                    if ($key == $module) {
                        $data['active_flag'] = (bool) $value;
                    }
                }
            }
            if (isset($request->read_all_flag)) {
                foreach ($request->read_all_flag as $key => $value) {
                    if ($key == $module) {
                        $data['read_all_flag'] = (bool) $value;
                    }
                }
            }
            array_push($datas, $data);
        }

        DB::beginTransaction();
        try {
            $this->user_access_repo->updateRecord($id, ['access_module' => json_encode($datas)]);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Hak Akses Pengguna');
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
