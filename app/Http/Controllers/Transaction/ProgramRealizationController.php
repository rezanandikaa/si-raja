<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\System\Sy_attachment;
use App\Models\Transaction\Tr_program_realization;
use App\Models\Transaction\Tr_program_realization_bnba;
use App\Repositories\CompileRepository;
use App\Repositories\System\AttachmentRepository;
use App\Repositories\Transaction\ProgramRealizationRepository;
use App\Repositories\Transaction\ProgramRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Yajra\DataTables\Facades\DataTables;

class ProgramRealizationController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $program_realization_repo;
    protected $program_repo;
    protected $attachment_repo;

    public function __construct(
        CompileRepository $compile_repo,
        ProgramRealizationRepository $program_realization_repo,
        ProgramRepository $program_repo,
        AttachmentRepository $attachment_repo
    ) {
        $this->route_prefix = 'program.realization.';
        $this->compile_repo = $compile_repo;
        $this->program_realization_repo = $program_realization_repo;
        $this->program_repo = $program_repo;
        $this->attachment_repo = $attachment_repo;
    }

    public function list()
    {
        $user = Auth::user();
        $organization_id = $user->organization_id;
        $organization_parent_id = $user->organization_parent_id;

        $cond = "1=1";
        $cond .= " and tr_program.budget_year_id = '{$user->budget_year_id}'";

        if (!general_organization($organization_id)) {
            if ($organization_parent_id != 0) {
                $cond .= " and tr_program.organization_id = '{$organization_parent_id}'";
                $cond .= " and tr_program.created_by_id = '{$user->id}'";
            } else {
                $cond .= " and tr_program.organization_id = '{$organization_id}'";
            }
        }

        $records = $this->program_realization_repo->getDataTable($cond);
        $records = $records->get();

        $collection = collect($records);
        $tw = $collection->groupBy('quarterly')->map(function ($group) {
            return $group->sum('budget_realization');
        });
        $tw_1 = isset($tw[1]) ? $tw[1] : 0;
        $tw_2 = isset($tw[2]) ? $tw[2] : 0;
        $tw_3 = isset($tw[3]) ? $tw[3] : 0;
        $tw_4 = isset($tw[4]) ? $tw[4] : 0;

        $summary_data = [
            ['title' => 'Triwulan #1', 'value' => number_format(round($tw_1, 0)), 'description' => 'Total Realisasi', 'class' => 'col-lg-3 col-md-6 col-sm-6'],
            ['title' => 'Triwulan #2', 'value' => number_format(round($tw_2, 0)), 'description' => 'Total Realisasi', 'class' => 'col-lg-3 col-md-6 col-sm-6'],
            ['title' => 'Triwulan #3', 'value' => number_format(round($tw_3, 0)), 'description' => 'Total Realisasi', 'class' => 'col-lg-3 col-md-6 col-sm-6'],
            ['title' => 'Triwulan #4', 'value' => number_format(round($tw_4, 0)), 'description' => 'Total Realisasi', 'class' => 'col-lg-3 col-md-6 col-sm-6'],
        ];
        $data = [
            '_be_page_title' => 'Realisasi Program',
            '_be_page_title_desc' => 'Halaman ini adalah semua Realisasi Program',
            '_be_breadcrumbs' => ['Program', 'Realisasi Program'],
            '_be_insert' => route('program.realization.insert'),
            'summary_data' => $summary_data
        ];
        return view('backpage.program.realization.list', compact('data'));
    }

    public function get_data(Request $request, $source = '')
    {
        if ($source == 'chart') {
            $cond = $request->condition ?? '';
        } else {
            $user = Auth::user();
            $organization_id = $user->organization_id;
            $organization_parent_id = $user->organization_parent_id;
            $cond = "1=1";
            $cond .= " and tr_program.budget_year_id = '{$user->budget_year_id}'";
            if (!general_organization($organization_id)) {
                if ($organization_parent_id != 0) {
                    $cond .= " and tr_program.organization_id = '{$organization_parent_id}'";
                    $cond .= " and tr_program.created_by_id = '{$user->id}'";
                } else {
                    $cond .= " and tr_program.organization_id = '{$organization_id}'";
                }
            }
            $cond .= " " . $request->condition ?? "";
        }

        if ($request->ajax()) {
            $data = $this->program_realization_repo->getDataTable($cond);
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function ($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->editColumn('budget_realization', function ($data) {
                    $ba = number_format($data->budget_realization, 2);
                    return "<div class='text text-right'>{$ba}</div>";
                })
                ->editColumn('program_budget_allocation', function ($data) {
                    $ba = number_format($data->program_budget_allocation, 2);
                    return "<div class='text text-right'>{$ba}</div>";
                })
                // ->editColumn('status', function($data) {
                //     $color = 'info';
                //     switch ($data->status) {
                //         case 'ACTIVE':
                //             $color = 'success';
                //             break;
                //         case 'DROPPED':
                //             $color = 'danger';
                //             break;

                //         default:
                //             # code...
                //             break;
                //     }
                //     return '<span class="badge light badge-'.$color.'">'.$data->status.'</span>';
                // })
                ->addIndexColumn()
                ->addColumn('percentage', function ($data) {
                    $percentage = $this->compile_repo->customDivide($data->budget_realization, $data->program_budget_allocation);
                    return "<div class='text text-right'>" . number_format($percentage * 100, 2) . "</div>";
                })
                ->addColumn('action', function ($data) {
                    // $btn = '<div class="btn-group">
                    //     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('program.realization.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="' . route($this->route_prefix . 'attachment.list', $data->id) . '" class="dropdown-item">Lampiran Kegiatan</a>
                            <a href="' . route($this->route_prefix . 'bnba.list', $data->id) . '" class="dropdown-item">BNBA</a>
                            <a href="' . route($this->route_prefix . 'edit', $data->id) . '" class="dropdown-item">Ubah</a>
                            <a data-id="delete-' . $data->id . '" data-url="' . route('program.realization.delete') . '" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['status', 'action', 'program_budget_allocation', 'budget_realization', 'budget_allocation', 'percentage'])
                ->make(true);
        }
    }

    public function get_form()
    {
        $fields = [];

        $fields['quarterly'] = [
            'label' => 'Triwulan',
            'name' => 'quarterly',
            'placeholder' => 'Triwulan',
            'type' => 'select',
            'options' => ['1', '2', '3', '4'],
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Triwulan wajib diisi'
        ];

        $fields['program_id'] = [
            'label' => 'Nama Kegiatan',
            'name' => 'program_id',
            'placeholder' => 'Nama Kegiatan',
            'type' => 'data',
            'data_table' => 'tr_program',
            'data_condition' => ' and tr_program.organization_id = "' . Auth::user()->organization_id . '" and tr_program.status = "ACTIVE"',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Nama Kegiatan wajib diisi'
        ];

        $fields['budget_allocation'] = [
            'label' => 'Pagu',
            'name' => 'budget_allocation',
            'placeholder' => 'Pagu',
            'type' => 'decimal',
            'required' => false,
            'show_only' => true,
            'read_only' => true,
            'validate_message' => 'Pagu wajib diisi'
        ];

        $fields['budget_realization'] = [
            'label' => 'Realisasi',
            'name' => 'budget_realization',
            'placeholder' => 'Realisasi',
            'type' => 'decimal',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Realisasi wajib diisi'
        ];

        $fields['target'] = [
            'label' => 'Sasaran Penerima Manfaat',
            'name' => 'target',
            'placeholder' => 'Sasaran Penerima Manfaat',
            'type' => 'text-area',
            'required' => true,
            'show_only' => false,
            'maxlength' => 1000,
            'validate_message' => 'Sasaran Penerima Manfaat wajib diisi'
        ];

        $fields['implementation_obstacle'] = [
            'label' => 'Kendala Pelaksanaan',
            'name' => 'implementation_obstacle',
            'placeholder' => 'Kendala Pelaksanaan',
            'type' => 'text-area',
            'required' => true,
            'show_only' => false,
            'maxlength' => 1000,
            'validate_message' => 'Kendala Pelaksanaan wajib diisi'
        ];

        $fields['benefit'] = [
            'label' => 'Besaran Manfaat',
            'name' => 'benefit',
            'placeholder' => 'Besaran Manfaat',
            'type' => 'text-area',
            'required' => true,
            'show_only' => false,
            'maxlength' => 1000,
            'validate_message' => 'Besaran Manfaat wajib diisi'
        ];

        $fields['duration_note'] = [
            'label' => 'Durasi Pemberian Bantuan',
            'name' => 'duration_note',
            'placeholder' => 'Durasi Pemberian Bantuan',
            'type' => 'text-area',
            'required' => true,
            'show_only' => false,
            'maxlength' => 1000,
            'validate_message' => 'Durasi Pemberian Bantuan wajib diisi'
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

    public function insert()
    {
        $data = [];
        $forms = $this->get_form();

        $user = Auth::user();
        $organization_id = $user->organization_id;
        $organization_parent_id = $user->organization_parent_id;
        $cond = " and tr_program.status = 'ACTIVE'";
        $cond .= " and tr_program.budget_year_id = '{$user->budget_year_id}'";
        if ($organization_parent_id != 0) {
            $cond .= " and tr_program.organization_id = '{$organization_parent_id}'";
            $cond .= " and tr_program.created_by_id = '{$user->id}'";
        } else {
            $cond .= " and tr_program.organization_id = '{$organization_id}'";
        }

        $forms['program_id']['data_condition'] = $cond;

        $data = [
            'fields' => $forms,
            'datas' => [],
            '_be_page_title' => 'Tambah Realisasi Program',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Realisasi Program baru',
            '_be_breadcrumbs' => ['Program', 'Tambah Realisasi Program'],
            '_be_card_title' => 'Tambah Program',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('program.realization.store'),
            '_be_home' => route('program.realization.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.realization.form', compact('data'));
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

        $record = $this->program_repo->getRecord($request->program_id);

        $data['program_id'] = $request->program_id;
        $data['program_goal_id'] = $record->program_goal_id;
        $data['quarterly'] = $request->quarterly;
        $data['budget_allocation'] = $record->budget_allocation;
        $data['budget_realization'] = $request->budget_realization;
        $data['target'] = $request->target ?? '';
        $data['implementation_obstacle'] = $request->implementation_obstacle ?? '';
        $data['benefit'] = $request->benefit ?? '';
        $data['duration_note'] = $request->duration_note ?? '';

        DB::beginTransaction();
        try {
            $program_realization = $this->program_realization_repo->getRecords("tr_program_realization.program_id = '{$data['program_id']}' and tr_program_realization.quarterly = '{$data['quarterly']}' and tr_program_realization.deleted_at IS NULL");
            if ($program_realization->count() > 0) {
                throw new \Exception("Realisasi Program untuk Triwulan {$data['quarterly']} Sudah Dilakukan");
            }

            $this->program_realization_repo->insertRecord($data);
            $program = $this->program_repo->getRecord($data['program_id']);
            // update realization
            // $total_realization = $this->program_realization_repo->getTotalRealization($data['program_id']);
            $total_realization = $this->program_realization_repo->getTotalRealizationByProgramId($data['program_id']);
            $this->program_repo->updateRecord($data['program_id'], ['budget_realization' => $total_realization]);

            if ($program->budget_allocation < $total_realization || $program->budget_allocation < $data['budget_realization']) {
                throw new \Exception("Realisasi Melebihi Pagu / Anggaran");
            }

            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Realisasi Program', 201);
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
        $record = $this->program_realization_repo->getRecord($id);
        if ($record && $record->program_status == 'ACTIVE') {
            $datas = $record->toArray();
        } else {
            return redirect(route($this->route_prefix . 'list'))->with('error', 'Tidak dapat menemukan ID');
        }
        $forms = $this->get_form();
        $datas['budget_allocation'] = $record->program_budget_allocation;

        $data = [
            'fields' => $forms,
            'datas' => $datas,
            '_be_page_title' => 'Ubah Program',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit Program',
            '_be_breadcrumbs' => ['Program', 'Ubah Program'],
            '_be_card_title' => 'Ubah Program',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('program.realization.update', $id),
            '_be_home' => route('program.realization.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.realization.form', compact('data'));
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

        $record = $this->program_realization_repo->getRecord($id);
        $data_before = $this->program_repo->getRecord($record->program_id);
        if ($data_before != null) {
            $data_before = $data_before->toArray();
        }

        $program = $this->program_repo->getRecord($request->program_id);

        $data['program_id'] = $request->program_id;
        $data['program_goal_id'] = $program->program_goal_id;
        $data['quarterly'] = $request->quarterly;
        $data['budget_allocation'] = $program->budget_allocation;
        $data['budget_realization'] = $request->budget_realization;
        $data['target'] = $request->target ?? '';
        $data['implementation_obstacle'] = $request->implementation_obstacle ?? '';
        $data['benefit'] = $request->benefit ?? '';
        $data['duration_note'] = $request->duration_note ?? '';

        DB::beginTransaction();
        try {
            $program_realization = $this->program_realization_repo->getRecords("tr_program_realization.program_id = '{$data['program_id']}' and tr_program_realization.quarterly = '{$data['quarterly']}' and tr_program_realization.id <> '{$id}' and tr_program_realization.deleted_at IS NULL");
            if ($program_realization->count() > 0) {
                throw new \Exception("Realisasi Program untuk Triwulan {$data['quarterly']} Sudah Dilakukan");
            }

            $this->program_realization_repo->updateRecord($id, $data);
            $program = $this->program_repo->getRecord($data['program_id']);

            if ($data_before != null && $record->program_id != $data['program_id']) {
                // update realization
                // $total_realization = $this->program_realization_repo->getTotalRealization($data_before['id']);
                $total_realization = $this->program_realization_repo->getTotalRealizationByProgramId($data_before['id']);
                $this->program_repo->updateRecord($data_before['id'], ['budget_realization' => $total_realization]);
            }

            // update realization
            // $total_realization = $this->program_realization_repo->getTotalRealization($data['program_id']);
            $total_realization = $this->program_realization_repo->getTotalRealizationByProgramId($data['program_id']);
            $this->program_repo->updateRecord($data['program_id'], ['budget_realization' => $total_realization]);

            if ($program->budget_allocation < $total_realization || $program->budget_allocation < $data['budget_realization']) {
                throw new \Exception("Realisasi Melebihi Pagu / Anggaran");
            }

            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah Realisasi Program');
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
        $record = $this->program_realization_repo->getRecord($id);
        if ($id != 0 && $record->program_status == 'ACTIVE') {
            try {
                DB::beginTransaction();
                $this->program_realization_repo->deleteRecord($id);
                $total_realization = $this->program_realization_repo->getTotalRealization($record->program_id);
                $program = $this->program_repo->getRecord($record->program_id);
                if ($program) {
                    $this->program_repo->updateRecord($record->program_id, ['budget_realization' => $total_realization]);
                }
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Realisasi Program');
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

    public function attachment_list($id)
    {
        $data = [
            '_be_page_title' => 'Daftar Lampiran',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Lampiran',
            '_be_breadcrumbs' => ['Program', 'Daftar Lampiran'],
            '_be_insert' => route('program.realization.insert', ['id' => $id]),
            '_parent_id' => $id
        ];
        return view('backpage.program.attachment.list', compact('data'));
    }

    public function attachment_get_data(Request $request)
    {
        $id = $request->id ?? 0;

        if ($request->ajax()) {
            $data = Sy_attachment::leftJoin('mt_user as updated_by', 'sy_attachment.updated_by_id', 'updated_by.id')
                ->where('sy_attachment.reference_name', 'tr_program_realization')
                ->where('sy_attachment.reference_id', $id)
                // ->whereNull('sy_attachment.deleted_at')
                ->select(
                    'sy_attachment.*',
                    'updated_by.name as updated_by_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function ($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->editColumn('size', function ($data) {
                    $ba = number_format($data->size / 1000, 0);
                    return "<div class='text text-right'>{$ba} kB</div>";
                })
                ->addIndexColumn()
                ->addColumn('action', function ($data) use ($id) {
                    $btn = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                        <a data-id="download-' . $data->id . '" data-url="' . route('program.realization.attachment.download', ['id' => $id, 'attachment_id' => $data->id]) . '" class="download dropdown-item">Unduh</a>
                        <a data-id="delete-' . $data->id . '" data-url="' . route('program.realization.attachment.delete', ['id' => $id]) . '" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['action', 'size'])
                ->make(true);
        }
    }

    public function attachment_get_form()
    {
        $fields = [];

        $fields['file'] = [
            'label' => 'Lampiran (.doc, .docx, .xls, .xlsx, .pdf)',
            'name' => 'file',
            'placeholder' => 'Lampiran (.doc, .docx, .xls, .xlsx, .pdf)',
            'type' => 'file',
            'required' => true,
            'show_only' => false,
            'multiple' => true,
            'validate_message' => 'Lampiran wajib diisi'
        ];

        return $fields;
    }

    public function attachment_insert($id)
    {
        $record = $this->program_realization_repo->getRecord($id);
        if ($record->program_status != 'ACTIVE') {
            return redirect()->back();
        }
        $data = [];

        $data = [
            'fields' => $this->attachment_get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah Lampiran',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan Lampiran baru',
            '_be_breadcrumbs' => ['Program', 'Tambah Lampiran'],
            '_be_card_title' => 'Tambah Lampiran',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('program.realization.attachment.store', ['id' => $id]),
            '_be_home' => route('program.realization.attachment.list', ['id' => $id])
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.attachment.form', compact('data'));
    }

    public function attachment_store(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'file.*' => 'required|file|max:512|mimes:doc,xls,docx,xlsx,pdf',
        ], [
            'file.*.required' => 'Mohon unggah terlebih dahulu',
            'file.*.mimes' => 'Hanya berkas dengan ekstensi berikut yang diizinkan (doc,xls,docx,xlsx,pdf)',
            'file.*.max' => 'Ukuran maksimal file yang diunggah 512KB',
        ]);
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        DB::beginTransaction();
        try {
            $uploads = $request->file('file') ?? [];
            if (count($uploads) > 0) {
                foreach ($uploads as $upload) {
                    $fileNameOriginal = $upload->getClientOriginalName();
                    $fileName = Uuid::uuid4() . '.' . $upload->extension();
                    $path = 'public/attachment/' . $fileName;
                    $data = [
                        'reference_name' => 'tr_program_realization',
                        'reference_id' => $id,
                        'file_name' => $fileName,
                        'file_name_original' => $fileNameOriginal,
                        'extension' => $upload->extension(),
                        'size' => $upload->getSize(),
                        'path' => "storage/attachment/" . $fileName,
                    ];
                    Storage::disk('local')->put($path, file_get_contents($upload));
                    $this->attachment_repo->insertRecord($data);
                }
            }
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Program Realisasi Lampiran', 201);
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

    public function attachment_destroy(Request $request, $id)
    {
        $attachment_id = $request->id ?? 0;
        $record = $this->program_realization_repo->getRecord($id);
        $attachment = $this->attachment_repo->getRecord($attachment_id);
        if ($attachment_id != 0 && $record->program_status == 'ACTIVE') {
            try {
                DB::beginTransaction();
                Storage::disk('local')->delete('public/attachment/' . $attachment->file_name);
                $this->attachment_repo->deleteRecord($attachment_id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Program Realisasi Lampiran');
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
                'message' => 'Tidak ada ID yang dikonfirmasi atau status dokumen tidak sama dengan DRAFT'
            ];
        }
        return response()->json($result);
    }

    public function attachment_download($id, $attachment_id)
    {
        $record = $this->program_realization_repo->getRecord($id);
        $attachment = $this->attachment_repo->getRecord($attachment_id);
        if ($record && $attachment) {
            $filePath = public_path($attachment->path);
            return response()->download($filePath, $attachment->file_name_original);
        }
        return null;
    }

    public function bnba_list($id)
    {
        $data = [
            '_be_page_title' => 'Daftar BNBA',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar BNBA',
            '_be_breadcrumbs' => ['Program', 'Daftar BNBA'],
            '_be_insert' => route('program.realization.insert', ['id' => $id]),
            '_parent_id' => $id
        ];
        return view('backpage.program.bnba.list', compact('data'));
    }

    public function bnba_get_data(Request $request, $id)
    {
        if ($request->ajax()) {
            $data = Tr_program_realization_bnba::leftJoin('mt_user as updated_by', 'tr_program_realization_bnba.updated_by_id', 'updated_by.id')
                ->leftJoin('tr_program_realization', 'tr_program_realization_bnba.program_realization_id', 'tr_program_realization.id')
                ->leftJoin('tr_program', 'tr_program_realization_bnba.program_id', 'tr_program.id')
                ->leftJoin('sy_option as bnba_type', 'tr_program_realization_bnba.bnba_type_id', 'bnba_type.id')
                ->where('tr_program_realization_bnba.program_realization_id', $id)
                ->whereNull('tr_program_realization_bnba.deleted_at')
                ->select(
                    'tr_program_realization_bnba.*',
                    'bnba_type.value as bnba_type_name',
                    'updated_by.name as updated_by_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function ($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                ->addIndexColumn()
                ->addColumn('action', function ($data) use ($id) {
                    $btn = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                        <a data-id="delete-' . $data->id . '" data-url="' . route('program.realization.bnba.delete', ['id' => $id]) . '" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function bnba_get_form()
    {
        $fields = [];

        $fields['nik'] = [
            'label' => 'NIK',
            'name' => 'nik',
            'placeholder' => 'NIK',
            'type' => 'text',
            'maxlength' => 20,
            'required' => true,
            'show_only' => false,
            'validate_message' => 'NIK wajib diisi'
        ];

        $fields['bnba_type_id'] = [
            'label' => 'Jenis BNBA',
            'name' => 'bnba_type_id',
            'placeholder' => 'Jenis BNBA',
            'type' => 'data',
            'data_table' => 'sy_option',
            'data_condition' => ' and code = "bnba_type"',
            'data_extra' => '',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Jenis BNBA wajib diisi'
        ];

        return $fields;
    }

    public function bnba_insert($id)
    {
        $record = $this->program_realization_repo->getRecord($id);
        if ($record->program_status != 'ACTIVE') {
            return redirect()->back();
        }
        $data = [];

        $data = [
            'fields' => $this->bnba_get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah BNBA',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan BNBA baru',
            '_be_breadcrumbs' => ['Program', 'Tambah BNBA'],
            '_be_card_title' => 'Tambah BNBA',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('program.realization.bnba.store', ['id' => $id]),
            '_be_home' => route('program.realization.bnba.list', ['id' => $id])
        ];

        $this->compile_repo->make($data);

        return view('backpage.program.bnba.form', compact('data'));
    }

    public function bnba_store(Request $request, $id)
    {
        $validated = Validator::make($request->all(), $this->compile_repo->validateRule($this->bnba_get_form()));
        if ($validated->fails()) {
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data = [];
        $data['program_realization_id'] = $id;
        $data['bnba_type_id'] = $request->bnba_type_id;
        $data['nik'] = $request->nik;
        $data['program_id'] = 0;

        $program = $this->program_realization_repo->getRecord($data['program_realization_id']);
        if ($program) {
            $data['program_id'] = $program->program_id;
        }

        DB::beginTransaction();
        try {
            $this->program_realization_repo->insertRecordBnba($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan Program Realisasi BNBA', 201);
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

    public function bnba_destroy(Request $request, $id)
    {
        $bnba_id = $request->id ?? 0;
        if ($bnba_id != 0) {
            try {
                DB::beginTransaction();
                $this->program_realization_repo->deleteRecordBnba($bnba_id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus Program Realisasi BNBA');
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
                'message' => 'Tidak ada ID yang dikonfirmasi atau status dokumen tidak sama dengan DRAFT'
            ];
        }
        return response()->json($result);
    }
}
