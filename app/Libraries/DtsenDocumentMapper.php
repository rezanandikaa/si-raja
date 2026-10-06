<?php

namespace App\Libraries;

use App\Repositories\Master\RegionRepository;

/**
 * Pemetaan dokumen DTSEN (sheet KK / ART) ke kolom tabel destitution.
 *
 * Dokumen memakai header sendiri (`nomor_kartu_keluarga`, `kecamatan`, `rt_ktp`,
 * …) sementara tabel memakai kolom aplikasi (`no_kk`, `district_id`, `rt`, …).
 * Peta di bawah ini adalah satu-satunya tempat pemetaan itu ditulis: dipakai
 * saat impor (SyncData) dan saat menyusun pilihan kolom grafik
 * (DashboardController::get_form_props()).
 */
class DtsenDocumentMapper
{
    /** Nilai sy_file.type untuk berkas KK. */
    public const TYPE_KK = 'KEPALA-KELUARGA';

    /** Nilai sy_file.type untuk berkas ART. */
    public const TYPE_ART = 'INDIVIDU';

    /**
     * Header dokumen => [kolom DB, label untuk form grafik].
     *
     * Kolom bertanda `_id` diisi lewat pencarian wilayah, bukan disalin apa
     * adanya. `status_hubungan_keluarga` sengaja dipetakan ke
     * `relationship_code` (angka mentah) karena legendanya belum ada.
     */
    private const MAP = [
        self::TYPE_KK => [
            'nomor_kartu_keluarga' => ['no_kk', 'Nomor Kartu Keluarga'],
            'kode_kabupaten_kota' => ['regency_id', 'Kabupaten/Kota'],
            'kecamatan' => ['district_id', 'Kecamatan'],
            'kelurahan_desa' => ['subdistrict_id', 'Desa/Kelurahan'],
            'alamat' => ['address', 'Alamat'],
            'jumlah_anggota_keluarga' => ['member_count', 'Jumlah Anggota Keluarga'],
            'nama_anggota_keluarga' => ['name', 'Nama Kepala Keluarga'],
            'desil_nasional' => ['decile', 'Desil'],
            'pbi_nas' => ['is_pbi_nas', 'PBI Nasional'],
            'pbi_pemda' => ['is_pbi_pemda', 'PBI Pemda'],
        ],
        self::TYPE_ART => [
            'nomor_induk_kependudukan' => ['nik', 'NIK'],
            'nomor_kartu_keluarga' => ['no_kk', 'Nomor Kartu Keluarga'],
            'nama' => ['name', 'Nama'],
            'status_hubungan_keluarga' => ['relationship_code', 'Status Hubungan Keluarga'],
            'kode_kabupaten_kota_ktp' => ['regency_id', 'Kabupaten/Kota (KTP)'],
            'kecamatan_ktp' => ['district_id', 'Kecamatan (KTP)'],
            'kelurahan_desa_ktp' => ['subdistrict_id', 'Desa/Kelurahan (KTP)'],
            'rt_ktp' => ['rt', 'RT'],
            'rw_ktp' => ['rw', 'RW'],
            'dusun_ktp' => ['dusun', 'Dusun'],
            'alamat_ktp' => ['address', 'Alamat'],
            'desil' => ['decile', 'Desil'],
        ],
    ];

    /**
     * Kolom NOT NULL bertipe id yang tidak ada padanannya di dokumen. Diisi 0
     * supaya baris dokumen bisa masuk tanpa mengubah skema jadi nullable.
     */
    private const ZERO_ID_COLUMNS = [
        self::TYPE_KK => [
            'priority_verval_id', 'padan_dukcapil_id', 'gender_id', 'job_id',
            'job_status_id', 'education_id', 'marital_status_id',
            'home_ownership_id', 'etc_ownership_id', 'home_roof_id',
            'home_roof_quality_id', 'home_wall_id', 'home_wall_quality_id',
            'home_floor_id', 'home_floor_quality_id', 'home_electricity_id',
            'home_electricity_power_id', 'home_cooking_id', 'home_water_id',
            'home_toilet_ownership_id', 'stunting_risk_id',
        ],
        self::TYPE_ART => [
            'priority_verval_id', 'padan_dukcapil_id', 'gender_id',
            'relationship_id', 'marital_status_id', 'job_id', 'job_status_id',
            'education_id', 'age_group_id',
        ],
    ];

    protected $region_repo;

    public function __construct(RegionRepository $region_repo)
    {
        $this->region_repo = $region_repo;
    }

    /**
     * Header dokumen yang dikenali untuk satu tipe berkas.
     */
    public static function headers($type)
    {
        return array_keys(self::MAP[$type] ?? []);
    }

    /**
     * Pilihan kolom untuk dropdown form grafik: [['value' => kolom DB, 'label' => teks dokumen]].
     * Bentuk pasangan value/label ini sudah didukung component/form.blade.php.
     */
    public static function columnOptions($type)
    {
        $options = [];
        foreach (self::MAP[$type] ?? [] as $header => $pair) {
            $options[] = ['value' => $pair[0], 'label' => $pair[1]];
        }

        return $options;
    }

    /**
     * Daftar putih kolom yang boleh dipakai sebagai `column` / `value` grafik.
     * Nilai di luar daftar ini harus ditolak pemanggil sebelum masuk SQL.
     */
    public static function columns($type)
    {
        $columns = array_column(self::MAP[$type] ?? [], 0);
        $columns[] = 'province_id';

        return array_values(array_unique($columns));
    }

