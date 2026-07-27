<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\CompileRepository;
use App\Repositories\Master\UserRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    protected $route_prefix;
    protected $user_repo;
    protected $compile_repo;

    public function __construct(
        UserRepository $user_repo,
        CompileRepository $compile_repo
    ) {
        $this->route_prefix = 'master.user.';
        $this->user_repo = $user_repo;
        $this->compile_repo = $compile_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Pengguna',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar pengguna',
            '_be_breadcrumbs' => ['Master Data', 'Master Pengguna', 'Daftar Pengguna'],
            '_be_insert' => route('master.user.insert')
        ];
        return view('backpage.master_user.list', compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = User::leftJoin('mt_user_access', 'mt_user.user_access_id', 'mt_user_access.id')
                ->leftJoin('mt_user as updated_by', 'mt_user.updated_by_id', 'updated_by.id')
                ->leftJoin('mt_organization', 'mt_user.organization_id', 'mt_organization.id')
                ->select(
                    'mt_user.*',
                    'updated_by.name as updated_by_name',
                    DB::raw("IFNULL(mt_organization.name, '-') as organization_name"),
                    DB::raw('IFNULL(mt_user_access.user_access_name, "-") as user_access_name'),
                );
            return DataTables::eloquent($data)
                ->editColumn('name', function ($data) {
                    return $data->name_with_title;
                })
                ->editColumn('updated_at', function ($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->editColumn('active_flag', function ($data) {
                    return '<span class="badge light badge-' . ($data->active_flag ? 'success' : 'danger') . '">' . ($data->active_flag ? 'Aktif' : 'Nonaktif') . '</span>';
                })
                ->editColumn('user_access_name', function ($data) {
                    return '<span class="badge light badge-success">' . $data->user_access_name . '</span>';
                })
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    // $btn = '<div class="btn-group">
                    //     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a data-id="reset-'.$data->id.'" data-url="'.route('master.user.reset').'" class="reset btn btn-sm btn-warning"><i class="fa fa-refresh"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.user.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="' . route($this->route_prefix . 'edit', $data->id) . '" class="dropdown-item">Ubah</a>
                            <a data-id="reset-' . $data->id . '" data-url="' . route('master.user.reset') . '" class="reset dropdown-item">Reset Sandi</a>
                            <a data-id="delete-' . $data->id . '" data-url="' . route('master.user.delete') . '" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['active_flag', 'user_access_name', 'action'])
                ->make(true);
        }
    }

    public function get_form()
    {
        $fields = [];

        $fields['name'] = [
            'label' => 'Nama Pengguna',
            'name' => 'name',
            'placeholder' => 'Nama Pengguna',
            'type' => 'text',
            'maxlength' => 255,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Nama Pengguna wajib diisi'
        ];

        $fields['email'] = [
            'label' => 'Email',
            'name' => 'email',
            'placeholder' => 'Email',
            'type' => 'email',
            'maxlength' => 300,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Email wajib diisi'
        ];

        $fields['user_access_id'] = [
            'label' => 'Hak Akses Pengguna',
            'name' => 'user_access_id',
            'placeholder' => 'Hak Akses Pengguna',
            'type' => 'data',
            'data_table' => 'mt_user_access',
            'data_condition' => '',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Hak Akses Pengguna wajib diisi'
        ];

        $fields['prefix_title'] = [
            'label' => 'Gelar Depan',
            'name' => 'prefix_title',
            'placeholder' => 'Gelar Depan',
            'type' => 'text',
            'maxlength' => 100,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Gelar Depan wajib diisi'
        ];

        $fields['suffix_title'] = [
            'label' => 'Gelar Belakang',
            'name' => 'suffix_title',
            'placeholder' => 'Gelar Belakang',
            'type' => 'text',
            'maxlength' => 100,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Gelar Belakang wajib diisi'
        ];

        $fields['gender'] = [
            'label' => 'Jenis Kelamin',
            'name' => 'gender',
            'placeholder' => 'Jenis Kelamin',
            'type' => 'select',
            'options' => ['L', 'P'],
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Jenis Kelamin wajib diisi'
        ];

        $fields['organization_id'] = [
            'label' => 'Perangkat Daerah',
            'name' => 'organization_id',
            'placeholder' => 'Perangkat Daerah',
            'type' => 'data',
            'data_table' => 'mt_organization',
            'data_condition' => '',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Perangkat Daerah wajib diisi'
        ];

        // $fields['is_employee'] = [
        //     'label' => 'Karyawan',
        //     'name' => 'is_employee',
        //     'placeholder' => 'Karyawan',
        //     'type' => 'checkbox',
        //     'required' => true,
        //     'show_only' => false,
        //     'validate_message' => 'Karyawan wajib diisi'
        // ];

        // $fields['image_id'] = [
        //     'label' => 'Meta Image',
        //     'name' => 'image_id',
        //     'placeholder' => 'Meta Image',
        //     'type' => 'image',
        //     'image_width' => 300,
        //     'image_height' => 300,
        //     'required' => false,
        //     'show_only' => false,
        //     'validate_message' => 'Meta Image wajib diisi'
        // ];

        // $fields['reference_table'] = [
        //     'label' => 'Referensi Pengguna',
        //     'name' => 'reference_table',
        //     'placeholder' => 'Referensi Pengguna',
        //     'type' => 'data',
        //     'data_table' => 'sy_reference_user',
        //     'data_condition' => '',
        //     'data_extra' => '',
        //     'required' => true,
        //     'show_only' => false,
        //     'validate_message' => 'Referensi Pengguna wajib diisi'
        // ];

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Pengguna',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan pengguna baru',
            '_be_breadcrumbs' => ['Master Data', 'Master Pengguna', 'Tambah Pengguna'],
            '_be_card_title' => 'Tambah Pengguna',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.user.store'),
            '_be_home' => route('master.user.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_user.form', compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:mt_user,email',
            'user_access_id' => 'required',
            'organization_id' => 'required'
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['gender'] = $request->gender;
        $data['prefix_title'] = $request->prefix_title ?? '';
        $data['suffix_title'] = $request->suffix_title ?? '';
        $data['user_access_id'] = $request->user_access_id;
        $data['organization_id'] = $request->organization_id;
        // $data['is_employee'] = (bool) $request->is_employee;
        $data['password'] = bcrypt('12345678');
        $data['budget_year_id'] = get_preference('default_budget_year', 0);
        $data['active_flag'] = true;

        try {
            DB::beginTransaction();
            $id = $this->user_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Pengguna', 201);
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
        if ($id == 0) {
            return redirect(route($this->route_prefix . 'list'))->with('error', 'Tidak dapat menemukan ID');
        }

        $datas = $this->user_repo->getRecord($id)->toArray();
        $form = $this->get_form();

        $form['image_id'] = [
            'label' => 'Avatar / Foto Profil',
            'name' => 'image_id',
            'placeholder' => 'Avatar / Foto Profil',
            'type' => 'image',
            'image_width' => 100,
            'image_height' => 100,
            'reference_name' => 'mt_user',
            'reference_id' => $id,
            'maxlength' => 3,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Avatar / Foto Profil wajib diisi'
        ];

        $form['active_flag'] = [
            'label' => 'Status',
            'name' => 'active_flag',
            'placeholder' => 'Status',
            'type' => 'checkbox',
            'maxlength' => 3,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Status wajib diisi'
        ];

        // unset($form['email']);

        $data = [
            'fields' => $form,
            'datas' => $datas,
            '_be_page_title' => 'Ubah Pengguna',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah pengguna baru',
            '_be_breadcrumbs' => ['Master Data', 'Master Pengguna', 'Ubah Pengguna'],
            '_be_card_title' => 'Ubah Pengguna',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.user.update', $id),
            '_be_home' => route('master.user.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_user.form', compact('data'));
    }

    public function edit_password()
    {
        $fields = [];
        $datas = [];

        $fields['old_password'] = [
            'label' => 'Kata Sandi Lama',
            'name' => 'old_password',
            'placeholder' => 'Kata Sandi Lama',
            'type' => 'password',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Kata Sandi Lama wajib diisi'
        ];

        $fields['password'] = [
            'label' => 'Kata Sandi Baru',
            'name' => 'password',
            'placeholder' => 'Kata Sandi Baru',
            'type' => 'password',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Kata Sandi Baru wajib diisi'
        ];

        $fields['password_confirmation'] = [
            'label' => 'Konfirmasi Kata Sandi Baru',
            'name' => 'password_confirmation',
            'placeholder' => 'Konfirmasi Kata Sandi Baru',
            'type' => 'password',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Konfirmasi Kata Sandi Baru Lama wajib diisi'
        ];

        $data = [
            'fields' => $fields,
            'datas' => $datas,
            '_be_page_title' => 'Ubah Sandi',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit sandi',
            '_be_breadcrumbs' => ['Pengaturan', 'Ubah Sandi'],
            '_be_card_title' => 'Ubah Sandi',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.user.update_password'),
            '_be_home' => route('home')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_user.form_update', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:mt_user,email,' . $id,
            'user_access_id' => 'required',
            'organization_id' => 'required'
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['user_access_id'] = $request->user_access_id;
        $data['organization_id'] = $request->organization_id;
        $data['gender'] = $request->gender;
        $data['prefix_title'] = $request->prefix_title ?? '';
        $data['suffix_title'] = $request->suffix_title ?? '';
        $data['image_id'] = $request->image_id ?? 0;
        $data['active_flag'] = (bool) $request->active_flag ?? 0;

        try {
            DB::beginTransaction();
            $this->user_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Pengguna');
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

    public function update_budget_year(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'budget_year_id' => 'required'
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['budget_year_id'] = $request->budget_year_id;

        try {
            DB::beginTransaction();
            $this->user_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Tahun Anggaran telah diubah'
            ];
            logbook('Berhasil mengubah Tahun Anggaran Pengguna');
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

    public function update_password(Request $request)
    {
        $user = Auth::user();
        $data['password'] = Hash::make($request->password);

        try {
            DB::beginTransaction();
            $old_pass = Hash::check($request->old_password, $user->password);
            if (!$old_pass) {
                $result = [
                    'status' => 'FAIL',
                    'message' => 'Kata sandi lama salah',
                ];
                return response()->json($result);
            }
            $validated = Validator::make($request->all(), [
                'old_password' => 'required',
                'password' => 'required|confirmed|min:6',
            ]);
            if ($validated->fails()) {
                $result = [
                    'status' => 'FAIL',
                    'message' => $validated->getMessageBag()->first()
                ];
                return response()->json($result);
            }
            $this->user_repo->updateRecord($user->id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Kata Sandi Pengguna');
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
        if ($id != 0) {
            try {
                DB::beginTransaction();
                $this->user_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Pengguna');
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

    public function reset(Request $request)
    {
        $id = $request->id ?? 0;
        if ($id != 0) {
            try {
                DB::beginTransaction();
                $data = ['password' => bcrypt('12345678')];
                $this->user_repo->updateRecord($id, $data);
                $result = [
                    'status' => 'OK',
                    'message' => 'Sandi di kembalikan ke (12345678)'
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
