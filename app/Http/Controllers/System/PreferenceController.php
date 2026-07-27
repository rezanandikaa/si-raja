<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Sy_preference;
use App\Repositories\CompileRepository;
use App\Repositories\System\PreferenceRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PreferenceController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $preference_repo;

    public function __construct(
        CompileRepository $compile_repo,
        PreferenceRepository $preference_repo
    ) {
        $this->route_prefix = 'system.preference.';
        $this->compile_repo = $compile_repo;
        $this->preference_repo = $preference_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Preferensi',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Preferensi',
            '_be_breadcrumbs' => ['Preferensi', 'Daftar Preferensi'],
            '_be_insert' => route('system.preference.insert')
        ];
        return view('backpage.preference.list', compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Sy_preference::leftJoin('mt_user as updated_by', 'sy_preference.updated_by_id', 'updated_by.id')
                ->where('sy_preference.type', 'show')
                ->select(
                    'sy_preference.*',
                    'updated_by.name as updated_by_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function ($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    // $btn = '<div class="btn-group">
                    //     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('system.preference.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="' . route($this->route_prefix . 'edit', $data->id) . '" class="dropdown-item">Ubah</a>
                            <a data-id="delete-' . $data->id . '" data-url="' . route('system.preference.delete') . '" class="delete dropdown-item">Hapus</a>
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

        $fields['name'] = [
            'label' => 'Nama Preferensi',
            'name' => 'name',
            'placeholder' => 'Nama Preferensi',
            'type' => 'text',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Nama Preferensi wajib diisi'
        ];

        $fields['key'] = [
            'label' => 'Key',
            'name' => 'key',
            'placeholder' => 'Key',
            'type' => 'text',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            'uppercase' => false,
            'validate_message' => 'Key wajib diisi'
        ];

        $fields['value'] = [
            'label' => 'Value',
            'name' => 'value',
            'placeholder' => 'Value',
            'type' => 'text',
            'required' => true,
            'show_only' => false,
            'uppercase' => false,
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
            '_be_page_title' => 'Tambah Preferensi',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Preferensi baru',
            '_be_breadcrumbs' => ['Preferensi', 'Tambah Preferensi'],
            '_be_card_title' => 'Tambah Preferensi',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('system.preference.store'),
            '_be_home' => route('system.preference.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.preference.form', compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'key' => 'required|max:100|unique:sy_preference,key,NULL,id,deleted_at,NULL',
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['name'] = $request->name;
        $data['key'] = strtolower($request->key);
        $data['value'] = $request->value;

        try {
            DB::beginTransaction();
            $this->preference_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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
        if ($id == 0) {
            return redirect(route($this->route_prefix . 'list'))->with('error', 'Tidak dapat menemukan ID');
        }

        $datas = $this->preference_repo->getRecord($id)->toArray();

        $form = $this->get_form();

        if ($datas['key'] == 'general_organization') {
            $form['value']['type'] = 'data-multi';
            $form['value']['data_table'] = 'mt_organization';
            $form['value']['data_condition'] = '';
            $form['value']['required'] = true;
        }

        if ($datas['key'] == 'default_budget_year') {
            $form['value']['type'] = 'data';
            $form['value']['data_table'] = 'mt_budget_year';
            $form['value']['data_condition'] = '';
            $form['value']['required'] = true;
        }

        if ($datas['key'] == 'default_dashboard') {
            $form['value']['type'] = 'data-multi';
            $form['value']['data_table'] = 'mt_dashboard';
            $form['value']['data_condition'] = '';
            $form['value']['required'] = true;
        }

        // if($datas['key'] == 'site_logo') {
        //     $form['value']['type'] = 'image';
        //     $form['value']['image_width'] = 300;
        //     $form['value']['image_height'] = 300;
        //     $form['value']['required'] = false;
        // }

        // if($datas['key'] == 'footer_image') {
        //     $form['value']['type'] = 'image';
        //     $form['value']['image_width'] = 1920;
        //     $form['value']['image_height'] = 1010;
        //     $form['value']['required'] = false;
        // }

        // if($datas['key'] == 'article_header_image') {
        //     $form['value']['type'] = 'image';
        //     $form['value']['image_width'] = 1920;
        //     $form['value']['image_height'] = 1182;
        //     $form['value']['required'] = false;
        // }

        // if($datas['key'] == 'site_address') {
        //     $form['value']['type'] = 'ckeditor';
        //     $form['value']['required'] = false;
        // }

        $data = [
            'fields' => $form,
            'datas' => $datas,
            '_be_page_title' => 'Ubah Preferensi',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Preferensi',
            '_be_breadcrumbs' => ['Preferensi', 'Ubah Preferensi'],
            '_be_card_title' => 'Ubah Preferensi',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('system.preference.update', $id),
            '_be_home' => route('system.preference.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.preference.form', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|max:100',
            'key' => 'required|max:100|unique:sy_preference,key,' . $id . ',id,deleted_at,NULL',
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['name'] = $request->name;
        $data['key'] = strtolower($request->key);
        $data['value'] = $request->value;

        try {
            DB::beginTransaction();
            $this->preference_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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
        if ($id != 0) {
            try {
                DB::beginTransaction();
                $this->preference_repo->deleteRecord($id);
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
}
