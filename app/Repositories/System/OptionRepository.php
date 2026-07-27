<?php

namespace App\Repositories\System;

use App\Models\System\Sy_option;

class OptionRepository
{
    protected $model;
    protected $table_name = 'sy_option';

    public function __construct(Sy_option $model)
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

    public function getOptionRecords($condition, $except = [])
    {
        return $this->model
            ->whereRaw($condition)
            ->whereNull($this->table_name.'.deleted_at')
            ->whereNotIn($this->table_name.'.id', $except)
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

    public function getTotalRecords()
    {
        $records = $this->model
            ->get();

        return $records->count();
    }

    public function getRecordsByCode($code)
    {
        return $this->model
            ->where($this->table_name.'.code', $code)
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.*'
            )
            ->get();
    }

    public function getRecordByColumn($column, $value, $code = null, $to_upper = true)
    {
        return $this->model
            ->when($code != null, function ($q) use ($code) {
                $q->where($this->table_name.'.code', $code);
            })
            ->where($this->table_name.'.'.$column, ($to_upper ? strtoupper($value) : $value))
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.*'
            )
            ->get()
            ->first();
    }

    public function getCustomOptionRecords($condition, $except = [])
    {
        $records = $this->model
            ->whereRaw($condition)
            ->whereNull($this->table_name.'.deleted_at')
            ->whereNotIn($this->table_name.'.id', $except)
            ->select(
                $this->table_name.'.*'
            )
            ->groupByRaw($this->table_name.'.code')
            ->get();

        $datas = [];
        foreach ($records as $record) {
            $data = ['id' => $record->code, 'label' => $record->label];
            array_push($datas, $data);
        }
        return $datas;
    }
}