    /**
     * Sumber dashboard mana yang isinya dokumen DTSEN, dan tipe dokumennya
     * apa. Null berarti sumbernya bukan dokumen (PROGRAM / REALISASI).
     * Kode sumber sengaja tetap kode lama supaya baris dashboard yang sudah
     * tersimpan tidak perlu migrasi data.
     */
    public static function typeForSource($source)
    {
        if ($source == 'P3KE-INDIVIDU') {
            return self::TYPE_ART;
        }
        if ($source == 'P3KE-KEPALA-KELUARGA') {
            return self::TYPE_KK;
        }

        return null;
    }

    public static function isKnownType($type)
    {
        return isset(self::MAP[$type]);
    }

    /**
     * Baris dokumen dikenali dari header khasnya, supaya jalur impor lama
     * (berkas P3KE berbahasa Indonesia) tidak ikut berubah.
     */
    public static function isDocumentRow($row)
    {
        if (!is_array($row)) {
            return false;
        }

        return array_key_exists('nomor_kartu_keluarga', $row)
            || array_key_exists('nomor_induk_kependudukan', $row);
    }

    /**
     * Susun larik siap insert untuk satu baris dokumen.
     * Mengembalikan null kalau wilayahnya tidak bisa ditentukan — pemanggil
     * menghitung baris gagal itu, bukan menebak wilayah.
     */
    public function map(array $row, $type)
    {
        if (!self::isKnownType($type)) {
            return null;
        }

        $region_code_header = $this->headerForColumn($type, 'regency_id');
        $regency_code = $this->digits($row[$region_code_header] ?? null);
        if (strlen($regency_code) < 4) {
            return null;
        }

        $province = $this->region_repo->getRecordByColumn('code', substr($regency_code, 0, 2));
        $regency = $this->region_repo->getRecordByColumn('code', substr($regency_code, 0, 4));
        if ($province === null || $regency === null) {
            return null;
        }

        $district = $this->region_repo->getRecordByNameAndParent(
            $row[$this->headerForColumn($type, 'district_id')] ?? null,
            $regency->id
        );
        if ($district === null) {
            return null;
        }

        $subdistrict = $this->region_repo->getRecordByNameAndParent(
            $row[$this->headerForColumn($type, 'subdistrict_id')] ?? null,
            $district->id
        );
        if ($subdistrict === null) {
            return null;
        }

        $decile = $this->toInt($this->valueForColumn($type, $row, 'decile'));

        // data_id sengaja tidak diisi di sini: itu urusan pemanggil (SyncData),
        // supaya pemetaan ini murni dan bisa diuji tanpa boot aplikasi.
        $data = [
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'district_id' => $district->id,
            'subdistrict_id' => $subdistrict->id,
            // Kode Kemdagri desa/kelurahan, sama seperti jalur impor lama.
            'kemdagri_code' => $subdistrict->code,
            'decile' => $decile,
            // Dokumen tidak memuat persentil. Desil N mencakup persentil
            // (N-1)*10+1..N*10, jadi batas atasnya dipakai supaya saringan
            // 'default_percentile' di repository tetap berlaku wajar.
            'percentile' => $decile * 10,
            'name' => $this->valueForColumn($type, $row, 'name'),
            'nik' => $this->valueForColumn($type, $row, 'nik'),
            'no_kk' => $this->valueForColumn($type, $row, 'no_kk'),
            'address' => $this->valueForColumn($type, $row, 'address'),
            'rt' => $this->valueForColumn($type, $row, 'rt'),
            'rw' => $this->valueForColumn($type, $row, 'rw'),
            'dusun' => $this->valueForColumn($type, $row, 'dusun'),
            'relationship_code' => $this->nullableInt($this->valueForColumn($type, $row, 'relationship_code')),
            'member_count' => $this->nullableInt($this->valueForColumn($type, $row, 'member_count')),
            'is_pbi_nas' => $this->nullableBool($this->valueForColumn($type, $row, 'is_pbi_nas')),
            'is_pbi_pemda' => $this->nullableBool($this->valueForColumn($type, $row, 'is_pbi_pemda')),
        ];

        foreach (self::ZERO_ID_COLUMNS[$type] as $column) {
            $data[$column] = 0;
        }

        return $data;
    }

    /**
     * Nama header dokumen yang memetakan ke satu kolom DB.
     */
    private function headerForColumn($type, $column)
    {
        foreach (self::MAP[$type] as $header => $pair) {
            if ($pair[0] === $column) {
                return $header;
            }
        }

        return null;
    }

    private function valueForColumn($type, array $row, $column)
    {
        $header = $this->headerForColumn($type, $column);
        if ($header === null || !array_key_exists($header, $row)) {
            return null;
        }

        $value = $row[$header];
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === '' ? null : $value;
    }

    /**
     * `36.02` dan `3602` sama-sama muncul di dokumen, jadi titik dibuang dulu.
     * Angka dari Excel bisa datang sebagai float (`36.02`), karena itu
     * diformat tanpa nol ekor sebelum disaring.
     */
    private function digits($value)
    {
        if ($value === null) {
            return '';
        }
        if (is_float($value) || is_int($value)) {
            $value = number_format((float) $value, 2, '.', '');
            $value = rtrim(rtrim($value, '0'), '.');
        }

        return preg_replace('/\D/', '', (string) $value);
    }

    private function toInt($value)
    {
        return $this->nullableInt($value) ?? 0;
    }

    private function nullableInt($value)
    {
        return $value === null ? null : (int) $value;
    }

    private function nullableBool($value)
    {
        return $value === null ? null : (bool) ((int) $value);
    }
}
