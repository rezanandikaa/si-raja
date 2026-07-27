<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction\Tr_gallery;

class GalleryRepository
{
    protected $model;
    protected $table_name = 'tr_gallery';

    public function __construct(Tr_gallery $model)
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
            ->whereNull($this->table_name.'.deleted_at')
            ->select(
                $this->table_name.'.*'
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
            ->delete();
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
}
