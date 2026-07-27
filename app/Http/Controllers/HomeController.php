<?php

namespace App\Http\Controllers;

use App\Repositories\CompileRepository;
use App\Repositories\Transaction\DashboardRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    protected $dashboard_repo;
    protected $compile_repo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(DashboardRepository $dashboard_repo,
        CompileRepository $compile_repo)
    {
        // $this->middleware('auth');
        $this->dashboard_repo = $dashboard_repo;
        $this->compile_repo = $compile_repo;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $charts = [];
        $user_id = Auth::user()->id;
        $cond = "tr_dashboard.user_id = '{$user_id}'";
        $records = $this->dashboard_repo->getRecords($cond);
        if ($records->count() > 0) {
            $charts = $records->toArray();
        }

        $budget_years = $this->compile_repo->getOptions('mt_budget_year', '');

        $data = [
            '_be_page_title' => 'Dashboard',
            '_be_page_title_desc' => 'Halaman ini adalah daftar semua Dashboard',
            // '_be_insert' => route('system.file.insert')
            '_be_home' => route('home'),
            'charts' => $charts,
            'budget_years' => $budget_years
        ];

        return view('backpage.dashboard.chart', compact('data'));
    }
}
