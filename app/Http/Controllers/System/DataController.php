<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Sy_data;
use App\Repositories\CompileRepository;
use App\Repositories\System\DataRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DataController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $data_repo;

    public function __construct(
        CompileRepository $compile_repo,
        DataRepository $data_repo
    )
    {
        $this->route_prefix = 'system.data.';
        $this->compile_repo = $compile_repo;
        $this->data_repo = $data_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Data Sumber',
            '_be_page_title_desc' => 'Halaman ini adalah daftar semua Data Sumber mengakses sistem',
            // '_be_insert' => route('system.data.insert')
        ];
        return view('backpage.data.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Sy_data::select(
                    'sy_data.*',
                    'updated_by.name as updated_by_name'
                )
                ->leftJoin('mt_user as updated_by','sy_data.updated_by_id','updated_by.id');
            return DataTables::eloquent($data)
                ->editColumn('active_flag', function($data) {
                    return '<span class="badge light badge-'.($data->active_flag ? 'success':'danger').'">'.($data->active_flag ? 'Aktif':'Nonaktif').'</span>';
                })
                ->editColumn('updated_at', function($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',$data->id).'" class="dropdown-item">Ubah</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('system.data.delete').'" class="delete dropdown-item">Hapus</a>
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
            'label' => 'Sumber Data',
            'name' => 'name',
            'placeholder' => 'Sumber Data',
            'type' => 'text',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Sumber Data wajib diisi'
        ];

        $fields['description'] = [
            'label' => 'Deskripsi Sumber Data',
            'name' => 'description',
            'placeholder' => 'Deskripsi Sumber Data',
            'type' => 'text',
            'maxlength' => 300,
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Deskripsi Sumber Data wajib diisi'
        ];

        $fields['active_flag'] = [
            'label' => 'Status',
            'name' => 'active_flag',
            'placeholder' => 'Status',
            'type' => 'checkbox',
            'required' => false,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
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
            '_be_page_title' => 'Tambah Data Sumber',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Data Sumber baru',
            '_be_breadcrumbs' => ['Master Data','Master Data Sumber','Tambah Data Sumber'],
            '_be_card_title' => 'Tambah Data Sumber',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('system.data.store'),
            '_be_home' => route('system.data.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.data.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|max:100|unique:sy_data,name',
            'description' => 'required|max:100',
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        DB::beginTransaction();
        try {
            $data['name'] = $request->name;
            $data['description'] = $request->description;
            $data['active_flag'] = $request->active_flag ?? 0;
            if ($data['active_flag']) {
                $this->data_repo->updateStatusAll(!$data['active_flag']);
            }
            $this->data_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Data Sumber', 201);
            DB::commit();
        } catch (Exception $err) {
            DB::rollBack();
            logbook($err->getMessage(), $err->getCode());
            $result = [
                'status' => 'FAIL',
                'message' => $err->getMessage()
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
        $record = $this->data_repo->getRecord($id);
        if ($record) {
            $datas = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah Data',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Data',
            '_be_breadcrumbs' => ['Master Data','Master Data','Ubah Data'],
            '_be_card_title' => 'Ubah Data',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('system.data.update',$id),
            '_be_home' => route('system.data.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.data.form',compact('data'));
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

        $data['name'] = $request->name;
        $data['description'] = $request->description;
        $data['active_flag'] = $request->active_flag ?? 0;

        try {
            DB::beginTransaction();
            if ($data['active_flag']) {
                $this->data_repo->updateStatusAll(!$data['active_flag']);
            }
            $this->data_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Data');
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
            DB::beginTransaction();
            try {
                $record = $this->data_repo->getRecord($id);
                $path = $record->path;

                if (Storage::exists($path)) {
                    Storage::delete($path);
                }

                $this->data_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Data Sumber', 200);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $result = [
                    'status' => 'FAIL',
                    'message' => $e->getMessage()
                ];
            }
        }
        return response()->json($result);
    }
}
