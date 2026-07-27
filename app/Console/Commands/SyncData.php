<?php

namespace App\Console\Commands;

use App\Models\System\Sy_import;
use App\Repositories\Master\DestitutionKkRepository;
use App\Repositories\Master\DestitutionNikRepository;
use App\Repositories\Master\RegionRepository;
use App\Repositories\System\ImportRepository;
use App\Repositories\System\OptionRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'baduyengine:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $destitution_nik_repo;
    protected $destitution_kk_repo;
    protected $import_repo;
    protected $region_repo;
    protected $option_repo;

    public function __construct(DestitutionNikRepository $destitution_nik_repo,
        DestitutionKkRepository $destitution_kk_repo,
        ImportRepository $import_repo,
        RegionRepository $region_repo,
        OptionRepository $option_repo)
    {
        parent::__construct();
        $this->destitution_nik_repo = $destitution_nik_repo;
        $this->destitution_kk_repo = $destitution_kk_repo;
        $this->import_repo = $import_repo;
        $this->region_repo = $region_repo;
        $this->option_repo = $option_repo;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $records = Sy_import::where('sy_import.is_sync', false)
            ->leftJoin('sy_file', 'sy_import.file_id', 'sy_file.id')
            ->select(
                'sy_import.*',
                'sy_file.type as type'
            )
            ->get();

        // Inisialisasi ProgressBar
        $progressBar = $this->output->createProgressBar();
        $progressBar->start();

        $i = 0;
        foreach ($records as $record) {
            $progressBar->setProgress($i);
            if ($record->type == 'INDIVIDU') {
                $destitution = json_decode($record->data, true);

                $kemdagri_code = $destitution['Kode Kemdagri'] ?? null;
                if ($kemdagri_code == null) {
                    Log::info("Error Kode Kemdagri :".json_encode($destitution));
                    continue;
                }

                $province_code = substr($kemdagri_code, 0, 2);
                $province = $this->region_repo->getRecordByColumn('code', $province_code);
                if ($province == null) {
                    Log::info("Error Province Not Found :".json_encode($destitution));
                    continue;
                }

                $regency_code = substr($kemdagri_code, 0, 4);
                $regency = $this->region_repo->getRecordByColumn('code', $regency_code);
                if ($regency == null) {
                    Log::info("Error Regency Not Found :".json_encode($destitution));
                    continue;
                }

                $district_code = substr($kemdagri_code, 0, 6);
                $district = $this->region_repo->getRecordByColumn('code', $district_code);
                if ($district == null) {
                    Log::info("Error District Not Found :".json_encode($destitution));
                    continue;
                }

                $subdistrict_code = $kemdagri_code;
                $subdistrict = $this->region_repo->getRecordByColumn('code', $subdistrict_code);
                if ($subdistrict == null) {
                    Log::info("Error Sub District Not Found :".json_encode($destitution));
                    continue;
                }

                $prority_verval = $destitution["Prioritas Verval"] ?? null;
                if ($prority_verval == null) {
                    Log::info("Error Kode Prioritas Verval :".json_encode($destitution));
                    continue;
                }
                $prority_verval_opt = $this->option_repo->getRecordByColumn('value', $prority_verval, 'verval_priority');
                if ($prority_verval_opt == null) {
                    Log::info("Error Kode Prioritas Verval Not Found :".json_encode($destitution));
                    continue;
                }

                $padan_dukcapil = $destitution["Padan Dukcapil"] ?? null;
                if ($padan_dukcapil == null) {
                    Log::info("Error Padan Dukcapil Verval :".json_encode($destitution));
                    continue;
                }
                $padan_dukcapil_opt = $this->option_repo->getRecordByColumn('value', $padan_dukcapil, 'padan_dukcapil');
                if ($padan_dukcapil_opt == null) {
                    Log::info("Error Padan Dukcapil Verval Not Found :".json_encode($destitution));
                    continue;
                }

                $gender = $destitution["Jenis Kelamin"] ?? null;
                if ($gender == null) {
                    Log::info("Error Jenis Kelamin :".json_encode($destitution));
                    continue;
                }
                $gender_opt = $this->option_repo->getRecordByColumn('value', $gender, 'gender');
                if ($gender_opt == null) {
                    Log::info("Error Jenis Kelamin Not Found :".json_encode($destitution));
                    continue;
                }

                $relationship = $destitution["Hubungan dengan Kepala Keluarga"] ?? null;
                if ($relationship == null) {
                    Log::info("Error Hubungan Dengan Kepala Keluarga :".json_encode($destitution));
                    continue;
                }
                $relationship_opt = $this->option_repo->getRecordByColumn('value', $relationship, 'relationship');
                if ($relationship_opt == null) {
                    Log::info("Error Hubungan Dengan Kepala Keluarga Not Found :".json_encode($destitution));
                    continue;
                }

                $marital_status = $destitution["Status Kawin"] ?? null;
                if ($marital_status == null) {
                    Log::info("Error Status Kawin :".json_encode($destitution));
                    continue;
                }

                $marital_status_opt = $this->option_repo->getRecordByColumn('value', $marital_status, 'marital_status');
                if ($marital_status_opt == null) {
                    Log::info("Error Status Kawin Not Found :".json_encode($destitution));
                    continue;
                }

                $job = $destitution["Pekerjaan"] ?? null;
                if ($job == null) {
                    Log::info("Error Hubungan Dengan Kepala Keluarga :".json_encode($destitution));
                    continue;
                }
                $job_opt = $this->option_repo->getRecordByColumn('value', $job, 'job');
                if ($job_opt == null) {
                    Log::info("Error Hubungan Dengan Kepala Keluarga Not Found :".json_encode($destitution));
                    continue;
                }

                $job = $destitution["Pekerjaan"] ?? null;
                if ($job == null) {
                    Log::info("Error Pekerjaan :".json_encode($destitution));
                    continue;
                }
                $job_opt = $this->option_repo->getRecordByColumn('value', $job, 'job');
                if ($job_opt == null) {
                    Log::info("Error Pekerjaan Not Found :".json_encode($destitution));
                    continue;
                }

                $job_status = $destitution["Status Pekerjaan"] ?? null;
                if ($job_status == null) {
                    Log::info("Error Status Pekerjaan :".json_encode($destitution));
                    continue;
                }
                $job_status_opt = $this->option_repo->getRecordByColumn('value', $job_status, 'job_status');
                if ($job_status_opt == null) {
                    Log::info("Error Status Pekerjaan Not Found :".json_encode($destitution));
                    continue;
                }

                $education = $destitution["Pendidikan"] ?? null;
                if ($education == null) {
                    Log::info("Error Pendidikan :".json_encode($destitution));
                    continue;
                }
                $education_opt = $this->option_repo->getRecordByColumn('value', $education, 'education');
                if ($education_opt == null) {
                    Log::info("Error Pendidikan Not Found :".json_encode($destitution));
                    continue;
                }

                $age_group = $destitution["Kelompok Usia 2023"] ?? null;
                if ($age_group == null) {
                    Log::info("Error Kelompok Usia 2023 :".json_encode($destitution));
                    continue;
                }
                $age_group_opt = $this->option_repo->getRecordByColumn('value', $age_group, 'age_group');
                if ($age_group_opt == null) {
                    Log::info("Error Kelompok Usia 2023 Not Found :".json_encode($destitution));
                    continue;
                }

                $bpnt = $destitution["Penerima BPNT"] ?? null;
                if ($bpnt == null) {
                    Log::info("Error Penerima BPNT :".json_encode($destitution));
                    continue;
                }

                $bpum = $destitution["Penerima BPUM"] ?? null;
                if ($bpum == null) {
                    Log::info("Error Penerima BPUM :".json_encode($destitution));
                    continue;
                }

                $bst = $destitution["Penerima BST"] ?? null;
                if ($bst == null) {
                    Log::info("Error Penerima BST :".json_encode($destitution));
                    continue;
                }

                $pkh = $destitution["Penerima PKH"] ?? null;
                if ($pkh == null) {
                    Log::info("Error Penerima PKH :".json_encode($destitution));
                    continue;
                }

                $sembako = $destitution["Penerima SEMBAKO"] ?? null;
                if ($sembako == null) {
                    Log::info("Error Penerima SEMBAKO :".json_encode($destitution));
                    continue;
                }

                $prakerja = $destitution["Penerima Prakerja"] ?? null;
                if ($prakerja == null) {
                    Log::info("Error Penerima Prakerja :".json_encode($destitution));
                    continue;
                }

                $kur = $destitution["Penerima KUR"] ?? null;
                if ($kur == null) {
                    Log::info("Error Penerima KUR :".json_encode($destitution));
                    continue;
                }

                $data = [
                    'data_id' => source_data_active(),
                    "p3ke" => $destitution["ID Keluarga P3KE"] ?? null,
                    "last_update_year" => isset($destitution["Dimuktakhirkan Tahun"]) ? $destitution["Dimuktakhirkan Tahun"] : 0,
                    "province_id" => $province->id,
                    "regency_id" => $regency->id,
                    "district_id" => $district->id,
                    "subdistrict_id" => $subdistrict->id,
                    "kemdagri_code" => $kemdagri_code,
                    "decile" => $destitution["Desil Kesejahteraan"] ?? 0,
                    "percentile" => $destitution["Persentil"] ?? 0,
                    "address" => $destitution["Alamat"] ?? null,
                    "individual_id" => $destitution["ID Individu"] ?? 0,
                    "priority_verval_id" => $prority_verval_opt->id,
                    "name" => $destitution["Nama"] ?? null,
                    "nik" => $destitution["NIK"] ?? null,
                    "birth_date" => $destitution["Tanggal Lahir"] ?? null,
                    "padan_dukcapil_id" => $padan_dukcapil_opt->id,
                    "gender" => isset($destitution["Jenis Kelamin"]) ? (strtolower($destitution["Jenis Kelamin"]) == 'laki-laki' ? "L" : "P") : null,
                    "gender_id" => $gender_opt->id,
                    "relationship_id" => $relationship_opt->id,
                    "marital_status_id" => $marital_status_opt->id,
                    "job_id" => $job_opt->id,
                    "job_status_id" => $job_status_opt->id,
                    "education_id" => $education_opt->id,
                    "age" => $destitution["Usia 2023"] ?? null,
                    "age_group_id" => $age_group_opt->id,
                    "is_bpnt" => ($destitution["Penerima BPNT"] == 'Ya' ? true : false),
                    "is_bpum" => ($destitution["Penerima BPUM"] == 'Ya' ? true : false),
                    "is_bst" => ($destitution["Penerima BST"] == 'Ya' ? true : false),
                    "is_pkh" => ($destitution["Penerima PKH"] == 'Ya' ? true : false),
                    "is_sembako" => ($destitution["Penerima SEMBAKO"] == 'Ya' ? true : false),
                    "is_prakerja" => ($destitution["Penerima Prakerja"] == 'Ya' ? true : false),
                    "is_kur" => ($destitution["Penerima KUR"] == 'Ya' ? true : false),
                ];

                DB::beginTransaction();
                try {
                    $this->destitution_nik_repo->insertRecord($data);
                    $this->import_repo->updateRecord($record->id, ['is_sync' => true]);
                    DB::commit();
                } catch (\Exception $err) {
                    Log::info("Error {$err->getCode()} : {$err->getMessage()}");
                    DB::rollBack();
                }
            }

            if ($record->type == 'KEPALA-KELUARGA') {
                $destitution = json_decode($record->data, true);

                $kemdagri_code = $destitution['Kode Kemdagri'] ?? null;
                if ($kemdagri_code == null) {
                    Log::info("Error Kode Kemdagri :".json_encode($destitution));
                    continue;
                }

                $province_code = substr($kemdagri_code, 0, 2);
                $province = $this->region_repo->getRecordByColumn('code', $province_code);
                if ($province == null) {
                    Log::info("Error Province Not Found :".json_encode($destitution));
                    continue;
                }

                $regency_code = substr($kemdagri_code, 0, 4);
                $regency = $this->region_repo->getRecordByColumn('code', $regency_code);
                if ($regency == null) {
                    Log::info("Error Regency Not Found :".json_encode($destitution));
                    continue;
                }

                $district_code = substr($kemdagri_code, 0, 6);
                $district = $this->region_repo->getRecordByColumn('code', $district_code);
                if ($district == null) {
                    Log::info("Error District Not Found :".json_encode($destitution));
                    continue;
                }

                $subdistrict_code = $kemdagri_code;
                $subdistrict = $this->region_repo->getRecordByColumn('code', $subdistrict_code);
                if ($subdistrict == null) {
                    Log::info("Error Sub District Not Found :".json_encode($destitution));
                    continue;
                }

                $prority_verval = $destitution["Prioritas Verval"] ?? null;
                if ($prority_verval == null) {
                    Log::info("Error Kode Prioritas Verval :".json_encode($destitution));
                    continue;
                }
                $prority_verval_opt = $this->option_repo->getRecordByColumn('value', $prority_verval, 'verval_priority_family');
                if ($prority_verval_opt == null) {
                    Log::info("Error Kode Prioritas Verval Not Found :".json_encode($destitution));
                    continue;
                }

                $padan_dukcapil = $destitution["NIK Kepala Keluarga Padan Kemdagri"] ?? null;
                if ($padan_dukcapil == null) {
                    Log::info("Error Padan Dukcapil Verval :".json_encode($destitution));
                    continue;
                }
                $padan_dukcapil_opt = $this->option_repo->getRecordByColumn('value', $padan_dukcapil, 'padan_dukcapil');
                if ($padan_dukcapil_opt == null) {
                    Log::info("Error Padan Dukcapil Verval Not Found :".json_encode($destitution));
                    continue;
                }

                $gender = $destitution["Jenis Kelamin Kepala Keluarga"] ?? null;
                if ($gender == null) {
                    Log::info("Error Jenis Kelamin Kepala Keluarga :".json_encode($destitution));
                    continue;
                }
                $gender_opt = $this->option_repo->getRecordByColumn('value', $gender, 'gender');
                if ($gender_opt == null) {
                    Log::info("Error Jenis Kelamin Kepala Keluarga Not Found :".json_encode($destitution));
                    continue;
                }

                $marital_status = $destitution["Status Kawin Kepala Keluarga"] ?? null;
                if ($marital_status == null) {
                    Log::info("Error Status Kawin Kepala Keluarga :".json_encode($destitution));
                    continue;
                }
                $marital_status_opt = $this->option_repo->getRecordByColumn('value', $marital_status, 'marital_status');
                if ($marital_status_opt == null) {
                    Log::info("Error Status Kawin Kepala Keluarga Not Found :".json_encode($destitution));
                    continue;
                }

                $job = $destitution["Pekerjaan Kepala Keluarga"] ?? null;
                if ($job == null) {
                    Log::info("Error Pekerjaan Kepala Keluarga :".json_encode($destitution));
                    continue;
                }
                $job_opt = $this->option_repo->getRecordByColumn('value', $job, 'job');
                if ($job_opt == null) {
                    Log::info("Error Pekerjaan Kepala Keluarga Not Found :".json_encode($destitution));
                    continue;
                }

                $job_status = $destitution["Status Pekerjaan Kepala Keluarga"] ?? null;
                if ($job_status == null) {
                    Log::info("Error Status Pekerjaan Kepala Keluarga :".json_encode($destitution));
                    continue;
                }
                $job_status_opt = $this->option_repo->getRecordByColumn('value', $job_status, 'job_status');
                if ($job_status_opt == null) {
                    Log::info("Error Status Pekerjaan Kepala Keluarga Not Found :".json_encode($destitution));
                    continue;
                }

                $education = $destitution["Pendidikan Kepala Keluarga"] ?? null;
                if ($education == null) {
                    Log::info("Error Pendidikan Kepala Keluarga :".json_encode($destitution));
                    continue;
                }
                $education_opt = $this->option_repo->getRecordByColumn('value', $education, 'education');
                if ($education_opt == null) {
                    Log::info("Error Pendidikan Kepala Keluarga Not Found :".json_encode($destitution));
                    continue;
                }

                $home_ownership = $destitution["Kepemilikan Rumah"] ?? null;
                if ($home_ownership == null) {
                    Log::info("Error Kepemilikan Rumah :".json_encode($destitution));
                    continue;
                }
                $home_ownership_opt = $this->option_repo->getRecordByColumn('value', $home_ownership, 'home_ownership');
                if ($home_ownership_opt == null) {
                    Log::info("Error Kepemilikan Rumah Not Found :".json_encode($destitution));
                    continue;
                }

                $etc_ownership = $destitution["Memiliki Simpanan Uang/Perhiasan/Ternak/Lainnya"] ?? null;
                if ($etc_ownership == null) {
                    Log::info("Error Memiliki Simpanan Uang/Perhiasan/Ternak/Lainnya :".json_encode($destitution));
                    continue;
                }
                $etc_ownership_opt = $this->option_repo->getRecordByColumn('value', $etc_ownership, 'etc_ownership');
                if ($etc_ownership_opt == null) {
                    Log::info("Error Memiliki Simpanan Uang/Perhiasan/Ternak/Lainnya Not Found :".json_encode($destitution));
                    continue;
                }

                $home_roof = $destitution["Jenis Atap"] ?? null;
                if ($home_roof == null) {
                    Log::info("Error Jenis Atap :".json_encode($destitution));
                    continue;
                }
                $home_roof_opt = $this->option_repo->getRecordByColumn('value', $home_roof, 'home_roof');
                if ($home_roof_opt == null) {
                    Log::info("Error Jenis Atap Not Found :".json_encode($destitution));
                    continue;
                }

                $home_roof_quality = $destitution["Kualitas Atap"] ?? null;
                if ($home_roof_quality == null) {
                    Log::info("Error Kualitas Atap :".json_encode($destitution));
                    continue;
                }
                $home_roof_quality_opt = $this->option_repo->getRecordByColumn('value', $home_roof_quality, 'home_roof_quality');
                if ($home_roof_quality_opt == null) {
                    Log::info("Error Kualitas Atap Not Found :".json_encode($destitution));
                    continue;
                }

                $home_wall = $destitution["Jenis Dinding"] ?? null;
                if ($home_wall == null) {
                    Log::info("Error Jenis Dinding :".json_encode($destitution));
                    continue;
                }
                $home_wall_opt = $this->option_repo->getRecordByColumn('value', $home_wall, 'home_wall');
                if ($home_wall_opt == null) {
                    Log::info("Error Jenis Dinding Not Found :".json_encode($destitution));
                    continue;
                }

                $home_wall_quality = $destitution["Kualitas Dinding"] ?? null;
                if ($home_wall_quality == null) {
                    Log::info("Error Kualitas Dinding :".json_encode($destitution));
                    continue;
                }
                $home_wall_quality_opt = $this->option_repo->getRecordByColumn('value', $home_wall_quality, 'home_wall_quality');
                if ($home_wall_quality_opt == null) {
                    Log::info("Error Kualitas Dinding Not Found :".json_encode($destitution));
                    continue;
                }

                $home_floor = $destitution["Jenis Lantai"] ?? null;
                if ($home_floor == null) {
                    Log::info("Error Jenis Lantai :".json_encode($destitution));
                    continue;
                }
                $home_floor_opt = $this->option_repo->getRecordByColumn('value', $home_floor, 'home_floor');
                if ($home_floor_opt == null) {
                    Log::info("Error Jenis Lantai Not Found :".json_encode($destitution));
                    continue;
                }

                $home_floor_quality = $destitution["Kualitas Lantai"] ?? null;
                if ($home_floor_quality == null) {
                    Log::info("Error Kualitas Lantai :".json_encode($destitution));
                    continue;
                }
                $home_floor_quality_opt = $this->option_repo->getRecordByColumn('value', $home_floor_quality, 'home_floor_quality');
                if ($home_floor_quality_opt == null) {
                    Log::info("Error Kualitas Lantai Not Found :".json_encode($destitution));
                    continue;
                }

                $home_electricity = $destitution["Sumber Penerangan"] ?? null;
                if ($home_electricity == null) {
                    Log::info("Error Sumber Penerangan :".json_encode($destitution));
                    continue;
                }
                $home_electricity_opt = $this->option_repo->getRecordByColumn('value', $home_electricity, 'home_electricity');
                if ($home_electricity_opt == null) {
                    Log::info("Error Sumber Penerangan Not Found :".json_encode($destitution));
                    continue;
                }

                $home_electricity_power = $destitution["Daya Listrik Terpasang"] ?? null;
                if ($home_electricity_power == null) {
                    Log::info("Error Daya Listrik Terpasang :".json_encode($destitution));
                    continue;
                }
                $home_electricity_power_opt = $this->option_repo->getRecordByColumn('value', $home_electricity_power, 'home_electricity_power');
                if ($home_electricity_power_opt == null) {
                    Log::info("Error Daya Listrik Terpasang Not Found :".json_encode($destitution));
                    continue;
                }

                $home_cooking = $destitution["Bahan Bakar Memasak"] ?? null;
                if ($home_cooking == null) {
                    Log::info("Error Bahan Bakar Memasak :".json_encode($destitution));
                    continue;
                }
                $home_cooking_opt = $this->option_repo->getRecordByColumn('value', $home_cooking, 'home_cooking');
                if ($home_cooking_opt == null) {
                    Log::info("Error Bahan Bakar Memasak Not Found :".json_encode($destitution));
                    continue;
                }

                $home_water = $destitution["Sumber Air Minum"] ?? null;
                if ($home_water == null) {
                    Log::info("Error Sumber Air Minum :".json_encode($destitution));
                    continue;
                }
                $home_water_opt = $this->option_repo->getRecordByColumn('value', $home_water, 'home_water');
                if ($home_water_opt == null) {
                    Log::info("Error Sumber Air Minum Not Found :".json_encode($destitution));
                    continue;
                }

                $home_toilet_ownership = $destitution["Memiliki fasilitas Buang Air Besar"] ?? null;
                if ($home_toilet_ownership == null) {
                    Log::info("Error Memiliki fasilitas Buang Air Besar :".json_encode($destitution));
                    continue;
                }
                $home_toilet_ownership_opt = $this->option_repo->getRecordByColumn('value', $home_toilet_ownership, 'home_toilet_ownership');
                if ($home_toilet_ownership_opt == null) {
                    Log::info("Error Memiliki fasilitas Buang Air Besar Not Found :".json_encode($destitution));
                    continue;
                }

                $stunting_risk = $destitution["Resiko Stunting"] ?? null;
                if ($stunting_risk == null) {
                    Log::info("Error Resiko Stunting :".json_encode($destitution));
                    continue;
                }
                $stunting_risk_opt = $this->option_repo->getRecordByColumn('value', $stunting_risk, 'stunting_risk');
                if ($stunting_risk_opt == null) {
                    Log::info("Error Resiko Stunting Not Found :".json_encode($destitution));
                    continue;
                }

                $bpnt = $destitution["Penerima BPNT"] ?? null;
                if ($bpnt == null) {
                    Log::info("Error Penerima BPNT :".json_encode($destitution));
                    continue;
                }

                $bpum = $destitution["Penerima BPUM"] ?? null;
                if ($bpum == null) {
                    Log::info("Error Penerima BPUM :".json_encode($destitution));
                    continue;
                }

                $bst = $destitution["Penerima BST"] ?? null;
                if ($bst == null) {
                    Log::info("Error Penerima BST :".json_encode($destitution));
                    continue;
                }

                $pkh = $destitution["Penerima PKH"] ?? null;
                if ($pkh == null) {
                    Log::info("Error Penerima PKH :".json_encode($destitution));
                    continue;
                }

                $sembako = $destitution["Penerima SEMBAKO"] ?? null;
                if ($sembako == null) {
                    Log::info("Error Penerima SEMBAKO :".json_encode($destitution));
                    continue;
                }

                $prakerja = $destitution["Penerima Prakerja"] ?? null;
                if ($prakerja == null) {
                    Log::info("Error Penerima Prakerja :".json_encode($destitution));
                    continue;
                }

                $kur = $destitution["Penerima KUR"] ?? null;
                if ($kur == null) {
                    Log::info("Error Penerima KUR :".json_encode($destitution));
                    continue;
                }

                $data = [
                    'data_id' => source_data_active(),
                    "p3ke" => $destitution["ID Keluarga P3KE"] ?? null,
                    "last_update_year" => isset($destitution["Dimuktakhirkan Tahun"]) ? $destitution["Dimuktakhirkan Tahun"] : 0,
                    "province_id" => $province->id,
                    "regency_id" => $regency->id,
                    "district_id" => $district->id,
                    "subdistrict_id" => $subdistrict->id,
                    "kemdagri_code" => $kemdagri_code,
                    "decile" => $destitution["Desil Kesejahteraan"] ?? 0,
                    "percentile" => $destitution["Persentil"] ?? 0,
                    "address" => $destitution["Alamat"] ?? null,
                    "priority_verval_id" => $prority_verval_opt->id,
                    "name" => $destitution["Nama Kepala Keluarga"] ?? null,
                    "nik" => $destitution["NIK Kepala Keluarga"] ?? null,
                    "padan_dukcapil_id" => $padan_dukcapil_opt->id,
                    "gender" => isset($destitution["Jenis Kelamin Kepala Keluarga"]) ? (strtolower($destitution["Jenis Kelamin Kepala Keluarga"]) == 'laki-laki' ? "L" : "P") : null,
                    "gender_id" => $gender_opt->id,
                    "birth_date" => $destitution["Tanggal Lahir Kepala Keluarga"] ?? null,
                    "job_id" => $job_opt->id,
                    "job_status_id" => $job_status_opt->id,
                    "education_id" => $education_opt->id,
                    "marital_status_id" => $marital_status_opt->id,
                    "home_ownership_id" => $home_ownership_opt->id,
                    "etc_ownership_id" => $etc_ownership_opt->id,
                    "home_roof_id" => $home_roof_opt->id,
                    "home_roof_quality_id" => $home_roof_quality_opt->id,
                    "home_wall_id" => $home_wall_opt->id,
                    "home_wall_quality_id" => $home_wall_quality_opt->id,
                    "home_floor_id" => $home_floor_opt->id,
                    "home_floor_quality_id" => $home_floor_quality_opt->id,
                    "home_electricity_id" => $home_electricity_opt->id,
                    "home_electricity_power_id" => $home_electricity_power_opt->id,
                    "home_cooking_id" => $home_cooking_opt->id,
                    "home_water_id" => $home_water_opt->id,
                    "home_toilet_ownership_id" => $home_toilet_ownership_opt->id,
                    "stunting_risk_id" => $stunting_risk_opt->id,
                    "is_bpnt" => ($destitution["Penerima BPNT"] == 'Ya' ? true : false),
                    "is_bpum" => ($destitution["Penerima BPUM"] == 'Ya' ? true : false),
                    "is_bst" => ($destitution["Penerima BST"] == 'Ya' ? true : false),
                    "is_pkh" => ($destitution["Penerima PKH"] == 'Ya' ? true : false),
                    "is_sembako" => ($destitution["Penerima SEMBAKO"] == 'Ya' ? true : false),
                    "is_prakerja" => ($destitution["Penerima Prakerja"] == 'Ya' ? true : false),
                    "is_kur" => ($destitution["Penerima KUR"] == 'Ya' ? true : false),
                ];

                DB::beginTransaction();
                try {
                    $this->destitution_kk_repo->insertRecord($data);
                    $this->import_repo->updateRecord($record->id, ['is_sync' => true]);
                    DB::commit();
                } catch (\Exception $err) {
                    Log::info("Error {$err->getCode()} : {$err->getMessage()}");
                    DB::rollBack();
                }
            }
            $i++;
        }
        $progressBar->finish();
    }
}
