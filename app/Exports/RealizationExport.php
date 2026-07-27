<?php

namespace App\Exports;

use App\Exports\Sheets\RealizationSheetExport;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RealizationExport implements WithMultipleSheets
{
    use Exportable;

    protected $budget_year_id;

    public function __construct(int $budget_year_id)
    {
        $this->budget_year_id = $budget_year_id;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        for ($quarterly = 1; $quarterly <= 4; $quarterly++) {
            $sheets[] = new RealizationSheetExport($this->budget_year_id, $quarterly);
        }

        return $sheets;
    }
}
