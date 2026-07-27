<?php

namespace App\Exports\Sheets;

use App\Models\Transaction\Tr_program_budget;
use App\Models\Transaction\Tr_program_realization;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RealizationSheetExport implements FromView, WithStyles, WithTitle
{
    private $budget_year_id;
    private $quarterly;

    public function __construct(int $budget_year_id, int $quarterly)
    {
        $this->budget_year_id  = $budget_year_id;
        $this->quarterly = $quarterly;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $data = Tr_program_realization::leftJoin('tr_program', 'tr_program_realization.program_id', 'tr_program.id')
            ->leftJoin('sy_option as program_goal', 'tr_program.program_goal_id', 'program_goal.id')
            ->leftJoin('mt_organization', 'tr_program.organization_id', 'mt_organization.id')
            ->leftJoin('mt_region as district', 'tr_program.district_id', 'district.id')
            ->leftJoin('mt_region as subdistrict', 'tr_program.subdistrict_id', 'subdistrict.id')
            ->where('tr_program.budget_year_id', $this->budget_year_id)
            ->where('tr_program_realization.quarterly', $this->quarterly)
            ->whereNull('tr_program_realization.deleted_at')
            ->select(
                'tr_program_realization.*',
                DB::raw('IFNULL(tr_program.id, 0) as program_id'),
                DB::raw('IFNULL(tr_program.code, "") as program_code'),
                DB::raw('IFNULL(tr_program.program, "") as program_program'),
                DB::raw('IFNULL(tr_program.sub_activity, "") as program_sub_activity'),
                DB::raw('IFNULL(tr_program.description, "") as program_description'),
                DB::raw('IFNULL(tr_program.budget_allocation, 0) as program_budget_allocation'),
                DB::raw('IFNULL(program_goal.value, "") as program_goal_value'),
                DB::raw('IFNULL(mt_organization.name, "") as organization_name'),
                DB::raw('IFNULL(district.name, "") as district_name'),
                DB::raw('IFNULL(subdistrict.name, "") as subdistrict_name'),
                DB::raw("'' as budget_source")
            )
            ->get()
            ->toArray();

        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['program_id'] != 0) {
                $program_budget = Tr_program_budget::leftJoin('mt_budget_source', 'tr_program_budget.budget_source_id', 'mt_budget_source.id')
                    ->where('tr_program_budget.program_id', $data[$i]['program_id'])
                    ->whereNull('tr_program_budget.deleted_at')
                    ->select(
                        DB::raw('IFNULL(mt_budget_source.name, "") as budget_source_name')
                    )
                    ->get()
                    ->toArray();
                if (count($program_budget) > 0) {
                    $values = [];
                    foreach ($program_budget as $value) {
                        $values[] = $value['budget_source_name'];
                    }
                    $data[$i]['budget_source'] = implode(", ", $values);
                }
            }
        }

        return view('excel.realization', compact('data'));
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
        return 'Triwulan ' . $this->quarterly;
    }
}
