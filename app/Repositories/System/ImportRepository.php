<?php

namespace App\Repositories\System;

use App\Models\System\Sy_import;

class ImportRepository
{
    protected $model;
    protected $table_name = 'sy_import';

    public function __construct(Sy_import $model)
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
            // ->whereNull($this->table_name.'.deleted_at')
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
        $records = $this->model->get();
        return $records->count();
    }
}
