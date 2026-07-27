<?php

namespace App\Repositories\Master;

use App\Models\User;

class UserRepository
{
    protected $model;
    protected $table_name = 'mt_user';

    public function __construct(User $model)
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
            // ->whereNull('deleted_at')
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

    public function updateRecordByReference($reference_table, $reference_id, $data)
    {
        $this->model
            ->where('reference_table', $reference_table)
            ->where('reference_id', $reference_id)
            ->update($data);
    }

    public function deleteRecordByReference($reference_table, $reference_id)
    {
        $this->model
            ->where('reference_table', $reference_table)
            ->where('reference_id', $reference_id)
            ->delete();
    }
}
