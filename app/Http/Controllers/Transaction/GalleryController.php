<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Tr_gallery;
use App\Repositories\CompileRepository;
use App\Repositories\System\AttachmentRepository;
use App\Repositories\Transaction\GalleryRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class GalleryController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $gallery_repo;
    protected $attachment_repo;

    public function __construct(
        CompileRepository $compile_repo,
        GalleryRepository $gallery_repo,
        AttachmentRepository $attachment_repo
    )
    {
        $this->route_prefix = 'gallery.';
        $this->compile_repo = $compile_repo;
        $this->gallery_repo = $gallery_repo;
        $this->attachment_repo = $attachment_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Galeri',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Galeri',
            '_be_breadcrumbs' => ['Galeri','Daftar Galeri'],
            '_be_insert' => route('gallery.insert')
        ];
        return view('backpage.gallery.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Tr_gallery::whereNull('tr_gallery.deleted_at')
                ->select(
                    'tr_gallery.*',
                    'updated_by.name as updated_by_name'
                )
                ->leftJoin('mt_user as updated_by','tr_gallery.updated_by_id','updated_by.id');
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
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('gallery.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',$data->id).'" class="dropdown-item">Ubah</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('gallery.delete').'" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['action','active_flag'])
                ->make(true);
        }
    }

    public function get_form()
    {
        $fields = [];

        $fields['title'] = [
            'label' => 'Judul',
            'name' => 'title',
            'placeholder' => 'Judul',
            'type' => 'text',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Judul wajib diisi'
        ];

        $fields['category'] = [
            'label' => 'Kategori',
            'name' => 'category',
            'placeholder' => 'Kategori',
            'type' => 'select',
            'options' => ['KEGIATAN','SOSIALISASI'],
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Kategori wajib diisi'
        ];

        $fields['image_id'] = [
            'label' => 'Gambar',
            'name' => 'image_id',
            'placeholder' => 'Gambar',
            'type' => 'image',
            'image_width' => 800,
            'image_height' => 600,
            'reference_name' => 'tr_gallery',
            'reference_id' => 0,
            'maxlength' => 3,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Gambar wajib diisi'
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
            '_be_page_title' => 'Tambah Galeri',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Galeri baru',
            '_be_breadcrumbs' => ['Galeri','Tambah Galeri'],
            '_be_card_title' => 'Tambah Galeri',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('gallery.store'),
            '_be_home' => route('gallery.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.gallery.form',compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), $this->compile_repo->validateRule($this->get_form()));
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['title'] = $request->title;
        $data['category'] = $request->category;
        $data['image_id'] = $request->image_id;
        $data['active_flag'] = $request->active_flag ?? false;

        try {
            DB::beginTransaction();
            $id = $this->gallery_repo->insertRecord($data);
            $this->attachment_repo->updateRecord($data['image_id'], ['reference_id' => $id]);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Program', 201);
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
        $record = $this->gallery_repo->getRecord($id);
        if ($record) {
            $datas = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $form = $this->get_form();

        $data = [
            'fields' => $form,
            'datas' => $datas,
            '_be_page_title' => 'Ubah Galeri',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Galeri',
            '_be_breadcrumbs' => ['Galeri','Ubah Galeri'],
            '_be_card_title' => 'Ubah Galeri',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('gallery.update',$id),
            '_be_home' => route('gallery.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.gallery.form',compact('data'));
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

        $data['title'] = $request->title;
        $data['category'] = $request->category;
        $data['image_id'] = $request->image_id;
        $data['active_flag'] = $request->active_flag ?? false;

        try {
            DB::beginTransaction();
            $this->gallery_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Galeri');
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
        $record = $this->gallery_repo->getRecord($id);
        if($id != 0){
            try {
                DB::beginTransaction();
                $record = $this->gallery_repo->getRecord($id);
                $this->attachment_repo->deleteRecord($record->image_id);

                $this->gallery_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Galeri');
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                logbook($e->getMessage(), $e->getCode());
                $result = [
                    'status' => 'FAIL',
                    'message' => $e->getMessage()
                ];
            }
        } else {
            $result = [
                'status' => 'FAIL',
                'message' => 'Hanya dapat menghapus status DRAFT'
            ];
        }
        return response()->json($result);
    }
}
