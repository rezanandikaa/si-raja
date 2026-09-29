<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\Tr_program;
use App\Models\Transaction\Tr_program_budget;
use App\Models\Transaction\Tr_program_sifat_bantuan;
use App\Repositories\CompileRepository;
use App\Repositories\Master\OrganizationRepository;
use App\Repositories\Master\ProgramTemplateRepository;
use App\Repositories\Transaction\ProgramRealizationRepository;
use App\Repositories\Transaction\ProgramRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class ProgramController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $program_repo;
    protected $program_template_repo;
    protected $program_realization_repo;
    protected $organization_repo;

    public function __construct(
        CompileRepository $compile_repo,
        ProgramRepository $program_repo,
        ProgramTemplateRepository $program_template_repo,
        ProgramRealizationRepository $program_realization_repo,
        OrganizationRepository $organization_repo
    ) {
        $this->route_prefix = 'program.';
        $this->compile_repo = $compile_repo;
        $this->program_repo = $program_repo;
        $this->program_template_repo = $program_template_repo;
        $this->program_realization_repo = $program_realization_repo;
        $this->organization_repo = $organization_repo;
    }

    public function list()
    {
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $organization_parent_id = $user->organization_parent_id;

        $cond = "1=1";
        $cond .= " and tr_program.budget_year_id = '{$user->budget_year_id}'";
        $cond .= " and tr_program.status = 'ACTIVE'";
        $org_id = $user->organization_id;
        if (!general_organization($organization_id)) {
            if ($organization_parent_id != 0) {
                $cond .= " and tr_program.organization_id = '{$organization_parent_id}'";
                $cond .= " and tr_program.created_by_id = '{$user->id}'";
                $org_id = $organization_parent_id;
            } else {
                $cond .= " and tr_program.organization_id = '{$organization_id}'";
                $org_id = $organization_id;
            }

            $records = $this->program_repo->getDataTable($cond);
            $records = $records->get();

            $collection = collect($records);
            $ba = $collection->groupBy('organization_id')->map(function ($group) {
                return $group->sum('budget_allocation');
            });

            $records = $this->program_realization_repo->getDataTable($cond);
            $records = $records->get();

            $collection = collect($records);
            $br = $collection->groupBy('program_organization_id')->map(function ($group) {
                return $group->sum('budget_realization');
            });

            $budget_allocation = (isset($ba[$org_id]) ? $ba[$org_id] : 0);
            $budget_realization = (isset($br[$org_id]) ? $br[$org_id] : 0);
        } else {
            $records = $this->program_repo->getDataTable($cond);
            $records = $records->get();
            $ba = $records->sum('budget_allocation');

            $records = $this->program_realization_repo->getDataTable($cond);
            $records = $records->get();
            $br = $records->sum('budget_realization');

            $budget_allocation = $ba;
            $budget_realization = $br;
        }

        $summary_data = [
            ['title' => 'Alokasi Anggaran (' . get_budget_year($user->budget_year_id) . ')', 'value' => number_format(round($budget_allocation, 0)), 'description' => 'Total Pagu', 'class' => 'col-lg-6 col-md-6 col-sm-6'],
            ['title' => 'Realisasi Anggaran (' . get_budget_year($user->budget_year_id) . ')', 'value' => number_format(round($budget_realization, 0)), 'description' => 'Total Realisasi', 'class' => 'col-lg-6 col-md-6 col-sm-6'],
        ];

        $data = [
            '_be_page_title' => 'Daftar Kegiatan',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Kegiatan',
            '_be_breadcrumbs' => ['Kegiatan', 'Daftar Kegiatan'],
            '_be_insert' => route('program.insert'),
            'summary_data' => $summary_data
        ];
        return view('backpage.program.list', compact('data'));
    }

    public function get_data(Request $request)
    {
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $organization_parent_id = $user->organization_parent_id;
        $cond = "1=1";
        if (!general_organization($organization_id)) {
            if ($organization_parent_id != 0) {
                $cond .= " and tr_program.organization_id = '{$organization_parent_id}'";
                $cond .= " and tr_program.created_by_id = '{$user->id}'";
            } else {
                $cond .= " and tr_program.organization_id = '{$organization_id}'";
            }
        }
        $cond .= " " . $request->condition ?? "";
        if ($request->ajax()) {
            $data = Tr_program::leftJoin('mt_user as updated_by', 'tr_program.updated_by_id', 'updated_by.id')
                ->leftJoin('mt_user as created_by', 'tr_program.created_by_id', 'created_by.id')
                ->leftJoin('mt_organization as created_by_org', 'created_by.organization_id', 'created_by_org.id')
                ->leftJoin('mt_organization', 'tr_program.organization_id', 'mt_organization.id')
                ->leftJoin('mt_budget_year', 'tr_program.budget_year_id', 'mt_budget_year.id')
                ->leftJoin('mt_budget_source', 'tr_program.budget_source_id', 'mt_budget_source.id')
                ->leftJoin('sy_option as program_goal', 'tr_program.program_goal_id', 'program_goal.id')
                ->leftJoin('mt_region as district', 'tr_program.district_id', 'district.id')
                ->leftJoin('mt_region as subdistrict', 'tr_program.subdistrict_id', 'subdistrict.id')
                ->where('tr_program.status', '<>', 'DROPPED')
                ->where('tr_program.budget_year_id', $user->budget_year_id)
                ->whereNull('tr_program.deleted_at')
                ->when($request->kemdagri_code != null && $request->kemdagri_code != '', function ($q) use ($request) {
                    $q->where('subdistrict.code', 'LIKE', "{$request->kemdagri_code}%");
                })
                ->when($cond != '', function ($q) use ($cond) {
                    $q->whereRaw($cond);
                })
                ->select(
                    'tr_program.*',
                    'mt_budget_year.name as budget_year_name',
                    'mt_budget_source.name as budget_source_name',
                    'mt_organization.name as organization_name',
                    'program_goal.value as strategy_program_name',
                    'district.name as district_name',
                    'subdistrict.name as subdistrict_name',
                    'updated_by.name as updated_by_name',
                    'created_by_org.name as created_by_organization_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function ($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->editColumn('budget_allocation', function ($data) {
                    $ba = number_format($data->budget_allocation, 2);
                    return "<div class='text text-right'>{$ba}</div>";
                })
                ->editColumn('budget_realization', function ($data) {
                    $br = number_format($data->budget_realization, 2);
                    return "<div class='text text-right'>{$br}</div>";
                })
                ->editColumn('status', function ($data) {
                    $color = 'info';
                    switch ($data->status) {
                        case 'ACTIVE':
                            $color = 'success';
                            break;
                        case 'DROPPED':
                            $color = 'danger';
                            break;

                        default:
                            # code...
                            break;
                    }
                    return '<span class="badge light badge-' . $color . '">' . $data->status . '</span>';
                })
                ->addIndexColumn()
                ->addColumn('percentage', function ($data) {
                    $percentage = $this->compile_repo->customDivide($data->budget_realization, $data->budget_allocation);
                    return "<div class='text text-right'>" . number_format($percentage * 100, 2) . "</div>";
                })
                ->addColumn('action', function ($data) {
                    // $btn = '<div class="btn-group">
                    //     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('program.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    if ($data->status == 'DRAFT') {
                        $btn = '
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Opsi
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                                <a href="' . route($this->route_prefix . 'budget.list', $data->id) . '" class="dropdown-item">Sumber Pembiayaan</a>
                                <a href="' . route($this->route_prefix . 'sifat_bantuan.list', $data->id) . '" class="dropdown-item">Sifat Bantuan</a>
                                <a data-id="confirm-' . $data->id . '" data-url="' . route('program.confirmation') . '" class="confirm dropdown-item">Konfirmasi</a>
                                <a href="' . route($this->route_prefix . 'edit', $data->id) . '" class="dropdown-item">Ubah</a>
                                <a data-id="delete-' . $data->id . '" data-url="' . route('program.delete') . '" class="delete dropdown-item">Hapus</a>
                            </div>
                        </div>
                        ';
                    } else {
                        $btn = '
                        <div class="btn-group" role="group">
                            <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Opsi
                            </button>
                            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                                <a href="' . route($this->route_prefix . 'budget.list', $data->id) . '" class="dropdown-item">Sumber Pembiayaan</a>
                                <a href="' . route($this->route_prefix . 'sifat_bantuan.list', $data->id) . '" class="dropdown-item">Sifat Bantuan</a>
                                <a data-id="cancel-' . $data->id . '" data-url="' . route('program.cancel') . '" class="cancel dropdown-item">Batalkan</a>
                            </div>
                        </div>
                        ';
                    }
                    return $btn;
                })
                ->rawColumns(['status', 'action', 'percentage', 'budget_allocation', 'budget_realization'])
                ->make(true);
        }
    }

    public function get_form()
    {
        $fields = [];

        $fields['budget_year_id'] = [
            'label' => 'Tahun Anggaran',
            'name' => 'budget_year_id',
            'placeholder' => 'Tahun Anggaran',
            'type' => 'data',
            'data_table' => 'mt_budget_year',
            'maxlength' => 100,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Tahun Anggaran wajib diisi'
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

        $fields['program_template_id'] = [
            'label' => 'Pilihan Program',
            'name' => 'program_template_id',
            'placeholder' => 'Pilihan Program',
            'type' => 'data',
            'data_table' => 'mt_program_template',
            'data_condition' => ' and mt_program_template.type = "sub_activity"',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Pilihan Program wajib diisi'
        ];

        $fields['district_id'] = [
            'label' => 'Kecamatan',
            'name' => 'district_id',
            'placeholder' => 'Kecamatan',
            'type' => 'data',
            'data_table' => 'mt_region',
            'data_condition' => ' and mt_region.type = "3-KECAMATAN"',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Kecamatan wajib diisi'
        ];

        $fields['subdistrict_id'] = [
            'label' => 'Desa/Kelurahan',
            'name' => 'subdistrict_id',
            'placeholder' => 'Desa/Kelurahan',
            'type' => 'data',
            'data_table' => 'mt_region',
            'data_condition' => ' and mt_region.type = "4-DESA-KELURAHAN"',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Desa/Kelurahan wajib diisi'
        ];

        $fields['marker'] = [
            'label' => 'Geo Tag',
            'name' => 'marker',
            'placeholder' => 'Geo Tag',
            'type' => 'map-marker',
            'map_type' => 'maptiler',
            'required' => true,
            'show_only' => false,
            // 'parsley' => 'data-parsley-type="email"',
            'validate_message' => 'Geo Tag wajib diisi'
        ];

        // Ditampilkan di kolom "Lokasi Tambahan" pada list Program,
        // digabung dengan nama kecamatan di kolom "Lokasi" excel. Tidak wajib diisi.
        $fields['lokasi_tambahan'] = [
            'label' => 'Lokasi Tambahan',
            'name' => 'lokasi_tambahan',
            'placeholder' => 'Lokasi Tambahan',
            'type' => 'text-area',
            'maxlength' => 255,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Lokasi Tambahan wajib diisi'
        ];

        $fields['program_uuid'] = [
            'label' => 'Program UUID',
            'name' => 'program_uuid',
            'placeholder' => 'Program UUID',
            'type' => 'hidden',
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Program UUID wajib diisi'
        ];


        $fields['description'] = [
            'label' => 'Catatan',
            'name' => 'description',
            'placeholder' => 'Catatan',
            'type' => 'text-area',
            'maxlength' => 1000,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Catatan wajib diisi'
        ];

        // Ditampilkan di kolom "Aktivitas Real" pada list Program. Tidak wajib diisi.
        $fields['aktivitas_real_langsung'] = [
            'label' => 'Aktivitas Langsung',
            'name' => 'aktivitas_real_langsung',
            'placeholder' => 'Silahkan isi Aktivitas Langsung',
            'type' => 'text',
            'maxlength' => 255,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Aktivitas Langsung wajib diisi'
        ];

        $fields['aktivitas_real_tidak_langsung'] = [
            'label' => 'Aktivitas Tidak Langsung',
            'name' => 'aktivitas_real_tidak_langsung',
            'placeholder' => 'Silahkan isi Aktivitas Tidak Langsung',
            'type' => 'text',
            'maxlength' => 255,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Aktivitas Tidak Langsung wajib diisi'
        ];

        $fields['aktivitas_real_penunjang'] = [
            'label' => 'Aktivitas Penunjang',
            'name' => 'aktivitas_real_penunjang',
            'placeholder' => 'Silahkan isi Aktivitas Penunjang',
            'type' => 'text',
            'maxlength' => 255,
            'required' => false,
            'show_only' => false,
            'validate_message' => 'Aktivitas Penunjang wajib diisi'
        ];

        // $fields['code'] = [
        //     'label' => 'Kode Sub Kegiatan',
        //     'name' => 'code',
        //     'placeholder' => 'Kode Sub Kegiatan',
        //     'type' => 'text',
        //     'maxlength' => 100,
        //     'required' => true,
        //     'show_only' => false,
        //     'validate_message' => 'Kode Sub Kegiatan wajib diisi'
        // ];

        // $fields['name'] = [
        //     'label' => 'Nama Kegiatan',
        //     'name' => 'name',
        //     'placeholder' => 'Nama Kegiatan',
        //     'type' => 'text-area',
        //     'maxlength' => 300,
        //     'required' => true,
        //     'show_only' => false,
        //     'validate_message' => 'Nama Kegiatan wajib diisi'
        // ];

        // $fields['sub_name'] = [
        //     'label' => 'Nama Sub Kegiatan',
        //     'name' => 'sub_name',
        //     'placeholder' => 'Nama Sub Kegiatan',
        //     'type' => 'text-area',
        //     'maxlength' => 300,
        //     'required' => true,
        //     'show_only' => false,
        //     'validate_message' => 'Nama Sub Kegiatan wajib diisi'
        // ];

        // $fields['budget_source_id'] = [
        //     'label' => 'Sumber Pembiayaan',
        //     'name' => 'budget_source_id',
        //     'placeholder' => 'Sumber Pembiayaan',
        //     'type' => 'data',
        //     'data_table' => 'mt_budget_source',
        //     'data_condition' => '',
        //     'data_extra' => '',
        //     'required' => true,
        //     'show_only' => false,
        //     'validate_message' => 'Sumber Pembiayaan wajib diisi'
        // ];

        // $fields['budget_allocation'] = [
        //     'label' => 'Pagu',
        //     'name' => 'budget_allocation',
        //     'placeholder' => 'Pagu',
        //     'type' => 'number',
        //     'required' => true,
        //     'show_only' => false,
        //     'validate_message' => 'Pagu wajib diisi'
        // ];

        // $fields['active_flag'] = [
        //     'label' => 'Status',
        //     'name' => 'active_flag',
        //     'placeholder' => 'Status',
        //     'type' => 'checkbox',
        //     'required' => false,
        //     'show_only' => false,
        //     'validate_message' => 'Status wajib diisi'
        // ];

        return $fields;
    }

    public function insert()
    {
        $data = [];

        $datas = [
            'program_uuid' => Uuid::uuid4(),
        ];

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Tambah Kegiatan',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Kegiatan baru',
            '_be_breadcrumbs' => ['Kegiatan', 'Tambah Kegiatan'],
            '_be_card_title' => 'Tambah Kegiatan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('program.store'),
            '_be_home' => route('program.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.form', compact('data'));
    }

    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), $this->compile_repo->validateRule($this->get_form()));
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $program_template_id = $request->program_template_id;
        $program_template = $this->program_template_repo->getRecord($program_template_id);

        $data['budget_year_id'] = $request->budget_year_id ?? 0;

        if ($program_template) {
            $data['code'] = $program_template->code;
            $data['sub_activity'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'sub_activity', 'concern', $data['budget_year_id']);
            $data['organization_id'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'sub_activity', 'organization_id', $data['budget_year_id']);
            $data['activity'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'activity', 'concern', $data['budget_year_id']);
            $data['program'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'program', 'concern', $data['budget_year_id']);
        }

        // $data['budget_year_id'] = $request->budget_year_id ?? 0;
        $data['program_goal_id'] = $request->program_goal_id ?? 0;
        $data['program_template_id'] = $request->program_template_id ?? 0;
        $data['district_id'] = $request->district_id ?? 0;
        $data['subdistrict_id'] = $request->subdistrict_id ?? 0;
        $data['marker'] = strtolower(($request->marker ?? 'null'));
        $data['budget_source_id'] = 0;
        $data['budget_allocation'] = 0;
        $data['status'] = 'DRAFT';
        $data['description'] = $request->description ?? '';
        $data['aktivitas_real_langsung'] = $request->aktivitas_real_langsung;
        $data['aktivitas_real_tidak_langsung'] = $request->aktivitas_real_tidak_langsung;
        $data['aktivitas_real_penunjang'] = $request->aktivitas_real_penunjang;
        $data['lokasi_tambahan'] = $request->lokasi_tambahan ?? '';

        try {
            DB::beginTransaction();
            $record_check = $this->program_repo->getRecordBy('program_uuid', $request->program_uuid);
            if ($record_check != null) {
                throw new \Exception("Program ini telah dibuat, mohon untuk kembali ke menu Rencana Kegiatan");
            }
            $data['program_uuid'] = $request->program_uuid;
            $this->program_repo->insertRecord($data);
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
        if ($id == 0) {
            return redirect(route($this->route_prefix . 'list'))->with('error', 'Tidak dapat menemukan ID');
        }

        $datas = [];
        $record = $this->program_repo->getRecord($id);
        if ($record && $record->status == 'DRAFT') {
            $record->real_organization_id = $record->organization_parent_id == 0 ? $record->organization_id : $record->organization_parent_id;
            $datas = $record->toArray();
        } else {
            return redirect(route($this->route_prefix . 'list'))->with('error', 'Tidak dapat menemukan ID');
        }

        $form = $this->get_form();
        $form['program_template_id']['data_condition'] = ' and mt_program_template.organization_id = "' . $record->organization_id . '" and mt_program_template.type = "sub_activity"';
        if ($record->budget_year_id != 0) {
            $form['program_template_id']['data_condition'] .= ' and mt_program_template.budget_year_id = "' . $record->budget_year_id . '"';
        }
        if ($record->program_goal_id != 0) {
            $form['program_template_id']['data_condition'] .= ' and mt_program_template.program_goal_id = "' . $record->program_goal_id . '"';
        }
        if ($record->district_id != 0) {
            $form['subdistrict_id']['data_condition'] = $form['subdistrict_id']['data_condition'] . ' and mt_region.parent_id = "' . $record->district_id . '"';
        }

        $data = [
            'fields' => $form,
            'datas' => $datas,
            '_be_page_title' => 'Ubah Kegiatan',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Kegiatan',
            '_be_breadcrumbs' => ['Kegiatan', 'Ubah Kegiatan'],
            '_be_card_title' => 'Ubah Kegiatan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('program.update', $id),
            '_be_home' => route('program.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.form', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), $this->compile_repo->validateRule($this->get_form()));
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $program_template_id = $request->program_template_id;
        $program_template = $this->program_template_repo->getRecord($program_template_id);

        $data['budget_year_id'] = $request->budget_year_id ?? 0;

        if ($program_template) {
            $data['code'] = $program_template->code;
            $data['sub_activity'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'sub_activity', 'concern', $data['budget_year_id']);
            $data['organization_id'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'sub_activity', 'organization_id', $data['budget_year_id']);
            $data['activity'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'activity', 'concern', $data['budget_year_id']);
            $data['program'] = $this->program_template_repo->getValueBySubActivityCode($program_template->code, 'program', 'concern', $data['budget_year_id']);
        }

        // $data['budget_year_id'] = $request->budget_year_id ?? 0;
        $data['program_goal_id'] = $request->program_goal_id ?? 0;
        $data['program_template_id'] = $request->program_template_id ?? 0;
        $data['district_id'] = $request->district_id ?? 0;
        $data['subdistrict_id'] = $request->subdistrict_id ?? 0;
        $data['marker'] = strtolower(($request->marker ?? 'null'));
        $data['aktivitas_real_langsung'] = $request->aktivitas_real_langsung;
        $data['aktivitas_real_tidak_langsung'] = $request->aktivitas_real_tidak_langsung;
        $data['aktivitas_real_penunjang'] = $request->aktivitas_real_penunjang;
        $data['lokasi_tambahan'] = $request->lokasi_tambahan ?? '';

        try {
            DB::beginTransaction();
            $this->program_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Kegiatan');
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
        $record = $this->program_repo->getRecord($id);
        if ($id != 0 && $record->status == 'DRAFT') {
            try {
                DB::beginTransaction();
                $this->program_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Kegiatan');
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

    public function confirmation(Request $request)
    {
        $confirm_id = $request->id ?? 0;
        $record = $this->program_repo->getRecord($confirm_id);
        if ($confirm_id != 0 && $record->status == 'DRAFT' && $record->budget_allocation > 0) {
            DB::beginTransaction();
            try {
                $this->program_repo->updateRecord($confirm_id, ['status' => 'ACTIVE']);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data berganti status menjadi ACTIVE'
                ];
                logbook('Berhasil konfirmasi Kegiatan');
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
                'message' => 'Tidak ada ID yang dikonfirmasi atau status dokumen tidak sama dengan DRAFT atau Sumber Pembiayaan belum diinput'
            ];
        }
        return response()->json($result);
    }

    public function cancel(Request $request)
    {
        $confirm_id = $request->id ?? 0;
        $record = $this->program_repo->getRecord($confirm_id);
        if ($confirm_id != 0 && $record->status == 'ACTIVE') {
            DB::beginTransaction();
            try {
                $this->program_repo->updateRecord($confirm_id, ['status' => 'DRAFT', 'budget_realization' => 0]);
                $this->program_realization_repo->deleteRecordByProgramId($confirm_id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data berganti status menjadi DRAFT'
                ];
                logbook('Berhasil konfirmasi Kegiatan');
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
                'message' => 'Tidak ada ID yang dikonfirmasi atau status dokumen tidak sama dengan DRAFT atau Sumber Pembiayaan belum diinput'
            ];
        }
        return response()->json($result);
    }

    public function budget_list($id)
    {
        $data = [
            '_be_page_title' => 'Daftar Sumber Pembiayaan',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Sumber Pembiayaan',
            '_be_breadcrumbs' => ['Program', 'Daftar Sumber Pembiayaan'],
            '_be_insert' => route('program.budget.insert', ['id' => $id]),
            '_parent_id' => $id
        ];
        return view('backpage.program.budget.list', compact('data'));
    }

    public function budget_get_data(Request $request)
    {
        $id = $request->id ?? 0;

        if ($request->ajax()) {
            $data = Tr_program_budget::leftJoin('mt_user as updated_by', 'tr_program_budget.updated_by_id', 'updated_by.id')
                ->leftJoin('tr_program', 'tr_program_budget.program_id', 'tr_program.id')
                ->leftJoin('mt_budget_source', 'tr_program_budget.budget_source_id', 'mt_budget_source.id')
                ->where('tr_program_budget.program_id', $id)
                ->whereNull('tr_program_budget.deleted_at')
                ->select(
                    'tr_program_budget.*',
                    'mt_budget_source.name as budget_source_name',
                    'updated_by.name as updated_by_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function ($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->editColumn('budget_allocation', function ($data) {
                    $ba = number_format($data->budget_allocation, 2);
                    return "<div class='text text-right'>{$ba}</div>";
                })
                ->editColumn('budget_stage', function ($data) {
                    $variant = ['Murni' => 'primary', 'Pergeseran' => 'warning', 'Perubahan' => 'info'][$data->budget_stage] ?? 'default';
                    return '<span class="badge light badge-' . $variant . '">' . ($data->budget_stage ?: '-') . '</span>';
                })
                ->addIndexColumn()
                ->addColumn('action', function ($data) use ($id) {
                    // Pagu awal (Murni) jadi dasar perhitungan tahapan lain, jadi tidak boleh diubah/dihapus.
                    if ($data->budget_stage == 'Murni') {
                        return '-';
                    }
                    $btn = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="' . route('program.budget.edit', ['id' => $id, 'budget_id' => $data->id]) . '" class="dropdown-item">Edit</a>
                            <a data-id="delete-' . $data->id . '" data-url="' . route('program.budget.delete', ['id' => $id]) . '" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['budget_stage', 'action', 'budget_allocation'])
                ->make(true);
        }
    }

    public function budget_get_form()
    {
        $fields = [];

        $fields['budget_source_id'] = [
            'label' => 'Sumber Pembiayaan',
            'name' => 'budget_source_id',
            'placeholder' => 'Sumber Pembiayaan',
            'type' => 'data',
            'data_table' => 'mt_budget_source',
            'data_condition' => '',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Sumber Pembiayaan wajib diisi'
        ];

        $fields['budget_allocation'] = [
            'label' => 'Pagu',
            'name' => 'budget_allocation',
            'placeholder' => 'Pagu',
            'type' => 'decimal',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Pagu wajib diisi'
        ];

        $fields['budget_stage'] = [
            'label' => 'Anggaran',
            'name' => 'budget_stage',
            'placeholder' => 'Anggaran',
            'type' => 'select',
            'options' => ['Murni', 'Pergeseran', 'Perubahan'],
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Anggaran wajib diisi'
        ];

        // $fields['active_flag'] = [
        //     'label' => 'Status',
        //     'name' => 'active_flag',
        //     'placeholder' => 'Status',
        //     'type' => 'checkbox',
        //     'required' => false,
        //     'show_only' => false,
        //     'validate_message' => 'Status wajib diisi'
        // ];

        return $fields;
    }

    public function budget_insert($id)
    {
        $record = $this->program_repo->getRecord($id);
        // if ($record->status != 'DRAFT') {
        //     return redirect()->back();
        // }
        $forms = $this->budget_get_form();
        $data = [];

        $data = [
            'fields' => $forms,
            'datas' => [],
            '_be_page_title' => 'Tambah Sumber Pembiayaan',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Sumber Pembiayaan baru',
            '_be_breadcrumbs' => ['Program', 'Tambah Sumber Pembiayaan'],
            '_be_card_title' => 'Tambah Sumber Pembiayaan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('program.budget.store', ['id' => $id]),
            '_be_home' => route('program.budget.list', ['id' => $id])
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.budget.form', compact('data'));
    }

    public function budget_store(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'budget_source_id' => 'required',
            'budget_allocation' => 'required',
            'budget_stage' => 'required'
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $stage = $request->budget_stage;
        $allocation = (float) $request->budget_allocation;
        $budgets = $this->program_repo->getRecordsBudget('tr_program_budget.program_id = ' . (int) $id);

        $stage_error = $this->validate_budget_stage($stage, $request->budget_source_id, $allocation, $budgets);
        if ($stage_error) {
            return response()->json([
                'status' => 'FAIL',
                'message' => $stage_error
            ]);
        }

        $data['program_id'] = $id;
        $data['budget_source_id'] = $request->budget_source_id;
        $data['budget_allocation'] = $allocation;
        $data['budget_stage'] = $stage;

        $program = $this->program_repo->getRecord($id);
        if ($program->status != 'DRAFT') {
            $data['refocusing_flag'] = true;
        }

        try {
            DB::beginTransaction();
            // Selalu baris baru: baris tahapan sebelumnya disimpan sebagai history.
            $this->program_repo->insertRecordBudget($data);
            $this->recalculateBudgetAllocation($id);

            $program = $this->program_repo->getRecord($id);
            if ($program->budget_allocation < $program->budget_realization) {
                throw new \Exception("Total Alokasi Anggaran tidak boleh melebihi Realisasi");
            }
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Program Budget', 201);
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

    /**
     * Aturan tahapan Anggaran. Return pesan error, atau null kalau lolos.
     */
    private function validate_budget_stage($stage, $budget_source_id, $allocation, $budgets)
    {
        // Belum ada pagu sama sekali: tahapan wajib Murni.
        if ($budgets->isEmpty() && $stage != 'Murni') {
            return 'Oopps, Masih Tahapan Murni';
        }

        // Pagu awal cuma boleh diinput sekali.
        if ($stage == 'Murni' && $budgets->where('budget_stage', 'Murni')->isNotEmpty()) {
            return 'Pagu Awal Sudah di Input';
        }

        // Pergeseran cuma mencatat ulang tahapan, pagu sumber pembiayaan itu tidak boleh berubah.
        if ($stage == 'Pergeseran') {
            $previous = $budgets
                ->where('budget_source_id', $budget_source_id)
                ->sortBy('id')
                ->last();

            if ($previous && round((float) $previous->budget_allocation, 2) != round($allocation, 2)) {
                return 'Nilai Pagu tidak Boleh di Ubah';
            }
        }

        return null;
    }

    public function budget_edit($id, $budget_id)
    {
        $record = $this->program_repo->getRecordBudget($budget_id);
        if (!$this->budget_editable($record)) {
            return redirect(route('program.budget.list', ['id' => $id]))->with('error', 'Data tidak dapat diubah');
        }

        $data = [
            'fields' => $this->budget_get_form(),
            'datas' => $record->toArray(),
            '_be_page_title' => 'Ubah Sumber Pembiayaan',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah Sumber Pembiayaan',
            '_be_breadcrumbs' => ['Program', 'Ubah Sumber Pembiayaan'],
            '_be_card_title' => 'Ubah Sumber Pembiayaan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('program.budget.update', ['id' => $id, 'budget_id' => $budget_id]),
            '_be_home' => route('program.budget.list', ['id' => $id])
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.budget.form', compact('data'));
    }

    public function budget_update(Request $request, $id, $budget_id)
    {
        $validated = Validator::make($request->all(), [
            'budget_source_id' => 'required',
            'budget_allocation' => 'required',
            'budget_stage' => 'required'
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $record = $this->program_repo->getRecordBudget($budget_id);
        if (!$this->budget_editable($record)) {
            return response()->json([
                'status' => 'FAIL',
                'message' => 'Data tidak dapat diubah'
            ]);
        }

        $stage = $request->budget_stage;
        $allocation = (float) $request->budget_allocation;
        // Baris yang sedang diedit dibuang dari pembanding supaya tidak bentrok dengan dirinya sendiri.
        $budgets = $this->program_repo->getRecordsBudget('tr_program_budget.program_id = ' . (int) $id)
            ->where('id', '!=', $budget_id);

        $stage_error = $this->validate_budget_stage($stage, $request->budget_source_id, $allocation, $budgets);
        if ($stage_error) {
            return response()->json([
                'status' => 'FAIL',
                'message' => $stage_error
            ]);
        }

        $data['budget_source_id'] = $request->budget_source_id;
        $data['budget_allocation'] = $allocation;
        $data['budget_stage'] = $stage;

        try {
            DB::beginTransaction();
            $this->program_repo->updateRecordBudget($budget_id, $data);
            $this->recalculateBudgetAllocation($id);

            $program = $this->program_repo->getRecord($id);
            if ($program->budget_allocation < $program->budget_realization) {
                throw new \Exception("Total Alokasi Anggaran tidak boleh melebihi Realisasi");
            }
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Program Budget');
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

    /**
     * Baris Murni adalah pagu awal, tidak boleh diubah/dihapus. Selain itu
     * ikut aturan hapus: dokumen masih DRAFT atau baris hasil refocusing.
     */
    private function budget_editable($record)
    {
        if (!$record) {
            return false;
        }
        if ($record->budget_stage == 'Murni') {
            return false;
        }
        return $record->status == 'DRAFT' || $record->refocusing_flag;
    }

    public function budget_destroy(Request $request, $id)
    {
        $budget_id = $request->id ?? 0;
        $record = $this->program_repo->getRecordBudget($budget_id);
        if ($budget_id == 0 || !$this->budget_editable($record)) {
            return response()->json([
                'status' => 'FAIL',
                'message' => ($record && $record->budget_stage == 'Murni')
                    ? 'Anggaran Murni tidak dapat diubah atau dihapus'
                    : 'Tidak ada ID yang dikonfirmasi atau status dokumen tidak sama dengan DRAFT'
            ]);
        }

        try {
            DB::beginTransaction();
            $this->program_repo->deleteRecordBudget($budget_id);
            $this->recalculateBudgetAllocation($id);
            $result = [
                'status' => 'OK',
                'message' => 'Data dihapus'
            ];
            logbook('Berhasil menghapus Program Budget');
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

    public function sifat_bantuan_list($id)
    {
        $data = [
            '_be_page_title' => 'Sifat Bantuan',
            '_be_page_title_desc' => 'Halaman ini adalah daftar Sifat Bantuan program',
            '_be_breadcrumbs' => ['Program', 'Sifat Bantuan'],
            '_be_insert' => route('program.sifat_bantuan.insert', ['id' => $id]),
            '_parent_id' => $id
        ];

        return view('backpage.program.sifat-bantuan.list', compact('data'));
    }

    public function sifat_bantuan_get_data(Request $request)
    {
        $id = $request->id;

        $data = Tr_program_sifat_bantuan::leftJoin('mt_user as updated_by', 'tr_program_sifat_bantuan.updated_by_id', 'updated_by.id')
            ->where('tr_program_sifat_bantuan.program_id', $id)
            ->whereNull('tr_program_sifat_bantuan.deleted_at')
            ->select(
                'tr_program_sifat_bantuan.*',
                'updated_by.name as updated_by_name'
            );

        return DataTables::eloquent($data)
            ->editColumn('updated_at', function ($data) {
                $ua = Carbon::parse($data->updated_at)->format('d M Y H:i');
                return "<div class='text text-center'>$ua</div>";
            })
            ->addColumn('action', function ($data) {
                $edit = route('program.sifat_bantuan.edit', ['id' => $data->program_id, 'sifat_bantuan_id' => $data->id]);
                $delete = route('program.sifat_bantuan.delete', ['id' => $data->program_id]);

                return '<div class="btn-group">
                            <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="' . $edit . '" class="dropdown-item">Ubah</a>
                                <a href="javascript:void(0)" class="dropdown-item delete" data-id="delete-' . $data->id . '" data-url="' . $delete . '">Hapus</a>
                            </div>
                        </div>';
            })
            ->rawColumns(['updated_at', 'action'])
            ->make(true);
    }

    public function sifat_bantuan_get_form()
    {
        $fields = [];

        $fields['sifat_bantuan'] = [
            'label' => 'Sifat Bantuan',
            'name' => 'sifat_bantuan',
            'placeholder' => 'Sifat Bantuan',
            'type' => 'text-area',
            'required' => true,
            'show_only' => false,
            'maxlength' => 255,
            'validate_message' => 'Sifat Bantuan wajib diisi'
        ];

        return $fields;
    }

    public function sifat_bantuan_insert($id)
    {
        $data = [];
        $forms = $this->sifat_bantuan_get_form();

        $data = [
            'fields' => $forms,
            'datas' => [],
            '_be_page_title' => 'Tambah Sifat Bantuan',
            '_be_page_title_desc' => 'Halaman ini untuk menambah Sifat Bantuan',
            '_be_breadcrumbs' => ['Program', 'Tambah Sifat Bantuan'],
            '_be_card_title' => 'Tambah Sifat Bantuan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('program.sifat_bantuan.store', ['id' => $id]),
            '_be_home' => route('program.sifat_bantuan.list', ['id' => $id])
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.sifat-bantuan.form', compact('data'));
    }

    public function sifat_bantuan_store(Request $request, $id)
    {
        $validate = $this->compile_repo->validateRule($this->sifat_bantuan_get_form());
        $request->validate($validate);

        DB::beginTransaction();
        try {
            Tr_program_sifat_bantuan::create([
                'program_id' => $id,
                'sifat_bantuan' => $request->sifat_bantuan,
            ]);

            DB::commit();
            $result = [
                'status' => 'OK',
                'message' => 'Sifat Bantuan berhasil disimpan',
                'redirect' => route('program.sifat_bantuan.list', ['id' => $id])
            ];
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

    public function sifat_bantuan_edit($id, $sifat_bantuan_id)
    {
        $record = Tr_program_sifat_bantuan::where('program_id', $id)->find($sifat_bantuan_id);

        if (!$record) {
            return redirect(route('program.sifat_bantuan.list', ['id' => $id]))->with('error', 'Data tidak dapat ditemukan');
        }

        $data = [
            'fields' => $this->sifat_bantuan_get_form(),
            'datas' => $record->toArray(),
            '_be_page_title' => 'Ubah Sifat Bantuan',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah Sifat Bantuan',
            '_be_breadcrumbs' => ['Program', 'Ubah Sifat Bantuan'],
            '_be_card_title' => 'Ubah Sifat Bantuan',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('program.sifat_bantuan.update', ['id' => $id, 'sifat_bantuan_id' => $sifat_bantuan_id]),
            '_be_home' => route('program.sifat_bantuan.list', ['id' => $id])
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.sifat-bantuan.form', compact('data'));
    }

    public function sifat_bantuan_update(Request $request, $id, $sifat_bantuan_id)
    {
        $validate = $this->compile_repo->validateRule($this->sifat_bantuan_get_form());
        $request->validate($validate);

        DB::beginTransaction();
        try {
            $record = Tr_program_sifat_bantuan::where('program_id', $id)->find($sifat_bantuan_id);
            if (!$record) {
                throw new \Exception('Data tidak dapat ditemukan');
            }

            $record->update(['sifat_bantuan' => $request->sifat_bantuan]);

            DB::commit();
            $result = [
                'status' => 'OK',
                'message' => 'Sifat Bantuan berhasil diubah',
                'redirect' => route('program.sifat_bantuan.list', ['id' => $id])
            ];
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

    public function sifat_bantuan_destroy(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $record = Tr_program_sifat_bantuan::where('program_id', $id)->find($request->id);
            if (!$record) {
                throw new \Exception('Data tidak dapat ditemukan');
            }

            $record->delete();

            DB::commit();
            $result = [
                'status' => 'OK',
                'message' => 'Sifat Bantuan berhasil dihapus'
            ];
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

    public function recalculateBudgetAllocation($id)
    {
        $cond = 'tr_program_budget.program_id = "' . $id . '"';
        $records = $this->program_repo->getRecordsBudget($cond);

        // Pagu program = jumlah pagu TERAKHIR tiap sumber pembiayaan. Baris tahapan
        // sebelumnya tetap tampil sebagai history, tapi tidak ikut dihitung supaya
        // pagu lama tidak dobel dengan pagu penggantinya.
        $budget_allocation = 0;
        foreach ($records->groupBy('budget_source_id') as $per_source) {
            $budget_allocation += (float) $per_source->sortBy('id')->last()->budget_allocation;
        }

        // update
        $this->program_repo->updateRecord($id, ['budget_allocation' => $budget_allocation]);
    }
}
