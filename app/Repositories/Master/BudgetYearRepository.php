<?php

namespace App\Repositories\Master;

use App\Models\Master\Mt_budget_year;

class BudgetYearRepository
{
    protected $model;
    protected $table_name = 'mt_budget_year';

    public function __construct(Mt_budget_year $model)
    {
        $this->model = $model;
    }

    public function getRecord($id)
    {
        return $this->model
            ->where($this->table_name.'.id',$id)
            ->select(
                $this->table_name.'.*'
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
        $this->model->destroy($id);
    }

    public function getTotalRecordsByColumn($column, $value, $condition = "1=1")
    {
        $records = $this->model
            ->where('data_id', source_data_active())
            ->whereRaw($condition)
            ->where($column, $value)
            ->select(
                'id'
            )
            ->get();
        return $records->count();
    }
}
