<?php

namespace App\Repositories\System;

use App\Models\System\Sy_preference;

class PreferenceRepository
{
    protected $model;
    protected $table_name = 'sy_preference';

    public function __construct(Sy_preference $model)
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
        $records = $this->model->whereNull('deleted_at')
            ->get();

        return $records->count();
    }

    public function getValueByPrefix($prefix, $column = null)
    {
        $record = $this->model
            ->where('key', $prefix)
            ->get()
            ->first();

        if ($record && $column != null) {
            return $record->$column;
        }

        return $record->value ?? '';
    }

    public function isPreferenceExist($menu_id, $key, $except_id = 0)
    {
        $records = $this->model->where('key', $key)
            ->when($except_id != 0, function($q) use ($except_id) {
                $q->where('id', '<>', $except_id);
            })
            ->whereNull('deleted_at')
            ->get();

        return $records->count() > 0 ? true : false;
    }
}
