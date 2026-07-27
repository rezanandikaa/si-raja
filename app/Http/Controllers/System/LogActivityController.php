<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\Sy_log_activity;
use App\Repositories\CompileRepository;
use App\Repositories\System\LogActivityRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LogActivityController extends Controller
{
    protected $route_prefix;
    protected $compile_repo;
    protected $log_activity_repo;

    public function __construct(
        CompileRepository $compile_repo,
        LogActivityRepository $log_activity_repo
    )
    {
        $this->route_prefix = 'system.log_activity.';
        $this->compile_repo = $compile_repo;
        $this->log_activity_repo = $log_activity_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Riwayat Aktivitas',
            '_be_page_title_desc' => 'Halaman ini adalah daftar semua riwayat aktivitas mengakses sistem',
            '_be_breadcrumbs' => ['Log Aktivitas','Riwayat Aktivitas'],
            // '_be_insert' => route('system.log_activity.insert')
        ];
        return view('backpage.log_activity.list',compact('data'));
    }

    public function get_data(Request $request)
    {
        if ($request->ajax()) {
            $data = Sy_log_activity::select(
                    'sy_log_activity.*',
                    'mt_user.name'
                )
                ->leftJoin('mt_user as mt_user','sy_log_activity.user_id','mt_user.id')
                ->latest('sy_log_activity.activity_date');
            return DataTables::eloquent($data)
                ->editColumn('activity_date', function($data) {
                    return $data->activity_date;
                })
                ->editColumn('ip_address', function($data) {
                    return '<span class="badge light badge-info">'.$data->ip_address.'</span>';
                })
                ->editColumn('messages', function($data) {
                    $message = str_replace('Code 0:', '<span class="badge badge-sm light badge-warning">0 Error</span>', $data->messages);
                    $message = str_replace('Code 23000:', '<span class="badge badge-sm light badge-warning">23000 Error</span>', $message);
                    $message = str_replace('Code 500:', '<span class="badge badge-sm light badge-danger">500 Error</span>', $message);
                    $message = str_replace('Code 501:', '<span class="badge badge-sm light badge-danger">501 Error</span>', $message);
                    $message = str_replace('Code 200:', '<span class="badge badge-sm light badge-success">200 OK</span>', $message);
                    $message = str_replace('Code 201:', '<span class="badge badge-sm light badge-success">201 Created</span>', $message);
                    return $message;
                })
                ->addIndexColumn()
                ->rawColumns(['activity_date','ip_address', 'messages'])
                ->make(true);
        }
    }
}
