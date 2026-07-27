<?php

namespace App\Repositories\System;

use App\Models\System\Sy_attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentRepository
{
    protected $model;
    protected $table_name = 'sy_attachment';

    public function __construct(Sy_attachment $model)
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
        $record = $this->getRecord($id);
        if ($record) {
            $path = str_replace('storage/','',$record->path);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $this->model->destroy($id);
    }

    public function getTotalRecords()
    {
        $records = $this->model
            ->get();

        return $records->count();
    }
}
