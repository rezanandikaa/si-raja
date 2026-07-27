<?php

namespace App\Exports;

use App\Models\Master\Mt_budget_year;
use App\Models\Transaction\Tr_program;
use App\Models\Transaction\Tr_program_budget;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProgramExport implements FromView
{
    use Exportable;

    protected $budget_year_id;

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
            ->select(
                'tr_program.*',
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
                        $values[] = $value['budget_source_name'];
                    }
                    $data[$i]['budget_source'] = implode(", ", $values);
                }
            }
        }

        return view('excel.program', compact('data'));
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle('1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
