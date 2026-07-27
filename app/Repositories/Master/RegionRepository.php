<?php

namespace App\Repositories\Master;

use App\Models\Master\Mt_region;
use Illuminate\Support\Facades\Log;

class RegionRepository
{
    protected $model;
    protected $table_name = 'mt_region';

    public function __construct(Mt_region $model)
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

    public function getRecordByColumn($column, $value, $to_upper = true)
    {
        return $this->model
            ->where($this->table_name.'.'.$column, ($to_upper ? strtoupper($value) : $value))
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.*'
            )
            ->get()
            ->first();
    }

    public function getRecordsByType($type)
    {
        return $this->model
            ->where($this->table_name.'.type', $type)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.*'
            )
            ->get();
    }

    public function getRecords($condition)
    {
        return $this->model
            ->whereRaw($condition)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.*'
            )
            ->get();
    }
}
