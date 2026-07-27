<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction\Tr_program_realization;
use App\Models\Transaction\Tr_program_realization_bnba;
use Illuminate\Support\Facades\DB;

class ProgramRealizationRepository
{
    protected $model;
    protected $program_realization_bnba;
    protected $table_name = 'tr_program_realization';

    public function __construct(Tr_program_realization $model, Tr_program_realization_bnba $program_realization_bnba)
    {
        $this->model = $model;
        $this->program_realization_bnba = $program_realization_bnba;
    }

    public function getRecord($id)
    {
        return $this->model
            ->leftJoin('tr_program', $this->table_name . '.program_id', 'tr_program.id')
            ->where($this->table_name.'.id',$id)
            ->select(
                $this->table_name.'.*',
                'tr_program.status as program_status',
                'tr_program.budget_allocation as program_budget_allocation'
            )
            ->get()
            ->first();
    }

    public function getRecordBy($column, $value)
    {
        return $this->model
            ->where($this->table_name.'.'.$column, $value)
            ->select(
                $this->table_name.'.*'
            )
            ->get()
            ->first();
    }

    public function getOptionRecords($condition)
    {
        return $this->model
            ->whereRaw($condition)
            ->whereNull('deleted_at')
            ->get();
    }

    public function insertRecord($data)
    {
        $record = $this->model->create($data);
        return $record->id;
    }

    public function updateRecord($id, $data)
    {
        $this->model->find($id)
            ->update($data);
    }

    public function deleteRecord($id)
    {
        $this->model->where('id', $id)
            ->delete();
    }

    public function getRecords($cond)
    {
        return $this->model
            ->leftJoin('tr_program', $this->table_name.'.program_id', 'tr_program.id')
            ->whereRaw($cond)
            ->select(
                $this->table_name.'.*',
                'tr_program.marker as program_marker',
                'tr_program.budget_allocation as program_budget_allocation',
                'tr_program.code as program_code',
                'tr_program.program as program_program',
                'tr_program.activity as program_activity',
                'tr_program.sub_activity as program_sub_activity'
            )
            ->get();
    }

    public function getRecordsRaw($cond)
    {
        return $this->model
            ->leftJoin('tr_program', $this->table_name.'.program_id', 'tr_program.id')
            ->whereRaw($cond)
            ->select(
                $this->table_name.'.*',
                'tr_program.marker as program_marker',
                'tr_program.budget_allocation as program_budget_allocation',
                'tr_program.code as program_code',
                'tr_program.program as program_program',
                'tr_program.activity as program_activity',
                'tr_program.sub_activity as program_sub_activity'
            );
    }

    public function getRecordsGroupBy($cond, $group_by = 'tr_program_realization.program_id')
    {
        return $this->model
            ->leftJoin('tr_program', $this->table_name.'.program_id', 'tr_program.id')
            ->whereRaw($cond)
            ->select(
                $this->table_name.'.*',
                'tr_program.marker as program_marker',
                'tr_program.budget_allocation as program_budget_allocation',
                'tr_program.code as program_code',
                'tr_program.program as program_program',
                'tr_program.activity as program_activity',
                'tr_program.sub_activity as program_sub_activity',
                DB::raw('SUM('.$this->table_name.'.budget_realization) as sum_budget_realization')
            )
            ->orderBy('tr_program_realization.quarterly', 'desc')
            ->groupBy($group_by)
            ->get();
    }

    public function getTotalRealization($program_id)
    {
        $records = $this->model->where('program_id', $program_id)
            ->whereNull('deleted_at')
            ->orderBy('quarterly', 'desc')
            ->get()
            ->first();

        $total_realization = 0;
        // foreach ($records as $record) {
        //     $total_realization += (float) $record->budget_realization;
        // }
        if ($records != null) {
            $total_realization = (float) $records->budget_realization;
        }
        return $total_realization;
    }

