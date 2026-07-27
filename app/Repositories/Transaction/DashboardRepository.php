<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction\Tr_dashboard;

class DashboardRepository
{
    protected $model;
    protected $table_name = 'tr_dashboard';

    public function __construct(Tr_dashboard $model)
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

    public function deleteRecordByDashboardId($dashboard_id)
    {
        $this->model->where('dashboard_id', $dashboard_id)
            ->delete();
    }

    public function getRecords($cond)
    {
        return $this->model
            ->leftJoin('mt_dashboard', 'tr_dashboard.dashboard_id', 'mt_dashboard.id')
            ->whereRaw($cond)
            ->where('tr_dashboard.active_flag', true)
            ->select(
                $this->table_name.'.*',
                'mt_dashboard.active_flag as status',
                'mt_dashboard.type as type',
                'mt_dashboard.title as title'
            )
            ->orderBy('tr_dashboard.order', 'asc')
            ->get();
    }
}
