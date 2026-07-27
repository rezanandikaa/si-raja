<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mt_program_template;
use App\Repositories\CompileRepository;
use App\Repositories\Master\ProgramTemplateRepository;
use App\Repositories\Transaction\ProgramRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class ProgramTemplateController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $program_template_repo;
    protected $program_repo;

    public function __construct(
        CompileRepository $compile_repo,
        ProgramTemplateRepository $program_template_repo,
        ProgramRepository $program_repo
    )
    {
        $this->route_prefix = 'master.program_template.';
        $this->compile_repo = $compile_repo;
        $this->program_template_repo = $program_template_repo;
        $this->program_repo = $program_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Templat Program',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Templat Program',
            '_be_breadcrumbs' => ['Master Data','Master Templat Program','Daftar Templat Program'],
            '_be_insert' => '#'
        ];
        return view('backpage.master_program_template.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        $user = Auth::user();
        if ($request->ajax()) {
            $data = Mt_program_template::leftJoin('mt_user as updated_by','mt_program_template.updated_by_id','updated_by.id')
                ->leftJoin('mt_organization','mt_program_template.organization_id','mt_organization.id')
                ->leftJoin('mt_budget_year','mt_program_template.budget_year_id','mt_budget_year.id')
                ->whereNull('mt_program_template.deleted_at')
                ->where('mt_program_template.budget_year_id', $user->budget_year_id)
                ->select(
                    'mt_program_template.*',
                    DB::raw("IFNULL(mt_organization.name, '-') as organization_name"),
                    DB::raw("IFNULL(mt_budget_year.name, '-') as budget_year_name"),
                    'updated_by.name as updated_by_name'
                )
                ->orderBy('mt_program_template.code', 'ASC');
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
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.program_template.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '<div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'edit',['id' => $data->id, 'type' => $data->type]).'" class="dropdown-item">Ubah</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('master.program_template.delete').'" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function get_form($type)
    {
        $fields = [];

        $fields['budget_year_id'] = [
            'label' => 'Tahun Anggaran',
            'name' => 'budget_year_id',
            'placeholder' => 'Tahun Anggaran',
            'type' => 'data',
            'data_table' => 'mt_budget_year',
            'data_condition' => '',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Tahun Anggaran wajib diisi'
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

        $fields['code'] = [
            'label' => 'Kode',
            'name' => 'code',
            'placeholder' => 'Kode',
            'type' => 'text',
            'maxlength' => 50,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Kode wajib diisi'
        ];

        $fields['concern'] = [
            'label' => 'Nomenklatur Urusan',
            'name' => 'concern',
            'placeholder' => 'Nomenklatur Urusan',
            'type' => 'text-area',
            'maxlength' => 1000,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Nomenklatur Urusan wajib diisi'
        ];

        if ($type == 'sub_activity') {
            $fields['performance'] = [
                'label' => 'Kinerja',
                'name' => 'performance',
                'placeholder' => 'Kinerja',
                'type' => 'text-area',
                'maxlength' => 1000,
                'required' => true,
                'show_only' => false,
                'validate_message' => 'Kinerja wajib diisi'
            ];

            $fields['indicator'] = [
                'label' => 'Indikator',
                'name' => 'indicator',
                'placeholder' => 'Indikator',
                'type' => 'text-area',
                'maxlength' => 1000,
                'required' => true,
                'show_only' => false,
                'validate_message' => 'Indikator wajib diisi'
            ];

            $fields['measure'] = [
                'label' => 'Satuan',
                'name' => 'measure',
                'placeholder' => 'Satuan',
                'type' => 'text',
                'maxlength' => 100,
                'required' => true,
                'show_only' => false,
                'validate_message' => 'Satuan wajib diisi'
            ];

            $fields['program_goal_id'] = [
                'label' => 'Strategi Program',
                'name' => 'program_goal_id',
                'placeholder' => 'Strategi Program',
                'type' => 'data',
                'data_table' => 'sy_option',
                'data_condition' => ' and code = "strategy_program"',
                'data_extra' => '',
                'required' => true,
                'show_only' => false,
                'validate_message' => 'Strategi Program wajib diisi'
            ];
        }

        return $fields;
    }

    public function insert($type)
    {
        $allows = [
            'program',
            'activity',
            'sub_activity'
        ];
        if (!in_array($type, $allows)) {
            return redirect(route('master.program_template.list'));
        }
        $data = [];

        $data = [
            'fields' => $this->get_form($type),
            'datas' => [],
            '_be_page_title' => 'Tambah Templat Program',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Templat Program baru',
            '_be_breadcrumbs' => ['Master Data','Master Templat Program','Tambah Templat Program'],
            '_be_card_title' => 'Tambah Templat Program',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.program_template.store', ['type' => $type]),
            '_be_home' => route('master.program_template.list'),
            '_be_type' => $type
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_program_template.form',compact('data'));
    }

    public function store(Request $request, $type)
    {
        $rules = $this->compile_repo->validateRule($this->get_form($type), ['code']);
        $validated = Validator::make($request->all(), array_merge([
            // 'code' => 'required|max:100|unique:mt_program_template,code,NULL,id,deleted_at,NULL',
        ], $rules));
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['code'] = $request->code;
        $data['budget_year_id'] = $request->budget_year_id ?? 0;
        $data['organization_id'] = $request->organization_id ?? 0;
        $data['program_goal_id'] = $request->program_goal_id ?? 0;
        $data['concern'] = $request->concern;
        $data['performance'] = $request->performance ?? '';
        $data['indicator'] = $request->indicator ?? '';
        $data['measure'] = $request->measure ?? '';
        $data['type'] = $type;

        DB::beginTransaction();
        try {
            $cond = "mt_program_template.code = '{$data['code']}' and mt_program_template.budget_year_id = '{$data['budget_year_id']}'";
            $records = $this->program_template_repo->getRecords($cond);
            if ($records->count() > 0){
                throw new \Exception("Program / Kegiatan / Sub Kegiatan Sudah Pernah Dibuat");
            }
            $this->program_template_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Templat Program', 201);
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

    public function edit($id, $type)
    {
        $data = [];
        $id = (int) $id;
        if($id == 0){
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $datas = [];
        $type = strtolower($type);
        $record = $this->program_template_repo->getRecord($id, $type);
        if ($record) {
            $datas = $record->toArray();
        } else {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $data = [
            'fields' => $this->get_form($type),
            'datas' => $datas,
            '_be_page_title' => 'Ubah Templat Program',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Templat Program',
            '_be_breadcrumbs' => ['Master Data','Master Templat Program','Ubah Templat Program'],
            '_be_card_title' => 'Ubah Templat Program',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.program_template.update',['id' => $id, 'type' => $type]),
            '_be_home' => route('master.program_template.list'),
            '_be_type' => $type
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_program_template.form',compact('data'));
    }

    public function update(Request $request, $id, $type)
    {
        $rules = $this->compile_repo->validateRule($this->get_form(strtolower($type)), ['code']);
        $validated = Validator::make($request->all(), array_merge([
            // 'code' => 'required|max:100|unique:mt_program_template,code,'.$id.',id,deleted_at,NULL',
        ], $rules));
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data['code'] = $request->code;
        $data['type'] = strtolower($type);
        $data['budget_year_id'] = $request->budget_year_id ?? 0;
        $data['organization_id'] = $request->organization_id ?? 0;
        $data['program_goal_id'] = $request->program_goal_id ?? 0;
        $data['concern'] = $request->concern;
        $data['performance'] = $request->performance ?? '';
        $data['indicator'] = $request->indicator ?? '';
        $data['measure'] = $request->measure ?? '';

        DB::beginTransaction();
        try {
            $cond = "mt_program_template.code = '{$data['code']}' and mt_program_template.budget_year_id = '{$data['budget_year_id']}' and mt_program_template.id <> '{$id}'";
            $records = $this->program_template_repo->getRecords($cond);
            if ($records->count() > 0){
                throw new \Exception("Program / Kegiatan / Sub Kegiatan Sudah Pernah Dibuat");
            }
            $this->program_template_repo->updateRecord($id, $data);
            $this->updateProgram($id);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Templat Program');
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
                $this->program_template_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Templat Program');
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

    public function updateProgram($id)
    {
        $allow_update = get_preference('auto_update_program', false);
        $data = $this->program_template_repo->getRecord($id);
        if ($allow_update && $data != null){
            $data = $data->toArray();
            switch (strtolower($data['type'])) {
                case 'program':
                    $cond = "tr_program.budget_year_id = '{$data['budget_year_id']}' and tr_program.code LIKE '{$data['code']}%'";
                    $data_update = ['program' => $data['concern']];
                    break;
                case 'activity':
                    $cond = "tr_program.budget_year_id = '{$data['budget_year_id']}' and tr_program.code LIKE '{$data['code']}%'";
                    $data_update = ['activity' => $data['concern']];
                    break;
                case 'sub_activity':
                    $cond = "tr_program.budget_year_id = '{$data['budget_year_id']}' and tr_program.code = '{$data['code']}'";
                    $data_update = [
                        'code' => $data['code'],
                        'program_goal_id' => $data['program_goal_id'],
                        'organization_id' => $data['organization_id'],
                        'sub_activity' => $data['concern']
                    ];
                    break;

                default:
                    # code...
                    break;
            }
            $this->program_repo->updateRecordByCond($cond, $data_update);
        }
    }
}
