<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mt_organization;
use App\Repositories\CompileRepository;
use App\Repositories\Master\OrganizationRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OrganizationController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $organization_repo;

    public function __construct(
        CompileRepository $compile_repo,
        OrganizationRepository $organization_repo
    )
    {
        $this->route_prefix = 'master.organization.';
        $this->compile_repo = $compile_repo;
        $this->organization_repo = $organization_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Organisasi Perangkat Daerah',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Organisasi Perangkat Daerah',
            '_be_breadcrumbs' => ['Master Data','Master Organisasi Perangkat Daerah','Daftar Organisasi Perangkat Daerah'],
            '_be_insert' => route('master.organization.insert')
        ];
        return view('backpage.master_organization.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Mt_organization::leftJoin('mt_user as updated_by','mt_organization.updated_by_id','updated_by.id')
                ->leftJoin('mt_organization as parent','mt_organization.parent_id','parent.id')
                ->whereNull('mt_organization.deleted_at')
                ->select(
                    'mt_organization.*',
                    'parent.name as parent_name',
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
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.organization.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',$data->id).'" class="dropdown-item">Ubah</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('master.organization.delete').'" class="delete dropdown-item">Hapus</a>
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

        $fields['parent_id'] = [
            'label' => 'Parent',
            'name' => 'parent_id',
            'placeholder' => 'Parent',
            'type' => 'data',
            'data_table' => 'mt_organization',
            'data_condition' => ' and mt_organization.parent_id = "0"',
            'data_extra' => '',
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Parent wajib diisi'
        ];

        $fields['code'] = [
            'label' => 'Kode',
            'name' => 'code',
            'placeholder' => 'Kode',
            'type' => 'text',
            'maxlength' => 300,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Kode wajib diisi'
        ];

        $fields['name'] = [
            'label' => 'Organisasi Perangkat Daerah',
            'name' => 'name',
            'placeholder' => 'Organisasi Perangkat Daerah',
            'type' => 'text',
            'maxlength' => 300,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Organisasi Perangkat Daerah wajib diisi'
        ];

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Organisasi Perangkat Daerah',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Organisasi Perangkat Daerah baru',
            '_be_breadcrumbs' => ['Master Data','Master Organisasi Perangkat Daerah','Tambah Organisasi Perangkat Daerah'],
            '_be_card_title' => 'Tambah Organisasi Perangkat Daerah',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.organization.store'),
            '_be_home' => route('master.organization.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_organization.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'code' => 'required|max:100|unique:mt_organization,code',
            'name' => 'required|max:300'
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['code'] = $request->code;
        $data['name'] = $request->name;
        $data['parent_id'] = $request->parent_id ?? 0;

        try {
            DB::beginTransaction();
            $this->organization_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Organisasi Perangkat Daerah', 201);
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

        $datas = $this->organization_repo->getRecord($id)->toArray();

        $forms = $this->get_form();
        if ($datas['parent_id'] == 0) {
            unset($forms['parent_id']);
        }

        $data = [
            'fields' => $forms,
            'datas' => $datas,
            '_be_page_title' => 'Ubah Organisasi Perangkat Daerah',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Organisasi Perangkat Daerah',
            '_be_breadcrumbs' => ['Master Data','Master Organisasi Perangkat Daerah','Ubah Organisasi Perangkat Daerah'],
            '_be_card_title' => 'Ubah Organisasi Perangkat Daerah',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.organization.update',$id),
            '_be_home' => route('master.organization.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_organization.form',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'code' => 'required|max:100|unique:mt_organization,code,'.$id,
            'name' => 'required|max:300'
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $record = $this->organization_repo->getRecord($id);

        $data['code'] = $request->code;
        $data['name'] = $request->name;

        if ($record->parent_id != 0 && $request->parent_id != null) {
            $data['parent_id'] = $request->parent_id ?? 0;
        }

        try {
            DB::beginTransaction();
            $this->organization_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Organisasi Perangkat Daerah');
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
                $this->organization_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Organisasi Perangkat Daerah');
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
}
