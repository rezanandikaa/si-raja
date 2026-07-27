<?php

namespace App\Repositories\Master;

use App\Models\Master\Mt_organization;

class OrganizationRepository
{
    protected $model;
    protected $table_name = 'mt_organization';

    public function __construct(Mt_organization $model)
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

    public function getChildsOrganization($parent_id, $is_implode = false, $separator = ', ')
    {
        $results = [];
        $records = $this->model
            ->where($this->table_name.'.parent_id', $parent_id)
            ->select(
                $this->table_name.'.*'
            )
            ->get();
        if ($records->count() > 0) {
            foreach ($records as $record) {
                array_push($results, $record->id);
            }
        }
        return $is_implode ? implode($separator, $results) : $results;
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

    public function getRecords()
    {
        return $this->model
            ->where('active_flag', true)
            ->select(
                $this->table_name.'.*'
            )
            ->get();
    }
}
