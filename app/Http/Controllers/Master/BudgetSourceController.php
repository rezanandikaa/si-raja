<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mt_budget_source;
use App\Repositories\CompileRepository;
use App\Repositories\Master\BudgetSourceRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class BudgetSourceController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $budget_source_repo;

    public function __construct(
        CompileRepository $compile_repo,
        BudgetSourceRepository $budget_source_repo
    )
    {
        $this->route_prefix = 'master.budget_source.';
        $this->compile_repo = $compile_repo;
        $this->budget_source_repo = $budget_source_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Sumber Pembiayaan',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Sumber Pembiayaan',
            '_be_breadcrumbs' => ['Master Data','Master Sumber Pembiayaan','Daftar Sumber Pembiayaan'],
            '_be_insert' => route('master.budget_source.insert')
        ];
        return view('backpage.master_budget_source.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Mt_budget_source::leftJoin('mt_user as updated_by','mt_budget_source.updated_by_id','updated_by.id')
                ->whereNull('mt_budget_source.deleted_at')
                ->select(
                    'mt_budget_source.*',
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
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.budget_source.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',$data->id).'" class="dropdown-item">Ubah</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('master.budget_source.delete').'" class="delete dropdown-item">Hapus</a>
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

        $fields['name'] = [
            'label' => 'Sumber Pembiayaan',
            'name' => 'name',
            'placeholder' => 'Sumber Pembiayaan',
            'type' => 'text',
            'maxlength' => 300,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Sumber Pembiayaan wajib diisi'
        ];

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Sumber Pembiayaan',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Sumber Pembiayaan baru',
            '_be_breadcrumbs' => ['Master Data','Master Sumber Pembiayaan','Tambah Sumber Pembiayaan'],
            '_be_card_title' => 'Tambah Sumber Pembiayaan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.budget_source.store'),
            '_be_home' => route('master.budget_source.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_budget_source.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|max:300|unique:mt_budget_source,name',
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['name'] = $request->name;

        try {
            DB::beginTransaction();
            $this->budget_source_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Sumber Pembiayaan', 201);
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

        $datas = $this->budget_source_repo->getRecord($id)->toArray();

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah Sumber Pembiayaan',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Sumber Pembiayaan',
            '_be_breadcrumbs' => ['Master Data','Master Sumber Pembiayaan','Ubah Sumber Pembiayaan'],
            '_be_card_title' => 'Ubah Sumber Pembiayaan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.budget_source.update',$id),
            '_be_home' => route('master.budget_source.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_budget_source.form',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|max:300|unique:mt_budget_source,name,'.$id,
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['name'] = $request->name;

        try {
            DB::beginTransaction();
            $this->budget_source_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Sumber Pembiayaan');
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
                $this->budget_source_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Sumber Pembiayaan');
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
