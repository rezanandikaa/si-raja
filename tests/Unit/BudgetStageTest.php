<?php

namespace Tests\Unit;

use App\Http\Controllers\Transaction\ProgramController;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

class BudgetStageTest extends TestCase
{
    /**
     * Panggil validate_budget_stage() lewat reflection; method tidak menyentuh
     * dependency constructor, jadi controller tidak perlu di-boot penuh.
     */
    private function validate(string $stage, int $budget_source_id, float $allocation, array $budgets)
    {
        $rows = collect($budgets)->map(function ($row) {
            return (object) $row;
        });

        $controller = (new ReflectionClass(ProgramController::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(ProgramController::class, 'validate_budget_stage');
        $method->setAccessible(true);

        return $method->invoke($controller, $stage, $budget_source_id, $allocation, $rows);
    }

    public function test_tahapan_lain_saat_list_masih_kosong_ditolak()
    {
        $this->assertSame('Oopps, Masih Tahapan Murni', $this->validate('Pergeseran', 1, 1000, []));
        $this->assertSame('Oopps, Masih Tahapan Murni', $this->validate('Perubahan', 1, 1000, []));
        $this->assertNull($this->validate('Murni', 1, 1000, []));
    }

    public function test_pagu_awal_hanya_boleh_sekali()
    {
        $rows = [['budget_source_id' => 1, 'budget_allocation' => 1000, 'budget_stage' => 'Murni']];

        $this->assertSame('Pagu Awal Sudah di Input', $this->validate('Murni', 1, 1000, $rows));
        $this->assertNull($this->validate('Perubahan', 1, 1000, $rows));
    }

    public function test_baris_murni_tidak_bisa_diedit_atau_dihapus()
    {
        $editable = function ($stage, $status, $refocusing) {
            $row = (object) [
                'budget_stage' => $stage,
                'status' => $status,
                'refocusing_flag' => $refocusing,
            ];

            $controller = (new ReflectionClass(ProgramController::class))->newInstanceWithoutConstructor();
            $method = new ReflectionMethod(ProgramController::class, 'budget_editable');
            $method->setAccessible(true);

            return $method->invoke($controller, $row);
        };

        // Murni ditolak walau dokumen masih DRAFT.
        $this->assertFalse($editable('Murni', 'DRAFT', false));
        // Tahapan lain ikut aturan hapus yang lama.
        $this->assertTrue($editable('Perubahan', 'DRAFT', false));
        $this->assertTrue($editable('Pergeseran', 'APPROVED', true));
        $this->assertFalse($editable('Perubahan', 'APPROVED', false));

        $controller = (new ReflectionClass(ProgramController::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(ProgramController::class, 'budget_editable');
        $method->setAccessible(true);
        $this->assertFalse($method->invoke($controller, null));
    }

    public function test_pergeseran_tidak_boleh_mengubah_pagu_sumber_yang_sama()
    {
        $rows = [
            ['budget_source_id' => 1, 'budget_allocation' => 1000, 'budget_stage' => 'Murni'],
            ['budget_source_id' => 2, 'budget_allocation' => 500, 'budget_stage' => 'Murni'],
        ];

        // Pagu sama dengan sumber pembiayaan yang sama -> lolos.
        $this->assertNull($this->validate('Pergeseran', 1, 1000, $rows));
        // Pagu sumber itu diubah -> ditolak.
        $this->assertSame('Nilai Pagu tidak Boleh di Ubah', $this->validate('Pergeseran', 1, 1500, $rows));
        // Pembanding adalah baris terakhir sumber itu, bukan baris pertama.
        $riwayat = array_merge($rows, [
            ['budget_source_id' => 1, 'budget_allocation' => 800, 'budget_stage' => 'Perubahan'],
        ]);
        $this->assertNull($this->validate('Pergeseran', 1, 800, $riwayat));
        $this->assertSame('Nilai Pagu tidak Boleh di Ubah', $this->validate('Pergeseran', 1, 1000, $riwayat));
        // Sumber pembiayaan baru belum punya pagu, jadi tidak ada yang berubah.
        $this->assertNull($this->validate('Pergeseran', 3, 500, $rows));
    }
}
