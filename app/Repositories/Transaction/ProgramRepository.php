<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction\Tr_program;
use App\Models\Transaction\Tr_program_budget;

class ProgramRepository
{
    protected $model;
    protected $program_budget;
    protected $table_name = 'tr_program';

    public function __construct(Tr_program $model, Tr_program_budget $program_budget)
    {
        $this->model = $model;
        $this->program_budget = $program_budget;
    }

    public function getRecord($id)
    {
        return $this->model
            ->leftJoin('mt_organization', $this->table_name.'.organization_id', 'mt_organization.id')
            ->leftJoin('mt_budget_year', $this->table_name.'.budget_year_id', 'mt_budget_year.id')
            ->leftJoin('mt_region as district', $this->table_name.'.district_id', 'district.id')
            ->leftJoin('mt_region as subdistrict', $this->table_name.'.subdistrict_id', 'subdistrict.id')
            ->leftJoin('mt_budget_source', $this->table_name.'.budget_source_id', 'mt_budget_source.id')
            ->leftJoin('sy_option as program_goal', $this->table_name.'.program_goal_id', 'program_goal.id')
            ->where($this->table_name.'.id',$id)
            ->select(
                $this->table_name.'.*',
                'mt_budget_year.name as budget_year_name',
                'mt_organization.name as organization_name',
                'mt_organization.parent_id as organization_parent_id',
                'district.name as district_name',
                'subdistrict.name as subdistrict_name'
            )
            ->get()
            ->first();
    }

    public function getDataTable($cond)
    {
        return $this->model
            ->leftJoin('mt_organization', $this->table_name.'.organization_id', 'mt_organization.id')
            ->leftJoin('mt_budget_year', $this->table_name.'.budget_year_id', 'mt_budget_year.id')
            ->leftJoin('mt_region as district', $this->table_name.'.district_id', 'district.id')
            ->leftJoin('mt_region as subdistrict', $this->table_name.'.subdistrict_id', 'subdistrict.id')
            ->leftJoin('mt_budget_source', $this->table_name.'.budget_source_id', 'mt_budget_source.id')
            ->leftJoin('sy_option as program_goal', $this->table_name.'.program_goal_id', 'program_goal.id')
            ->when($cond != '', function($q) use ($cond) {
                $q->whereRaw($cond);
            })
            ->select(
                $this->table_name.'.*',
                'mt_budget_year.name as budget_year_name',
                'mt_organization.name as organization_name',
                'mt_organization.parent_id as organization_parent_id',
                'district.name as district_name',
                'subdistrict.name as subdistrict_name'
            );
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
            ->leftJoin('mt_user as created_by',$this->table_name.'.created_by_id','created_by.id')
            ->leftJoin('mt_organization as created_by_org', 'created_by.organization_id', 'created_by_org.id')
            ->leftJoin('mt_organization', $this->table_name.'.organization_id', 'mt_organization.id')
            ->leftJoin('mt_budget_year', $this->table_name.'.budget_year_id', 'mt_budget_year.id')
            ->leftJoin('mt_region as district', $this->table_name.'.district_id', 'district.id')
            ->leftJoin('mt_region as subdistrict', $this->table_name.'.subdistrict_id', 'subdistrict.id')
            ->leftJoin('mt_budget_source', $this->table_name.'.budget_source_id', 'mt_budget_source.id')
            ->leftJoin('sy_option as program_goal', $this->table_name.'.program_goal_id', 'program_goal.id')
            ->whereRaw($condition)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.*',
                'mt_budget_year.name as budget_year_name',
                'mt_organization.name as organization_name',
                'district.name as district_name',
                'subdistrict.name as subdistrict_name',
                'created_by_org.name as created_by_organization_name'
            )
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
            ->update(['status' => 'DROPPED']);
    }

    public function getRecords($cond)
    {
        return $this->model
            ->whereRaw($cond)
            ->select(
                $this->table_name.'.*'
            )
            ->get();
    }

    public function getTotalRecordsByColumn($column, $value, $condition = "1=1")
    {
        $records = $this->model
            ->leftJoin('mt_region as subdistrict', $this->table_name.'.subdistrict_id', 'subdistrict.id')
            ->whereRaw($condition)
            ->where($column, $value)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.id'
            )
            ->get();
        return $records->count();
    }

    public function getSumRecordsByColumn($column, $value, $condition = "1=1", $sum_column = "id")
    {
        $records = $this->model
            ->whereRaw($condition)
            ->where($column, $value)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name . '.*'
            )
            ->get();
        return $records->sum($sum_column);
    }

    public function getTotalRecordsByCond($condition = "1=1")
    {
        $records = $this->model
            ->leftJoin('mt_region as subdistrict', $this->table_name.'.subdistrict_id', 'subdistrict.id')
            ->whereRaw($condition)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.id'
            )
            ->get();
        return $records->count();
    }

    public function getRecordBudget($id)
    {
        return $this->program_budget
            ->leftJoin($this->table_name, $this->table_name.'_budget.program_id', $this->table_name.'.id')
            ->where($this->table_name.'_budget.id',$id)
            ->select(
                $this->table_name.'.status',
                $this->table_name.'_budget.*'
            )
            ->get()
            ->first();
    }

    public function insertRecordBudget($data)
    {
        $record = $this->program_budget->create($data);
        return $record->id;
    }

    public function updateRecordBudget($id, $data)
    {
        $this->program_budget->find($id)
            ->update($data);
    }

    public function deleteRecordBudget($id)
    {
        $this->program_budget->destroy($id);
    }

    public function getRecordsBudget($cond)
    {
        return $this->program_budget
            ->leftJoin('tr_program', 'tr_program_budget.program_id', 'tr_program.id')
            ->whereRaw($cond)
            ->whereNull('tr_program_budget.deleted_at')
            ->select(
                $this->table_name.'_budget.*'
            )
            ->get();
    }

    public function updateRecordByCond($cond, $data)
    {
        $this->model->whereRaw($cond)
            ->update($data);
    }
}
