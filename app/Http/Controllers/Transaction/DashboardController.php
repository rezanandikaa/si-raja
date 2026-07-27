<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Tr_dashboard;
use App\Repositories\CompileRepository;
use App\Repositories\Transaction\DashboardRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DashboardController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $dashboard_repo;

    public function __construct(
        CompileRepository $compile_repo,
        DashboardRepository $dashboard_repo
    )
    {
        $this->route_prefix = 'dashboard.';
        $this->compile_repo = $compile_repo;
        $this->dashboard_repo = $dashboard_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Data Grafik',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Data Grafik',
            '_be_breadcrumbs' => ['Data Grafik','Daftar Data Grafik'],
            '_be_insert' => route('dashboard.insert')
        ];
        return view('backpage.dashboard.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Tr_dashboard::leftJoin('mt_user as updated_by','tr_dashboard.updated_by_id','updated_by.id')
                ->leftJoin('mt_dashboard','tr_dashboard.dashboard_id','mt_dashboard.id')
                ->whereNull('tr_dashboard.deleted_at')
                ->where('tr_dashboard.user_id', Auth::user()->id)
                ->select(
                    'tr_dashboard.*',
                    'mt_dashboard.title as dashboard_title',
                    'mt_dashboard.type as dashboard_type',
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
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('dashboard.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',$data->id).'" class="dropdown-item">Ubah</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('dashboard.delete').'" class="delete dropdown-item">Hapus</a>
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

        $fields['dashboard_id'] = [
            'label' => 'Info Grafik',
            'name' => 'dashboard_id',
            'placeholder' => 'Info Grafik',
            'type' => 'data',
            'data_table' => 'mt_dashboard',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Info Grafik wajib diisi'
        ];

        $fields['order'] = [
            'label' => 'Order',
            'name' => 'order',
            'placeholder' => 'Order',
            'type' => 'number',
            'maxlength' => 3,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Order wajib diisi'
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

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Data Grafik',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Data Grafik baru',
            '_be_breadcrumbs' => ['Data Grafik','Tambah Data Grafik'],
            '_be_card_title' => 'Tambah Data Grafik',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('dashboard.store'),
            '_be_home' => route('dashboard.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.dashboard.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'dashboard_id' => 'required',
            'order' => 'required'
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['user_id'] = Auth::user()->id;
        $data['dashboard_id'] = $request->dashboard_id;
        $data['order'] = $request->order;
        $data['active_flag'] = $request->active_flag ?? 0;

        try {
            DB::beginTransaction();
            $this->dashboard_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Data Grafik Pengguna', 201);
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

        $datas = $this->dashboard_repo->getRecord($id)->toArray();

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah Data Grafik',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Data Grafik',
            '_be_breadcrumbs' => ['Data Grafik','Ubah Data Grafik'],
            '_be_card_title' => 'Ubah Data Grafik',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('dashboard.update',$id),
            '_be_home' => route('dashboard.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.dashboard.form',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'dashboard_id' => 'required',
            'order' => 'required'
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['dashboard_id'] = $request->dashboard_id;
        $data['order'] = $request->order;
        $data['active_flag'] = $request->active_flag ?? 0;

        try {
            DB::beginTransaction();
            $this->dashboard_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Data Grafik Pengguna');
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
                $this->dashboard_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Data Grafik Pengguna');
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
