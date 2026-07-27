<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Repositories\CompileRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ToolController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;

    public function __construct(
        CompileRepository $compile_repo
    )
    {
        $this->route_prefix = 'tool.';
        $this->compile_repo = $compile_repo;
    }

    public function program_list()
    {
        $data = [
            '_be_page_title' => 'Cek Program',
            '_be_page_title_desc' => 'Halaman ini adalah daftar semua Cek Program mengakses sistem',
            // '_be_insert' => route('system.import.insert')
        ];
        return view('backpage.tool.program.list',compact('data'));
    }

    // public function get_data(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = Sy_import::select(
    //             'sy_import.*',
    //             'sy_file.type as type'
    //         )
    //             ->leftJoin('sy_file', 'sy_import.file_id', 'sy_file.id')
    //             ->where('sy_import.is_sync', false);

    //         return DataTables::eloquent($data)
    //             // ->editColumn('data', function($data) {
    //             //     return substr($data->data, 0, 10) . ' ...';
    //             // })
    //             ->editColumn('created_at', function($data) {
    //                 return Carbon::parse($data->created_at)->format('Y-m-d H:i');
    //             })
    //             ->addIndexColumn()
    //             ->rawColumns(['created_at'])
    //             ->make(true);
    //     }
    // }
}
