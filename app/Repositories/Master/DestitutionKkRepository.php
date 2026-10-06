<?php

namespace App\Repositories\Master;

use App\Libraries\DtsenDocumentMapper;
use App\Models\Master\Mt_destitution_kk;

class DestitutionKkRepository
{
    protected $model;
    protected $table_name = 'mt_destitution_kk';

    public function __construct(Mt_destitution_kk $model)
    {
        $this->model = $model;
    }

    public function getRecord($id)
    {
        return $this->model
            ->where($this->table_name.'.id',$id)
            ->when(get_preference('default_percentile', 2) > 0, function ($q){
                $q->where($this->table_name.'.percentile', '<=', get_preference('default_percentile', 2));
            })
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
            ->when(get_preference('default_percentile', 2) > 0, function ($q){
                $q->where($this->table_name.'.percentile', '<=', get_preference('default_percentile', 2));
            })
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

    public function getTotalRecordsByColumn($column, $value, $condition = "1=1")
    {
        $records = $this->model
            ->where('data_id', source_data_active())
            ->whereRaw($condition)
            ->where($column, $value)
            ->when(get_preference('default_percentile', 2) > 0, function ($q){
                $q->where($this->table_name.'.percentile', '<=', get_preference('default_percentile', 2));
            })
            ->select(
                'id'
            )
            ->get();
        return $records->count();
    }

    public function getSumRecordsByColumn($column, $value, $condition = "1=1", $sum_column = "id")
    {
        $records = $this->model
            ->where('data_id', source_data_active())
            ->whereRaw($condition)
            ->where($column, $value)
            ->whereNull($this->table_name.'.deleted_at')
            ->when(get_preference('default_percentile', 2) > 0, function ($q){
                $q->where($this->table_name.'.percentile', '<=', get_preference('default_percentile', 2));
            })
            ->select(
                $this->table_name . '.*'
            )
            ->get();
        return $records->sum($sum_column);
    }

    /**
     * Kelompokkan langsung menurut satu kolom dokumen (desil, kecamatan,
     * pbi, jumlah anggota, …) tanpa lewat tabel pilihan sy_option.
     *
     * $column dan $sum_column wajib ada di daftar putih DtsenDocumentMapper:
     * nilainya ikut masuk SQL, jadi jangan pernah kirim mentah dari request.
     *
     * @return \Illuminate\Support\Collection baris {label, total}
     */
    public function getGroupedByColumn($column, $condition = "1=1", $sum_column = null)
    {
        $allowed = DtsenDocumentMapper::columns(DtsenDocumentMapper::TYPE_KK);
        if (!in_array($column, $allowed, true)) {
            throw new \InvalidArgumentException("Kolom grafik tidak dikenal: {$column}");
        }
        if ($sum_column !== null && !in_array($sum_column, $allowed, true)) {
            throw new \InvalidArgumentException("Kolom jumlah tidak dikenal: {$sum_column}");
        }

        $query = $this->model
            ->where($this->table_name.'.data_id', source_data_active())
            ->whereRaw($condition)
            ->whereNull($this->table_name.'.deleted_at')
            ->when(get_preference('default_percentile', 2) > 0, function ($q) {
                $q->where($this->table_name.'.percentile', '<=', get_preference('default_percentile', 2));
            });

        // Kolom wilayah tersimpan sebagai id; yang dibaca orang namanya.
        $is_region = in_array($column, ['province_id', 'regency_id', 'district_id', 'subdistrict_id'], true);
        if ($is_region) {
            $query->leftJoin('mt_region', $this->table_name.'.'.$column, 'mt_region.id');
        }

        $label = $is_region
            ? 'COALESCE(mt_region.name, "(tidak diketahui)")'
            : $this->table_name.'.'.$column;
        $total = $sum_column === null ? 'COUNT(*)' : 'SUM('.$this->table_name.'.'.$sum_column.')';

        // label untuk yang dibaca orang, value untuk menyaring baris (kolom
        // wilayah isinya id, bukan nama).
        return $query
            ->selectRaw("{$label} as label, {$this->table_name}.{$column} as value, {$total} as total")
            ->groupByRaw("{$label}, {$this->table_name}.{$column}")
            ->orderByRaw($label)
            ->get();
    }

    public function getTotalRecordsByCond($condition = "1=1")
    {
        $records = $this->model
            ->leftJoin('sy_option as option_stunting_risk', $this->table_name . '.stunting_risk_id', 'option_stunting_risk.id')
            ->where($this->table_name. '.data_id', source_data_active())
            ->whereRaw($condition)
            ->whereNull($this->table_name.'.deleted_at')
            ->when(get_preference('default_percentile', 2) > 0, function ($q){
                $q->where($this->table_name.'.percentile', '<=', get_preference('default_percentile', 2));
            })
            ->select(
                $this->table_name. '.id'
            )
            ->get();
        return $records->count();
    }
}
