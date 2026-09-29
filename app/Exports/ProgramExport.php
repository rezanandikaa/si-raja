<?php

namespace App\Exports;

use App\Models\Master\Mt_budget_year;
use App\Models\Transaction\Tr_program;
use App\Models\Transaction\Tr_program_budget;
use App\Models\Transaction\Tr_program_realization;
use App\Models\Transaction\Tr_program_sifat_bantuan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProgramExport implements FromView, WithStyles
{
    use Exportable;

    protected $budget_year_id;

    // Nomor baris pemisah antar Strategi OPPKPE di sheet, diisi di view() lalu
    // dipakai styles() untuk menebalkan + memberi latar barisnya.
    protected $separator_rows = [];

    public function __construct(int $budget_year_id)
    {
        $this->budget_year_id = $budget_year_id;
    }

    public function view(): View
    {
        $data = Tr_program::leftJoin('sy_option as program_goal', 'tr_program.program_goal_id', 'program_goal.id')
            ->leftJoin('mt_organization', 'tr_program.organization_id', 'mt_organization.id')
            ->leftJoin('mt_region as district', 'tr_program.district_id', 'district.id')
            ->leftJoin('mt_region as subdistrict', 'tr_program.subdistrict_id', 'subdistrict.id')
            ->where('tr_program.budget_year_id', $this->budget_year_id)
            ->where('tr_program.status', '<>', 'DROPPED')
            ->whereNull('tr_program.deleted_at')
            // Baris harus berkumpul per Strategi OPPKPE supaya baris pemisah di blade
            // tidak muncul berulang di tengah kelompok. Urutannya ikut sy_option.id.
            ->orderBy('program_goal.id')
            ->orderBy('tr_program.id')
            ->select(
                'tr_program.*',
                DB::raw('IFNULL(tr_program.description, "") as program_description'),
                DB::raw('IFNULL(program_goal.value, "") as goal_value'),
                DB::raw('IFNULL(mt_organization.name, "") as organization_name'),
                DB::raw('IFNULL(district.name, "") as district_name'),
                DB::raw('IFNULL(subdistrict.name, "") as subdistrict_name'),
            )
            ->get()
            ->toArray();

        for ($i=0; $i < count($data) ; $i++) {
            if ($data[$i]['id'] != 0){
                $program_budget = Tr_program_budget::leftJoin('mt_budget_source', 'tr_program_budget.budget_source_id', 'mt_budget_source.id')
                    ->where('tr_program_budget.program_id', $data[$i]['id'])
                    ->whereNull('tr_program_budget.deleted_at')
                    ->select(
                        DB::raw('IFNULL(mt_budget_source.name, "") as budget_source_name')
                    )
                    ->get()
                    ->toArray();

                $data[$i]['budget_source'] = '';
                if (count($program_budget) > 0){
                    $values = [];
                    foreach ($program_budget as $value) {
                        $name = trim($value['budget_source_name']);
                        // Satu program bisa punya beberapa baris budget untuk sumber yang sama
                        // (tahapan Murni/Pergeseran/Perubahan), tampilkan sekali saja.
                        if ($name != '' && !in_array($name, $values)) {
                            $values[] = $name;
                        }
                    }
                    $data[$i]['budget_source'] = implode(", ", $values);
                }
            }
        }

        // Realisasi per triwulan, dijumlahkan per program.
        $realization_map = [];
        $program_ids = array_filter(array_column($data, 'id'));
        if (count($program_ids) > 0) {
            $realizations = Tr_program_realization::whereIn('program_id', $program_ids)
                ->whereNull('deleted_at')
                ->select('program_id', 'quarterly', DB::raw('SUM(budget_realization) as total_realization'))
                ->groupBy('program_id', 'quarterly')
                ->get();

            foreach ($realizations as $row) {
                $realization_map[$row->program_id][(int) $row->quarterly] = (float) $row->total_realization;
            }
        }

        // Sasaran penerima manfaat, besaran manfaat, jenis bantuan, dan durasi pemberian bantuan
        // diisi per triwulan dengan teks bebas, jadi digabung unik per program.
        $realization_texts = ['target' => [], 'benefit' => [], 'jenis_bantuan' => [], 'duration_note' => []];
        if (count($program_ids) > 0) {
            $realizations_text = Tr_program_realization::whereIn('program_id', $program_ids)
                ->whereNull('deleted_at')
                ->orderBy('quarterly')
                ->select('program_id', 'target', 'benefit', 'jenis_bantuan', 'duration_note')
                ->get();

            foreach ($realizations_text as $row) {
                foreach (array_keys($realization_texts) as $column) {
                    $value = trim((string) $row->$column);
                    if ($value != '' && !in_array($value, $realization_texts[$column][$row->program_id] ?? [])) {
                        $realization_texts[$column][$row->program_id][] = $value;
                    }
                }
            }
        }

        for ($i = 0; $i < count($data); $i++) {
            for ($quarterly = 1; $quarterly <= 4; $quarterly++) {
                $data[$i]['realization_q'.$quarterly] = $realization_map[$data[$i]['id']][$quarterly] ?? 0;
            }

            $data[$i]['realization_total'] = $data[$i]['realization_q1'] + $data[$i]['realization_q2']
                + $data[$i]['realization_q3'] + $data[$i]['realization_q4'];
            $data[$i]['sifat_bantuan'] = '';

            // Kolom "Lokasi": kecamatan di baris pertama, lokasi tambahan di baris berikutnya.
            $lokasi_tambahan = trim($data[$i]['lokasi_tambahan'] ?? '');
            $data[$i]['lokasi'] = trim($data[$i]['district_name'] ?? '')
                . ($lokasi_tambahan != '' ? "\n" . $lokasi_tambahan : '');

            $data[$i]['sasaran_penerima_manfaat'] = implode('; ', $realization_texts['target'][$data[$i]['id']] ?? []);
            $data[$i]['besaran_manfaat'] = implode('; ', $realization_texts['benefit'][$data[$i]['id']] ?? []);
            $data[$i]['jenis_bantuan'] = implode('; ', $realization_texts['jenis_bantuan'][$data[$i]['id']] ?? []);
            $data[$i]['durasi_penerima_bantuan'] = implode('; ', $realization_texts['duration_note'][$data[$i]['id']] ?? []);
        }

        // Sifat bantuan per program, digabung kalau lebih dari satu.
        if (count($program_ids) > 0) {
            $sifat_bantuans = Tr_program_sifat_bantuan::whereIn('program_id', $program_ids)
                ->whereNull('deleted_at')
                ->where('sifat_bantuan', '<>', '')
                ->select('program_id', DB::raw("GROUP_CONCAT(DISTINCT sifat_bantuan SEPARATOR '; ') as sifat_bantuan"))
                ->groupBy('program_id')
                ->get();

            foreach ($sifat_bantuans as $row) {
                foreach ($data as $i => $program) {
                    if ($program['id'] == $row->program_id) {
                        $data[$i]['sifat_bantuan'] = $row->sifat_bantuan;
                    }
                }
            }
        }

        $budget_year = Mt_budget_year::find($this->budget_year_id);
        $budget_year_name = $budget_year ? $budget_year->name : '';

        // Baris sudah diurutkan per strategi di query, jadi cukup dipotong berurutan.
        // Satu tabel, satu header; yang ditambah cuma baris pemisah per strategi.
        $groups = [];
        foreach ($data as $record) {
            $groups[$record['goal_value']][] = $record;
        }

        // Nomor baris pemisah: header memakai baris 1-2, jadi baris pertama data = 3.
        // Tiap pemisah menambah satu baris, jadi penghitungnya ikut maju.
        $this->separator_rows = [];
        $row = 3;
        foreach ($groups as $records) {
            $this->separator_rows[] = $row;
            $row += count($records) + 1;
        }

        return view('excel.program', compact('groups', 'budget_year_name'));
    }

    public function styles(Worksheet $sheet)
    {
        $last_column = 'W';
        $last_row = $sheet->getHighestRow();

        // Header 2 baris (baris 1 merge untuk "Aktivitas Real" dan "Realisasi").
        $header = $sheet->getStyle("A1:{$last_column}2");
        $header->getFont()->setBold(true);
        $header->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $header->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $header->getAlignment()->setWrapText(true);
        $header->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE7E6E6');

        // Border seluruh tabel.
        $sheet->getStyle("A1:{$last_column}{$last_row}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Kolom alokasi + realisasi (triwulan dan total) tampil sebagai rupiah.
        // L = Sumber Pembiayaan, M = Sifat Bantuan, N = Lokasi, O..R = teks realisasi
        // (sasaran, besaran manfaat, jenis bantuan, durasi), tidak ikut diformat.
        $currency = '"Rp" #,##0';
        foreach (['K3:K', 'S3:W'] as $range) {
            $sheet->getStyle($range . $last_row)->getNumberFormat()->setFormatCode($currency);
            $sheet->getStyle($range . $last_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // Isi: rata atas + wrap agar teks panjang tidak melebar.
        $sheet->getStyle("A3:{$last_column}{$last_row}")
            ->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $sheet->getStyle("A3:A{$last_row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Baris pemisah Strategi OPPKPE. Dipasang setelah gaya isi di atas supaya
        // tidak ketiban rata-atas/wrap, dan setelah merge supaya gayanya menempel
        // di sel kiri-atas gabungan A..W.
        foreach ($this->separator_rows as $separator_row) {
            $sheet->mergeCells("A{$separator_row}:{$last_column}{$separator_row}");
            $separator = $sheet->getStyle("A{$separator_row}:{$last_column}{$separator_row}");
            $separator->getFont()->setBold(true);
            $separator->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D9D9');
            $separator->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $separator->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        // Lebar kolom: pendek tetap, teks panjang dibatasi.
        $widths = ['A' => 6, 'B' => 28, 'C' => 28, 'D' => 16, 'E' => 34, 'F' => 34, 'G' => 34, 'H' => 28, 'I' => 28, 'J' => 28, 'K' => 22, 'L' => 24, 'M' => 24, 'N' => 24, 'O' => 28, 'P' => 24, 'Q' => 24, 'R' => 24, 'S' => 18, 'T' => 18, 'U' => 18, 'V' => 18, 'W' => 20];
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Header tetap terlihat saat discroll.
        $sheet->freezePane('A3');
    }

    /**
     * @return string
     */
    public function title(): string
    {
        $budget_year = Mt_budget_year::find($this->budget_year_id);
        return 'Laporan Rencana ' . $budget_year->name;
    }
}
