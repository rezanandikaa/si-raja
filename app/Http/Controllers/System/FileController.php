<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Sy_file;
use App\Repositories\CompileRepository;
use App\Repositories\System\FileRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class FileController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $file_repo;

    public function __construct(
        CompileRepository $compile_repo,
        FileRepository $file_repo
    )
    {
        $this->route_prefix = 'system.file.';
        $this->compile_repo = $compile_repo;
        $this->file_repo = $file_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Berkas',
            '_be_page_title_desc' => 'Halaman ini adalah daftar semua Berkas mengakses sistem',
            // '_be_insert' => route('system.file.insert')
        ];
        return view('backpage.file.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Sy_file::select(
                    'sy_file.*',
                    'updated_by.name as updated_by_name'
                )
                ->leftJoin('mt_user as updated_by','sy_file.updated_by_id','updated_by.id');
            return DataTables::eloquent($data)
                ->editColumn('size', function($data) {
                    $num = number_format($data->size, 0, ',', '.') ;
                    return '<span class="badge badge-sm light badge-primary">'.$num.' KB</span>';
                })
                ->editColumn('updated_at', function($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->addIndexColumn()
                ->addColumn('action', function($data){
                    $btn = '';
                    if ($data->is_sync == true) {
                        $btn = '<a class="btn btn-sm btn-icon btn-pure btn-default on-default import" data-url="'.route('system.file.import', ['id' => $data->id]).'" data-toggle="tooltip" data-original-title="Impor"><i class="icon-arrow-up" aria-hidden="true"></i>
                        </a>';
                        $btn .= '&nbsp;';
                    }
                    $btn .= '<a class="btn btn-sm btn-icon btn-pure btn-default on-default button-remove delete" data-id="delete-'.$data->id.'" data-url="'.route('system.file.delete').'" data-toggle="tooltip" data-original-title="Hapus"><i class="icon-trash" aria-hidden="true"></i>
                    </a>';

                    return $btn;
                })
                ->rawColumns(['size','action'])
                ->make(true);
        }
    }

    public function get_form()
    {
        $fields = [];

        $fields['batch_code'] = [
            'label' => 'Kode Batch',
            'name' => 'batch_code',
            'placeholder' => 'Kode Batch',
            'type' => 'text',
            'maxlength' => 5,
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Kode Batch wajib diisi'
        ];

        $fields['type'] = [
            'label' => 'Jenis Berkas',
            'name' => 'type',
            'placeholder' => 'Jenis Berkas',
            'type' => 'select',
            'options' => ['INDIVIDU','KEPALA-KELUARGA'],
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Jenis Berkas wajib diisi'
        ];

        $fields['file'] = [
            'label' => 'Berkas Unggah',
            'name' => 'file',
            'placeholder' => 'Berkas Unggah',
            'type' => 'file',
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Berkas Unggah wajib diisi'
        ];

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Berkas',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Berkas baru',
            '_be_breadcrumbs' => ['Master Data','Master Berkas','Tambah Berkas'],
            '_be_card_title' => 'Tambah Berkas',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('system.file.store'),
            '_be_home' => route('system.file.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.file.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'batch_code' => 'required|max:5|unique:sy_file,batch_code',
            'file' => 'required',
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
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $file_name_original = $request->batch_code .'.'. $file->getClientOriginalExtension();
                $file->storeAs('public/', $file_name_original);
            } else {
                logbook($msg = 'Tidak ada berkas yang diunggah', 403);
                $result = [
                    'status' => 'FAIL',
                    'message' => $msg
                ];
                return response()->json($result);
            }
            $data['data_id'] = source_data_active();
            $data['batch_code'] = $request->batch_code;
            $data['file_name_original'] = $file_name_original;
            $data['extension'] = $file->getClientOriginalExtension();
            $data['type'] = $request->type;
            $data['size'] = $file->getSize() / 1000;
            $data['path'] = 'app/public/'. $file_name_original;

            $this->file_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Berkas', 201);
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

    public function destroy(Request $request)
    {
        $id = $request->id ?? 0;
        if($id != 0){
            DB::beginTransaction();
            try {
                $record = $this->file_repo->getRecord($id);
                $path = $record->path;

                if (Storage::exists($path)) {
                    Storage::delete($path);
                }

                $this->file_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
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

    public function import(Request $request, $id) {
        DB::beginTransaction();
        try {
            $this->file_repo->updateRecord($id, ['is_sync' => false]);
            $result = [
                'status' => 'OK',
                'message' => 'Berhasil menambahkan berkas ke antrian, tunggu prosesnya beberapa menit'
            ];
            DB::commit();
        } catch (\Exception $err) {
            DB::rollBack();
            Log::info($err->getMessage());
            $result = [
                'status' => 'FAIL',
                'message' => $err->getMessage()
            ];
        }
        return response()->json($result);
    }
}
