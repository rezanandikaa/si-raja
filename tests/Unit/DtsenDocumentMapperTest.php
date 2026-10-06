<?php

namespace Tests\Unit;

use App\Libraries\DtsenDocumentMapper;
use App\Repositories\Master\RegionRepository;
use PHPUnit\Framework\TestCase;

/**
 * Repository wilayah diganti tiruan: pemetaan diuji tanpa DB supaya tesnya
 * jalan di mesin mana pun, tidak bergantung isi mt_region.
 */
class DtsenDocumentMapperTest extends TestCase
{
    private const REGIONS_BY_CODE = [
        '36' => ['id' => 1, 'code' => '36', 'name' => 'BANTEN'],
        '3602' => ['id' => 2, 'code' => '3602', 'name' => 'LEBAK'],
    ];

    private const REGIONS_BY_NAME = [
        // parent_id ikut jadi kunci: nama desa berulang antar kecamatan.
        'SOBANG|2' => ['id' => 3, 'code' => '360203', 'name' => 'SOBANG'],
        'SINAR JAYA|3' => ['id' => 4, 'code' => '3602032001', 'name' => 'SINAR JAYA'],
    ];

    private function makeMapper()
    {
        $region_repo = new class(self::REGIONS_BY_CODE, self::REGIONS_BY_NAME) extends RegionRepository {
            public $code_lookups = [];

            private $by_code;
            private $by_name;

            public function __construct(array $by_code, array $by_name)
            {
                $this->by_code = $by_code;
                $this->by_name = $by_name;
            }

            public function getRecordByColumn($column, $value, $to_upper = true)
            {
                $this->code_lookups[] = $value;
                $row = $this->by_code[$value] ?? null;

                return $row === null ? null : (object) $row;
            }

            public function getRecordByNameAndParent($name, $parent_id)
            {
                $key = strtoupper(trim((string) $name)) . '|' . $parent_id;
                $row = $this->by_name[$key] ?? null;

                return $row === null ? null : (object) $row;
            }
        };

        return [new DtsenDocumentMapper($region_repo), $region_repo];
    }

    public function test_kk_row_is_mapped_to_destitution_columns(): void
    {
        [$mapper, $region_repo] = $this->makeMapper();

        $data = $mapper->map([
            'nomor_kartu_keluarga' => '3602000000000001',
            'kode_kabupaten_kota' => '36.02',
            'kecamatan' => 'Sobang',
            'kelurahan_desa' => 'Sinar Jaya',
            'alamat' => 'Kp. Sawah',
            'jumlah_anggota_keluarga' => '4',
            'nama_anggota_keluarga' => 'Kepala Satu',
            'desil_nasional' => 2,
            'pbi_nas' => 1,
            'pbi_pemda' => 0,
        ], DtsenDocumentMapper::TYPE_KK);

        // '36.02' harus dibaca sebagai kode provinsi 36 + kabupaten 3602,
        // bukan dicocokkan apa adanya ke mt_region.
        $this->assertSame(['36', '3602'], $region_repo->code_lookups);
        $this->assertSame(2, $data['regency_id']);
        $this->assertSame(3, $data['district_id']);
        $this->assertSame(4, $data['subdistrict_id']);
        $this->assertSame('3602032001', $data['kemdagri_code']);

        $this->assertSame('3602000000000001', $data['no_kk']);
        $this->assertSame('Kepala Satu', $data['name']);
        $this->assertSame(4, $data['member_count']);
        $this->assertSame(2, $data['decile']);
        $this->assertSame(20, $data['percentile']);
        $this->assertTrue($data['is_pbi_nas']);
        $this->assertFalse($data['is_pbi_pemda']);

        // Kolom id wajib yang tidak ada di dokumen harus tetap terisi.
        $this->assertSame(0, $data['stunting_risk_id']);
        $this->assertSame(0, $data['gender_id']);
    }

    public function test_art_row_keeps_relationship_code_and_location_fields(): void
    {
        [$mapper] = $this->makeMapper();

        $data = $mapper->map([
            'No.' => 1,
            'nomor_induk_kependudukan' => '3602012002000001',
            'nomor_kartu_keluarga' => '3602000000000001',
            'nama' => 'Warga Satu',
            'status_hubungan_keluarga' => '2',
            'kode_kabupaten_kota_ktp' => '3602',
            'kecamatan_ktp' => 'Sobang',
            'kelurahan_desa_ktp' => 'Sinar Jaya',
            'rt_ktp' => '003',
            'rw_ktp' => '002',
            'dusun_ktp' => 'Dusun Satu',
            'alamat_ktp' => 'Kp. Sawah',
            'desil' => '1',
        ], DtsenDocumentMapper::TYPE_ART);

        $this->assertSame('3602012002000001', $data['nik']);
        $this->assertSame('3602000000000001', $data['no_kk']);
        $this->assertSame('003', $data['rt']);
        $this->assertSame('002', $data['rw']);
        $this->assertSame('Dusun Satu', $data['dusun']);
        $this->assertSame('Kp. Sawah', $data['address']);
        $this->assertSame(3, $data['district_id']);
        $this->assertSame(4, $data['subdistrict_id']);

        // Angka status hubungan keluarga belum ada legendanya: disimpan apa
        // adanya, relationship_id tidak ditebak.
        $this->assertSame(2, $data['relationship_code']);
        $this->assertSame(0, $data['relationship_id']);
        $this->assertSame(10, $data['percentile']);
    }

    public function test_unknown_region_returns_null_instead_of_guessing(): void
    {
        [$mapper] = $this->makeMapper();

        $this->assertNull($mapper->map([
            'nomor_kartu_keluarga' => 'K1',
            'kode_kabupaten_kota' => '36.02',
            'kecamatan' => 'KECAMATAN YANG TIDAK ADA',
            'kelurahan_desa' => 'SINAR JAYA',
        ], DtsenDocumentMapper::TYPE_KK));

        // Kode kabupaten kosong juga ditolak, bukan dipaksa jadi id 0.
        $this->assertNull($mapper->map([
            'nomor_kartu_keluarga' => 'K1',
            'kode_kabupaten_kota' => '',
        ], DtsenDocumentMapper::TYPE_KK));
    }

    public function test_document_rows_are_recognised_and_column_options_follow_the_document(): void
    {
        $this->assertTrue(DtsenDocumentMapper::isDocumentRow(['nomor_kartu_keluarga' => 'K1']));
        $this->assertTrue(DtsenDocumentMapper::isDocumentRow(['nomor_induk_kependudukan' => 'N1']));
        $this->assertFalse(DtsenDocumentMapper::isDocumentRow(['Kode Kemdagri' => '3602032001']));

        $options = DtsenDocumentMapper::columnOptions(DtsenDocumentMapper::TYPE_ART);
        $this->assertContains(['value' => 'rt', 'label' => 'RT'], $options);
        $this->assertNotContains('No.', array_column($options, 'label'));

        // Kolom di luar peta tidak boleh lolos ke SQL sebagai kolom grafik.
        $this->assertNotContains('id', DtsenDocumentMapper::columns(DtsenDocumentMapper::TYPE_ART));
        $this->assertContains('district_id', DtsenDocumentMapper::columns(DtsenDocumentMapper::TYPE_ART));
    }
}