    public function getTotalRealizationByProgramId($program_id, $except_id = 0)
    {
        $records = $this->model->where('program_id', $program_id)
            ->when($except_id != 0, function ($q) use ($except_id) {
                $q->where('id', '<>', $except_id);
            })
            ->whereNull('deleted_at')
            ->get();

        $total_realization = 0;
        if ($records != null) {
            foreach ($records as $record) {
                $total_realization += (float) $record->budget_realization;
            }
        }
        return $total_realization;
    }

    public function getTotalRecordsByColumn($column, $value, $condition = "1=1")
    {
        $records = $this->model
            ->leftJoin('tr_program', $this->table_name . '.program_id', 'tr_program.id')
            ->whereRaw($condition)
            ->where($column, $value)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name .'.id'
            )
            ->orderBy('tr_program_realization.quarterly', 'desc')
            ->groupBy('tr_program_realization.program_id')
            ->get();
        return $records->count();
    }

    public function getSumRecordsByColumn($column, $value, $condition = "1=1", $sum_column = "id")
    {
        $records = $this->model
            ->leftJoin('tr_program', $this->table_name . '.program_id', 'tr_program.id')
            ->whereRaw($condition)
            ->where($column, $value)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name . '.*'
            )
            ->orderBy('tr_program_realization.quarterly', 'desc')
            ->groupBy('tr_program_realization.program_id')
            ->get();;
        return $records->sum($sum_column);
    }

    public function getTotalRecordsByCond($condition = "1=1")
    {
        $records = $this->model
            ->leftJoin('tr_program', $this->table_name.'.program_id', 'tr_program.id')
            ->whereRaw($condition)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.id'
            )
            ->get();
        return $records->count();
    }

    public function insertRecordBnba($data)
    {
        $record = $this->program_realization_bnba->create($data);
        return $record->id;
    }

    public function updateRecordBnba($id, $data)
    {
        $this->program_realization_bnba->find($id)
            ->update($data);
    }

    public function deleteRecordBnba($id)
    {
        $this->program_realization_bnba->where('id', $id)
            ->delete();
    }

    public function deleteRecordByProgramId($program_id)
    {
        $this->model->where('program_id', $program_id)
            ->delete();
    }

    public function getDataTable($cond) 
    {
        return $this->model->leftJoin('mt_user as updated_by','tr_program_realization.updated_by_id','updated_by.id')
            ->leftJoin('tr_program', 'tr_program_realization.program_id', 'tr_program.id')
            ->leftJoin('mt_user as created_by','tr_program.created_by_id','created_by.id')
            ->leftJoin('mt_organization as created_by_org', 'created_by.organization_id', 'created_by_org.id')
            ->leftJoin('mt_budget_year', 'tr_program.budget_year_id', 'mt_budget_year.id')
            ->leftJoin('sy_option as program_goal', 'tr_program.program_goal_id', 'program_goal.id')
            ->leftJoin('mt_region as district', 'tr_program.district_id', 'district.id')
            ->leftJoin('mt_region as subdistrict', 'tr_program.subdistrict_id', 'subdistrict.id')
            ->leftJoin('mt_organization', 'tr_program.organization_id', 'mt_organization.id')
            ->whereNull('tr_program_realization.deleted_at')
            ->when($cond != '', function($q) use ($cond) {
                $q->whereRaw($cond);
            })
            ->select(
                'tr_program_realization.*',
                'tr_program.code as program_code',
                'tr_program.program as program_program',
                'tr_program.activity as program_activity',
                'tr_program.sub_activity as program_sub_activity',
                'tr_program.budget_allocation as program_budget_allocation',
                'tr_program.organization_id as program_organization_id',
                'mt_budget_year.name as budget_year_name',
                'mt_organization.name as organization_name',
                'program_goal.value as strategy_program_name',
                'district.name as district_name',
                'subdistrict.name as subdistrict_name',
                'updated_by.name as updated_by_name',
                'created_by_org.name as created_by_organization_name'
            );
    }
}
