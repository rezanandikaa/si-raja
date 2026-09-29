<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mt_destitution_nik;
use App\Models\Transaction\Tr_program_realization_bnba;
use App\Repositories\CompileRepository;
use App\Repositories\Master\DestitutionNikRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DestitutionNikController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $destitution_nik_repo;

    public function __construct(
        CompileRepository $compile_repo,
        DestitutionNikRepository $destitution_nik_repo
    )
    {
        $this->route_prefix = 'master.destitution_nik.';
        $this->compile_repo = $compile_repo;
        $this->destitution_nik_repo = $destitution_nik_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar P3KE Individu',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar P3KE Individu',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Individu','Daftar P3KE Individu'],
            '_be_insert' => '#'
        ];
        return view('backpage.master_destitution_nik.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Mt_destitution_nik::leftJoin('mt_user as updated_by','mt_destitution_nik.updated_by_id','updated_by.id')
                ->leftJoin('mt_region as province', 'mt_destitution_nik.province_id', 'province.id')
                ->leftJoin('mt_region as regency', 'mt_destitution_nik.regency_id', 'regency.id')
                ->leftJoin('mt_region as district', 'mt_destitution_nik.district_id', 'district.id')
                ->leftJoin('mt_region as subdistrict', 'mt_destitution_nik.subdistrict_id', 'subdistrict.id')
                ->leftJoin('sy_data', 'mt_destitution_nik.data_id', 'sy_data.id')
                ->when($request->kemdagri_code != null && $request->kemdagri_code != '', function($q) use ($request) {
                    $q->where('mt_destitution_nik.kemdagri_code', 'LIKE', "{$request->kemdagri_code}%");
                })
                ->when($request->condition != null && $request->condition != '', function($q) use ($request) {
                    $q->whereRaw($request->condition);
                })
                ->when(get_preference('default_percentile', 2) > 0, function ($q){
                    $q->where('mt_destitution_nik.percentile', '<=', get_preference('default_percentile', 2));
                })
                ->whereNull('mt_destitution_nik.deleted_at')
                ->where('mt_destitution_nik.data_id', source_data_active())
                ->select(
                    'mt_destitution_nik.id',
                    'mt_destitution_nik.p3ke',
                    'mt_destitution_nik.last_update_year',
                    'mt_destitution_nik.nik',
                    'mt_destitution_nik.name',
                    'mt_destitution_nik.decile',
                    'mt_destitution_nik.percentile',
                    'mt_destitution_nik.p3ke',
                    DB::raw("IFNULL(district.name,'-') as district_name"),
                    DB::raw("IFNULL(subdistrict.name,'-') as subdistrict_name"),
                    DB::raw("IFNULL(subdistrict.code,'-') as subdistrict_code"),
                    DB::raw("IFNULL(sy_data.name,'-') as data_name"),
                    'updated_by.name as updated_by_name'
                );
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
                    //     <a href="'.route($this->route_prefix.'detail',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.destitution_nik.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';

                    $btn = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'detail',$data->id).'" class="dropdown-item">Lihat</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    // Kolom NOT NULL di mt_destitution_nik yang belum punya field di form ini.
    // Harus diisi 0 supaya insert tidak gagal.
    // Kolom yang sudah punya field jangan didaftarkan di sini: loop ini jalan paling
    // akhir di payload() sehingga akan menimpa nilai dari form jadi 0.
    const NOT_NULL_DEFAULTS = [
        'priority_verval_id', 'padan_dukcapil_id', 'relationship_id',
        'marital_status_id', 'job_id', 'job_status_id', 'education_id', 'age_group_id',
    ];

    private function field($label, $name, $type, $required = false, $extra = [])
    {
        return array_merge([
            'label' => $label,
            'name' => $name,
            'placeholder' => $label,
            'type' => $type,
            'required' => $required,
            'show_only' => false,
            'validate_message' => $label . ' wajib diisi',
        ], $extra);
    }

    private function regionField($label, $name, $type, $required = true)
    {
        return $this->field($label, $name, 'data', $required, [
            'data_table' => 'mt_region',
            'data_condition' => ' and mt_region.type = "' . $type . '"',
            'data_extra' => '',
        ]);
    }

    // Fieldnya mengikuti kolom datatable di halaman daftar P3KE Individu.
    public function get_form()
    {
        $fields = [];

        $fields['p3ke'] = $this->field('ID P3KE', 'p3ke', 'text', true, ['maxlength' => 100]);
        $fields['last_update_year'] = $this->field('Tahun Update', 'last_update_year', 'number', true);
        $fields['nik'] = $this->field('NIK', 'nik', 'text', true, ['maxlength' => 20]);
        $fields['name'] = $this->field('Nama', 'name', 'text', true, ['maxlength' => 100]);

        // Kabupaten/Kota & Kecamatan dipakai untuk menjaring pilihan, lihat form.blade.php.
        $fields['province_id'] = $this->regionField('Provinsi', 'province_id', '1-PROVINSI');
        $fields['regency_id'] = $this->regionField('Kabupaten/Kota', 'regency_id', '2-KABUPATEN/KOTA');
        $fields['district_id'] = $this->regionField('Kecamatan', 'district_id', '3-KECAMATAN');
        $fields['subdistrict_id'] = $this->regionField('Desa/Kelurahan', 'subdistrict_id', '4-DESA-KELURAHAN');

        $fields['decile'] = $this->field('Desil Kesejahteraan', 'decile', 'number', true);
        $fields['percentile'] = $this->field('Persentil', 'percentile', 'number');

        return $fields;
    }

    // Rule tambahan untuk field yang tidak tercakup CompileRepository::validateRule.
    private function extra_rules()
    {
        return [
            'last_update_year' => 'required|integer|digits:4',
            'decile' => 'required|integer|min:1',
            'percentile' => 'nullable|integer|min:1',
        ];
    }

    // Susun baris mt_destitution_nik dari input form.
    private function payload(Request $request)
    {
        $data = [
            // data_id harus sama dengan sumber data aktif, kalau tidak barisnya
            // tidak akan muncul di datatable (get_data filter kolom ini).
            'data_id' => source_data_active(),
            'p3ke' => $request->p3ke,
            'last_update_year' => (int) $request->last_update_year,
            'nik' => $request->nik,
            'name' => $request->name,
            'province_id' => (int) $request->province_id,
            'regency_id' => (int) $request->regency_id,
            'district_id' => (int) $request->district_id,
            'subdistrict_id' => (int) $request->subdistrict_id,
            'decile' => (int) $request->decile,
            'percentile' => (int) ($request->percentile ?? 0),
        ];

        foreach (self::NOT_NULL_DEFAULTS as $column) {
            $data[$column] = 0;
        }

        return $data;
    }

    public function insert()
    {
        $data = [];

        $data = [
            'fields' => $this->get_form(),
            'datas' => [],
            '_be_page_title' => 'Tambah P3KE Individu',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan P3KE Individu baru',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Individu','Tambah P3KE Individu'],
            '_be_card_title' => 'Tambah P3KE Individu',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.destitution_nik.store'),
            '_be_home' => route('master.destitution_nik.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_destitution_nik.form',compact('data'));
    }

    public function store(Request $request)
    {
        $rules = array_merge(
            $this->compile_repo->validateRule($this->get_form()),
            $this->extra_rules()
        );
        $validated = Validator::make($request->all(), $rules);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $data = $this->payload($request);

        try {
            DB::beginTransaction();
            $this->destitution_nik_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan P3KE Individu', 201);
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

        $datas = $this->destitution_nik_repo->getRecord($id)->toArray();
        $this->getAccessModule($datas);

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah P3KE Individu',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit P3KE Individu',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Individu','Ubah P3KE Individu'],
            '_be_card_title' => 'Ubah P3KE Individu',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.destitution_nik.update',$id),
            '_be_home' => route('master.destitution_nik.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_destitution_nik.form',compact('data'));
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'user_access_name' => 'required|max:100|unique:mt_user_access,user_access_name,'.$id,
            'user_access_desc' => 'required|max:300'
        ]);
        if($validated->fails()){
            $result = [
                'status' => 'FAIL',
                'message' => $validated->getMessageBag()->first()
            ];
            return response()->json($result);
        }

        $access_module = [];
        $this->generateAccessModule($access_module, $request);

        $data['user_access_name'] = $request->user_access_name;
        $data['user_access_desc'] = $request->user_access_desc;
        $data['access_module'] = json_encode($access_module);

        try {
            DB::beginTransaction();
            $this->destitution_nik_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah P3KE Individu Pengguna');
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
                $this->destitution_nik_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus P3KE Individu Pengguna');
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

    public function detail($id)
    {
        $record = $this->destitution_nik_repo->getRecord($id);
        if ($record == null) {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $data = [
            'datas' => $record->toArray(),
            '_be_page_title' => 'Detail P3KE Individu',
            '_be_page_title_desc' => 'Halaman ini adalah Detail P3KE Individu',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Individu','Detail P3KE Individu'],
            '_be_card_title' => 'Detail P3KE Individu',
            '_be_home' => route('master.destitution_nik.list'),
            '_parent_id' => $id,
        ];

        return view('backpage.master_destitution_nik.detail',compact('data'));
    }

    public function bnba_get_data(Request $request, $id)
    {
        $type = 'INDIVIDU';
        if ($request->ajax()) {
            $data = Tr_program_realization_bnba::leftJoin('mt_user as updated_by','tr_program_realization_bnba.updated_by_id','updated_by.id')
                ->leftJoin('tr_program_realization', 'tr_program_realization_bnba.program_realization_id', 'tr_program_realization.id')
                ->leftJoin('mt_destitution_nik', 'tr_program_realization_bnba.nik', 'mt_destitution_nik.nik')
                ->leftJoin('tr_program', 'tr_program_realization.program_id', 'tr_program.id')
                ->leftJoin('sy_option as bnba_type', 'tr_program_realization_bnba.bnba_type_id', 'bnba_type.id')
                ->leftJoin('mt_organization', 'tr_program.organization_id', 'mt_organization.id')
                ->leftJoin('mt_budget_year', 'tr_program.budget_year_id', 'mt_budget_year.id')
                ->whereNull('tr_program_realization_bnba.deleted_at')
                ->where('mt_destitution_nik.id', $id)
                ->where('bnba_type.value', $type)
                ->select(
                    'tr_program_realization_bnba.*',
                    'tr_program.code as program_code',
                    'tr_program.program as program_program',
                    'tr_program.activity as program_activity',
                    'tr_program.sub_activity as program_sub_activity',
                    'mt_budget_year.name as budget_year_name',
                    'mt_organization.name as organization_name',
                    'updated_by.name as updated_by_name'
                );
            return DataTables::eloquent($data)
                ->editColumn('updated_at', function($data) {
                    return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
                })
                // ->editColumn('active_flag', function($data) {
                //     return '<span class="badge light badge-'.($data->active_flag ? 'success':'danger').'">'.($data->active_flag ? 'Aktif':'Nonaktif').'</span>';
                // })
                ->addIndexColumn()
                ->rawColumns([])
                ->make(true);
        }
    }
}
