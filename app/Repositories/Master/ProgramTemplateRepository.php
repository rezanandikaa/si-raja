<?php

namespace App\Repositories\Master;

use App\Models\Master\Mt_program_template;

class ProgramTemplateRepository
{
    protected $model;
    protected $table_name = 'mt_program_template';

    public function __construct(Mt_program_template $model)
    {
        $this->model = $model;
    }

    public function getRecord($id, $type = null)
    {
        return $this->model
            ->when($type != null, function ($q) use ($type) {
                $q->where($this->table_name . '.type', $type);
            })
            ->where($this->table_name . '.id', $id)
            ->select(
                $this->table_name . '.*'
            )
            ->get()
            ->first();
    }

    public function getRecordBy($column, $value)
    {
        return $this->model
            ->where($this->table_name . '.' . $column, $value)
            ->select(
                $this->table_name . '.*'
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

    public function getRecordByCode($code, $budget_year_id = 0)
    {
        return $this->model
            ->where($this->table_name . '.code', $code)
            ->when($budget_year_id != 0, function ($q) use ($budget_year_id) {
                $q->where($this->table_name . '.budget_year_id', $budget_year_id);
            })
            ->select(
                $this->table_name . '.*'
            )
            ->first();
    }

    public function getValueBySubActivityCode($code, $type, $column, $budget_year_id)
    {
        switch ($type) {
            case 'activity':
                $code = substr($code, 0, 12);
                break;
            case 'program':
                $code = substr($code, 0, 7);
                break;

            default:
                # code...
                break;
        }

        $record = $this->getRecordByCode($code, $budget_year_id);
        if ($record) {
            return $record->$column ?? '';
        }
        return '';
    }

    public function getRecords($cond)
    {
        return $this->model
            ->whereRaw($cond)
            ->whereNull('mt_program_template.deleted_at')
            ->select(
                $this->table_name . '.*'
            )
            ->get();
    }
}
