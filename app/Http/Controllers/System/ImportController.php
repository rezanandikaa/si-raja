<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Sy_import;
use App\Repositories\CompileRepository;
use App\Repositories\System\ImportRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ImportController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $import_repo;

    public function __construct(
        CompileRepository $compile_repo,
        ImportRepository $import_repo
    )
    {
        $this->route_prefix = 'system.import.';
        $this->compile_repo = $compile_repo;
        $this->import_repo = $import_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Log Import',
            '_be_page_title_desc' => 'Halaman ini adalah daftar semua Log Import mengakses sistem',
            // '_be_insert' => route('system.import.insert')
        ];
        return view('backpage.import.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Sy_import::select(
                'sy_import.*',
                'sy_file.type as type'
            )
                ->leftJoin('sy_file', 'sy_import.file_id', 'sy_file.id')
                ->where('sy_import.is_sync', false);

            return DataTables::eloquent($data)
                // ->editColumn('data', function($data) {
                //     return substr($data->data, 0, 10) . ' ...';
                // })
                ->editColumn('created_at', function($data) {
                    return Carbon::parse($data->created_at)->format('Y-m-d H:i');
                })
                ->addIndexColumn()
                ->rawColumns(['created_at'])
                ->make(true);
        }
    }
}
