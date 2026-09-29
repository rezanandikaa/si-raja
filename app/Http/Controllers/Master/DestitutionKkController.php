<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mt_destitution_kk;
use App\Models\System\Sy_option;
use App\Models\Transaction\Tr_program_realization_bnba;
use App\Repositories\CompileRepository;
use App\Repositories\Master\DestitutionKkRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DestitutionKkController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $destitution_kk_repo;

    public function __construct(
        CompileRepository $compile_repo,
        DestitutionKkRepository $destitution_kk_repo
    )
    {
        $this->route_prefix = 'master.destitution_kk.';
        $this->compile_repo = $compile_repo;
        $this->destitution_kk_repo = $destitution_kk_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar P3KE Kepala Keluarga',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar P3KE Kepala Keluarga',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Kepala Keluarga','Daftar P3KE Kepala Keluarga'],
            '_be_insert' => route('master.destitution_kk.insert')
        ];
        return view('backpage.master_destitution_kk.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Mt_destitution_kk::leftJoin('mt_user as updated_by','mt_destitution_kk.updated_by_id','updated_by.id')
                ->leftJoin('mt_region as province', 'mt_destitution_kk.province_id', 'province.id')
                ->leftJoin('mt_region as regency', 'mt_destitution_kk.regency_id', 'regency.id')
                ->leftJoin('mt_region as district', 'mt_destitution_kk.district_id', 'district.id')
                ->leftJoin('mt_region as subdistrict', 'mt_destitution_kk.subdistrict_id', 'subdistrict.id')
                ->leftJoin('sy_data', 'mt_destitution_kk.data_id', 'sy_data.id')
                ->whereNull('mt_destitution_kk.deleted_at')
                ->where('mt_destitution_kk.data_id', source_data_active())
                ->when($request->kemdagri_code != null && $request->kemdagri_code != '', function($q) use ($request) {
                    $q->where('mt_destitution_kk.kemdagri_code', 'LIKE', "{$request->kemdagri_code}%");
                })
                ->when($request->condition != null && $request->condition != '', function($q) use ($request) {
                    $q->whereRaw($request->condition);
                })
                ->when(get_preference('default_percentile', 2) > 0, function ($q){
                    $q->where('mt_destitution_kk.percentile', '<=', get_preference('default_percentile', 2));
                })
                ->select(
                    'mt_destitution_kk.*',
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
                    //     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
                    //     <a data-id="delete-'.$data->id.'" data-url="'.route('master.destitution_kk.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
                    // ';
                    $btn = '
                    <div class="btn-group" role="group">
                        <button id="btnGroupDrop1" type="button" class="btn btn-sm round btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Opsi
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" style="">
                            <a href="'.route($this->route_prefix.'detail',$data->id).'" class="dropdown-item">Lihat</a>
                            <a data-id="delete-'.$data->id.'" data-url="'.route('master.destitution_kk.delete').'" class="delete dropdown-item">Hapus</a>
                        </div>
                    </div>
                    ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    // Kolom NOT NULL di mt_destitution_kk yang belum punya field di form ini.
    // Harus diisi 0 supaya insert tidak gagal.
    // Kolom yang sudah punya field jangan didaftarkan di sini: loop ini jalan paling
    // akhir di payload() sehingga akan menimpa nilai dari form jadi 0.
    const NOT_NULL_DEFAULTS = [
        'priority_verval_id', 'padan_dukcapil_id',
        'home_electricity_id', 'home_cooking_id',
    ];

    const BANTUAN_FLAGS = ['is_pkh', 'is_bpnt', 'is_bst', 'is_bpum', 'is_kur', 'is_prakerja', 'is_sembako'];

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

    private function optionField($label, $name, $code, $required = false)
    {
        return $this->field($label, $name, 'data', $required, [
            'data_table' => 'sy_option',
            'data_condition' => ' and sy_option.code = "' . $code . '"',
            'data_extra' => '',
        ]);
    }

    public function get_form()
    {
        $fields = [];

        // Identitas
        $fields['p3ke'] = $this->field('No. KK', 'p3ke', 'text', true, ['maxlength' => 100]);
        $fields['last_update_year'] = $this->field('Tahun Update', 'last_update_year', 'number', true);
        $fields['nik'] = $this->field('NIK', 'nik', 'text', true, ['maxlength' => 20]);
        $fields['name'] = $this->field('Nama', 'name', 'text', true, ['maxlength' => 100]);

        // Wilayah
        $fields['province_id'] = $this->regionField('Provinsi', 'province_id', '1-PROVINSI');
        $fields['regency_id'] = $this->regionField('Kabupaten/Kota', 'regency_id', '2-KABUPATEN/KOTA');
        $fields['district_id'] = $this->regionField('Kecamatan', 'district_id', '3-KECAMATAN');
        $fields['subdistrict_id'] = $this->regionField('Desa/Kelurahan', 'subdistrict_id', '4-DESA-KELURAHAN');
        $fields['address'] = $this->field('Alamat', 'address', 'text-area', false, ['maxlength' => 500]);

        // Kesejahteraan
        $fields['decile'] = $this->field('Desil Kesejahteraan', 'decile', 'number', true);
        $fields['percentile'] = $this->field('Persentil', 'percentile', 'number');

        // Demografi
        $fields['gender_id'] = $this->optionField('Jenis Kelamin', 'gender_id', 'gender', true);
        $fields['birth_date'] = $this->field('Tanggal Lahir', 'birth_date', 'date');
        $fields['job_id'] = $this->optionField('Pekerjaan', 'job_id', 'job');
        $fields['job_status_id'] = $this->optionField('Status Pekerjaan', 'job_status_id', 'job_status');
        $fields['education_id'] = $this->optionField('Pendidikan', 'education_id', 'education');
        $fields['marital_status_id'] = $this->optionField('Status Kawin', 'marital_status_id', 'marital_status');
        $fields['home_ownership_id'] = $this->optionField('Status Rumah', 'home_ownership_id', 'home_ownership');
        $fields['etc_ownership_id'] = $this->optionField('Memiliki Simpanan/Uang/Perhiasan/Ternak/Lainnya', 'etc_ownership_id', 'etc_ownership');

        // Kondisi rumah. Jenis dan kualitasnya berpasangan, urutan kolomnya mengikuti
        // halaman detail supaya mudah dicocokkan.
        $fields['home_roof_id'] = $this->optionField('Jenis Atap', 'home_roof_id', 'home_roof');
        $fields['home_roof_quality_id'] = $this->optionField('Kualitas Atap', 'home_roof_quality_id', 'home_roof_quality');
        $fields['home_wall_id'] = $this->optionField('Jenis Dinding', 'home_wall_id', 'home_wall');
        $fields['home_wall_quality_id'] = $this->optionField('Kualitas Dinding', 'home_wall_quality_id', 'home_wall_quality');
        $fields['home_floor_id'] = $this->optionField('Jenis Lantai', 'home_floor_id', 'home_floor');
        $fields['home_floor_quality_id'] = $this->optionField('Kualitas Lantai', 'home_floor_quality_id', 'home_floor_quality');
        $fields['home_electricity_power_id'] = $this->optionField('Daya Listrik Rumah', 'home_electricity_power_id', 'home_electricity_power');
        $fields['home_water_id'] = $this->optionField('Sumber Air Minum', 'home_water_id', 'home_water');
        $fields['home_toilet_ownership_id'] = $this->optionField('Fasilitas Buang Air Besar', 'home_toilet_ownership_id', 'home_toilet_ownership');
        $fields['stunting_risk_id'] = $this->optionField('Resiko Stunting', 'stunting_risk_id', 'stunting_risk');

        // Bantuan
        $fields['is_pkh'] = $this->field('PKH', 'is_pkh', 'checkbox');
        $fields['is_bpnt'] = $this->field('BPNT', 'is_bpnt', 'checkbox');
        $fields['is_bst'] = $this->field('BST', 'is_bst', 'checkbox');
        $fields['is_bpum'] = $this->field('BPUM', 'is_bpum', 'checkbox');
        $fields['is_kur'] = $this->field('KUR', 'is_kur', 'checkbox');
        $fields['is_prakerja'] = $this->field('Prakerja', 'is_prakerja', 'checkbox');
        $fields['is_sembako'] = $this->field('Sembako', 'is_sembako', 'checkbox');

        return $fields;
    }

    // Rule tambahan untuk field yang tidak tercakup CompileRepository::validateRule.
    private function extra_rules()
    {
        return [
            'last_update_year' => 'required|integer|digits:4',
            'decile' => 'required|integer|min:1',
            'percentile' => 'nullable|integer|min:1',
            'birth_date' => 'nullable|date',
        ];
    }

    // Susun baris mt_destitution_kk dari input form.
    private function payload(Request $request)
    {
        $gender_id = (int) ($request->gender_id ?? 0);
        $gender_value = $gender_id ? Sy_option::where('id', $gender_id)->value('value') : null;

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
            'address' => $request->address ?? '',
            'decile' => (int) $request->decile,
            'percentile' => (int) ($request->percentile ?? 0),
            'gender_id' => $gender_id,
            // kolom gender cuma 1 huruf: "LAKI-LAKI" -> "L", "PEREMPUAN" -> "P".
            'gender' => $gender_value ? strtoupper(substr($gender_value, 0, 1)) : null,
            'birth_date' => $request->birth_date ? Carbon::parse($request->birth_date)->format('Y-m-d') : null,
            'job_id' => (int) ($request->job_id ?? 0),
            'job_status_id' => (int) ($request->job_status_id ?? 0),
            'education_id' => (int) ($request->education_id ?? 0),
            'marital_status_id' => (int) ($request->marital_status_id ?? 0),
            'home_ownership_id' => (int) ($request->home_ownership_id ?? 0),
            'etc_ownership_id' => (int) ($request->etc_ownership_id ?? 0),
            'home_roof_id' => (int) ($request->home_roof_id ?? 0),
            'home_roof_quality_id' => (int) ($request->home_roof_quality_id ?? 0),
            'home_wall_id' => (int) ($request->home_wall_id ?? 0),
            'home_wall_quality_id' => (int) ($request->home_wall_quality_id ?? 0),
            'home_floor_id' => (int) ($request->home_floor_id ?? 0),
            'home_floor_quality_id' => (int) ($request->home_floor_quality_id ?? 0),
            'home_electricity_power_id' => (int) ($request->home_electricity_power_id ?? 0),
            'home_water_id' => (int) ($request->home_water_id ?? 0),
            'home_toilet_ownership_id' => (int) ($request->home_toilet_ownership_id ?? 0),
            'stunting_risk_id' => (int) ($request->stunting_risk_id ?? 0),
        ];

        foreach (self::BANTUAN_FLAGS as $flag) {
            $data[$flag] = $request->$flag ? 1 : 0;
        }

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
            '_be_page_title' => 'Tambah P3KE Kepala Keluarga',
            '_be_page_title_desc' => 'Halaman ini untuk menambahkan P3KE Kepala Keluarga baru',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Kepala Keluarga','Tambah P3KE Kepala Keluarga'],
            '_be_card_title' => 'Tambah P3KE Kepala Keluarga',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'POST',
            '_be_action' => route('master.destitution_kk.store'),
            '_be_home' => route('master.destitution_kk.list')
        ];

        $this->compile_repo->make($data);

        // make() selalu mengisi tipe 'date' dengan tanggal hari ini. Untuk tanggal
        // lahir itu salah: kalau petugas tidak membuka kalender, hari ini ikut tersimpan.
        $data['fields']['birth_date']['value'] = '';

        return view('backpage.master_destitution_kk.form',compact('data'));
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
            $this->destitution_kk_repo->insertRecord($data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil menambahkan P3KE Kepala Keluarga', 201);
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

        $datas = $this->destitution_kk_repo->getRecord($id)->toArray();

        $data = [
            'fields' => $this->get_form(),
            'datas' => $datas,
            '_be_page_title' => 'Ubah P3KE Kepala Keluarga',
            '_be_page_title_desc' => 'Halaman ini untuk mengubah / mengedit P3KE Kepala Keluarga',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Kepala Keluarga','Ubah P3KE Kepala Keluarga'],
            '_be_card_title' => 'Ubah P3KE Kepala Keluarga',
            '_be_btn_label' => 'Simpan',
            '_be_btn_variant' => 'primary',
            '_be_method' => 'PUT',
            '_be_action' => route('master.destitution_kk.update',$id),
            '_be_home' => route('master.destitution_kk.list')
        ];

        $this->compile_repo->make($data);

        return view('backpage.master_destitution_kk.form',compact('data'));
    }

    public function update(Request $request, $id)
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
            $this->destitution_kk_repo->updateRecord($id, $data);
            $result = [
                'status' => 'OK',
                'message' => 'Data tersimpan'
            ];
            logbook('Berhasil mengubah P3KE Kepala Keluarga Pengguna');
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
                $this->destitution_kk_repo->deleteRecord($id);
                $result = [
                    'status' => 'OK',
                    'message' => 'Data dihapus'
                ];
                logbook('Berhasil menghapus P3KE Kepala Keluarga Pengguna');
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
        $record = $this->destitution_kk_repo->getRecord($id);
        if ($record == null) {
            return redirect(route($this->route_prefix.'list'))->with('error','Tidak dapat menemukan ID');
        }

        $data = [
            'datas' => $record->toArray(),
            '_be_page_title' => 'Detail P3KE Kepala Keluarga',
            '_be_page_title_desc' => 'Halaman ini adalah Detail P3KE Kepala Keluarga',
            '_be_breadcrumbs' => ['Master Data','Master P3KE Kepala Keluarga','Detail P3KE Kepala Keluarga'],
            '_be_card_title' => 'Detail P3KE Kepala Keluarga',
            '_be_home' => route('master.destitution_kk.list'),
            '_parent_id' => $id,
        ];

        return view('backpage.master_destitution_kk.detail',compact('data'));
    }

    public function bnba_get_data(Request $request, $id)
    {
        $type = 'KEPALA-KELUARGA';
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
