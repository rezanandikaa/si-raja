<?php

namespace Tests\Unit;

use App\Libraries\SheetReader;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use PHPUnit\Framework\TestCase;

/**
 * Berkas uji dibuat sendiri (bukan fixture) supaya tidak ada berkas biner
 * ikut masuk repo.
 */
class SheetReaderTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = tempnam(sys_get_temp_dir(), 'sheet') . '.xlsx';

        $writer = new Writer();
        $writer->openToFile($this->path);

        $writer->addRow(Row::fromValues(['kolom_art', 'nama']));
        $writer->addRow(Row::fromValues(['A1', 'Warga Satu']));

        $writer->addNewSheetAndMakeItCurrent();
        $writer->addRow(Row::fromValues(['nomor_kartu_keluarga', 'nama_anggota_keluarga', 'desil_nasional']));
        $writer->addRow(Row::fromValues(['KK1', 'Kepala Satu', 1]));
        $writer->addRow(Row::fromValues([null, null, null]));
        $writer->addRow(Row::fromValues(['KK2', 'Kepala Dua', 2]));
        $writer->addRow(Row::fromValues(['KK3', 'Kepala Tiga', 3]));

        $writer->close();
    }

    protected function tearDown(): void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
        parent::tearDown();
    }

    public function test_preview_returns_sheet_names_and_rows(): void
    {
        $preview = (new SheetReader())->preview($this->path, 'Sheet2', 100);

        $this->assertSame(['Sheet1', 'Sheet2'], $preview['sheets']);
        $this->assertSame('Sheet2', $preview['sheet']);
        $this->assertSame(['nomor_kartu_keluarga', 'nama_anggota_keluarga', 'desil_nasional'], $preview['columns']);
        $this->assertSame(3, count($preview['rows']));
        $this->assertSame(['KK1', 'Kepala Satu', 1], $preview['rows'][0]);
        $this->assertFalse($preview['truncated']);
    }

    public function test_preview_marks_truncated(): void
    {
        // Sheet2 punya 3 baris isi; limit 2 harus menandai masih ada sisa.
        $preview = (new SheetReader())->preview($this->path, 'Sheet2', 2);

        $this->assertSame('Sheet2', $preview['sheet']);
        $this->assertTrue($preview['truncated']);
        $this->assertCount(2, $preview['rows']);
    }

    public function test_missing_sheet_falls_back_to_the_first_sheet(): void
    {
        // Berkas DTSEN nyata bisa bernama sheet "Sheet1", bukan "KK" — halaman
        // harus tetap menampilkan tabel, bukan tabel kosong.
        $preview = (new SheetReader())->preview($this->path, SheetReader::SHEET_KK);

        $this->assertSame('Sheet1', $preview['sheet']);
        $this->assertSame(['kolom_art', 'nama'], $preview['columns']);
        $this->assertSame([['A1', 'Warga Satu']], $preview['rows']);
        $this->assertSame(['Sheet1', 'Sheet2'], $preview['sheets']);
    }

    public function test_stream_chunks_are_keyed_by_header(): void
    {
        $chunks = iterator_to_array((new SheetReader())->stream($this->path, 'Sheet2', 2));

        $this->assertCount(2, $chunks);
        $this->assertSame(['nomor_kartu_keluarga' => 'KK1', 'nama_anggota_keluarga' => 'Kepala Satu', 'desil_nasional' => 1], $chunks[0][0]);
        $this->assertCount(1, $chunks[1]);
        $this->assertSame('KK3', $chunks[1][0]['nomor_kartu_keluarga']);
    }

    public function test_stream_falls_back_to_the_first_sheet_when_sheet_is_absent(): void
    {
        // Sama dengan pratinjau: sheet "KK" tidak ada, jadi sheet pertama yang
        // dibaca — bukan nol baris yang bikin unggahan diam-diam kosong.
        $chunks = iterator_to_array((new SheetReader())->stream($this->path, SheetReader::SHEET_KK));

        $this->assertCount(1, $chunks);
        $this->assertSame(['kolom_art' => 'A1', 'nama' => 'Warga Satu'], $chunks[0][0]);
    }

    public function test_dates_and_booleans_are_normalized(): void
    {
        // Sel tanggal harus ber-style format tanggal, kalau tidak Excel
        // menyimpannya sebagai angka seri (46053) dan bukan tanggal.
        $date_style = new Style();
        $date_style->setFormat('yyyy-mm-dd');

        $path = tempnam(sys_get_temp_dir(), 'sheet') . '.xlsx';
        $writer = new Writer();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues(['tanggal', 'flag']));
        $writer->addRow(new Row([
            Cell\DateTimeCell::fromValue(new \DateTime('2026-01-31'), $date_style),
            Cell\BooleanCell::fromValue(true),
        ]));
        $writer->close();

        $rows = iterator_to_array((new SheetReader())->stream($path))[0];

        $this->assertSame('2026-01-31', $rows[0]['tanggal']);
        $this->assertSame(1, $rows[0]['flag']);

        unlink($path);
    }
}
