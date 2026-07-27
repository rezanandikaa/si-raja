<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Sy_data;
use App\Models\System\Sy_option;
use App\Repositories\CompileRepository;
use App\Repositories\System\OptionRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class OptionController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $option_repo;

    public function __construct(
        CompileRepository $compile_repo,
        OptionRepository $option_repo
    )
    {
        $this->route_prefix = 'system.option.';
        $this->compile_repo = $compile_repo;
        $this->option_repo = $option_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Pilihan',
            '_be_page_title_desc' => 'Halaman ini adalah daftar semua Pilihan pada sistem',
            // '_be_insert' => route('system.option.insert')
        ];
        return view('backpage.option.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Sy_option::select(
                    'sy_option.*',
                    'updated_by.name as updated_by_name'
                )
                ->leftJoin('mt_user as updated_by','sy_option.updated_by_id','updated_by.id');
            return DataTables::eloquent($data)
                ->editColumn('active_flag', function($data) {
                    return $data->active_flag;
                })
                ->editColumn('updated_at', function($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    $btn = '';
                    // $btn = '<a class="btn btn-sm btn-icon btn-pure btn-default on-default" data-url="'.route('system.option.edit', ['id' => $data->id]).'" data-toggle="tooltip" data-original-title="Edit"><i class="icon-pencil" aria-hidden="true"></i>
                    // </a>';
                    // $btn .= '&nbsp;';
                    // $btn .= '<a class="btn btn-sm btn-icon btn-pure btn-default on-default button-remove delete" data-id="delete-'.$data->id.'" data-url="'.route('system.option.delete').'" data-toggle="tooltip" data-original-title="Hapus"><i class="icon-trash" aria-hidden="true"></i>
                    // </a>';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',$data->id).'" class="dropdown-item">Ubah</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('system.option.delete').'" class="delete dropdown-item">Hapus</a>
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

        $fields['code'] = [
            'label' => 'Kode',
            'name' => 'code',
            'placeholder' => 'Kode',
            'type' => 'text',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Kode wajib diisi'
        ];

        $fields['label'] = [
            'label' => 'Label',
            'name' => 'label',
            'placeholder' => 'Label',
            'type' => 'text',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Label wajib diisi'
        ];

        $fields['value'] = [
            'label' => 'Nilai',
            'name' => 'value',
            'placeholder' => 'Nilai',
            'type' => 'text',
            'maxlength' => 300,
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Value wajib diisi'
        ];

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Pilihan',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Pilihan baru',
            '_be_breadcrumbs' => ['Master Data','Master Pilihan','Tambah Pilihan'],
            '_be_card_title' => 'Tambah Pilihan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('system.option.store'),
            '_be_home' => route('system.option.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.option.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'code' => 'required|max:100',
            'label' => 'required|max:100',
            'value' => 'required|max:300',
        ]);

        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['code'] = strtolower($request->code);
        $data['label'] = ucwords(strtolower($request->label));
        $data['value'] = $request->value;

        DB::beginTransaction();
        try {
            $this->option_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Pilihan', 201);
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

        $datas = $this->option_repo->getRecord($id);

        $datas = [];
        $record = $this->option_repo->getRecord($id);
        if ($record) {
            $datas = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }


        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah Pilihan',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Pilihan',
            '_be_breadcrumbs' => ['Master Data','Master Pilihan','Ubah Pilihan'],
            '_be_card_title' => 'Ubah Pilihan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('system.option.update',$id),
            '_be_home' => route('system.option.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.option.form',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'code' => 'required|max:100',
            'label' => 'required|max:100',
            'value' => 'required|max:300',
        ]);

        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['code'] = strtolower($request->code);
        $data['label'] = ucwords(strtolower($request->label));
        $data['value'] = $request->value;

        try {
            DB::beginTransaction();
            $this->option_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Pilihan Pengguna');
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
                $this->option_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Pilihan', 200);
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
