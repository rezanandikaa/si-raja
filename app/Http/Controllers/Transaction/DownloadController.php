<?php

namespace App\Http\Controllers\Transaction;

use App\Exports\ProgramExport;
use App\Exports\RealizationExport;
use App\Http\Controllers\Controller;
use App\Repositories\CompileRepository;
use App\Repositories\Master\BudgetYearRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DownloadController extends Controller
{
    protected $compile_repo;
    protected $budget_year_repo;

    public function __construct(
        CompileRepository $compile_repo,
        BudgetYearRepository $budget_year_repo
    )
    {
        $this->compile_repo = $compile_repo;
        $this->budget_year_repo = $budget_year_repo;
    }

    public function list()
    {
        $data = [
            '_be_page_title' => 'Daftar Data Grafik',
            '_be_page_title_desc' => 'Halaman ini adalah semua daftar Data Grafik',
            '_be_breadcrumbs' => ['Data Grafik','Daftar Data Grafik'],
            '_be_insert' => route('dashboard.insert')
        ];
        return view('backpage.dashboard.list',compact('data'));
    }

    // public function get_data(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = Tr_dashboard::leftJoin('mt_user as updated_by','tr_dashboard.updated_by_id','updated_by.id')
    //             ->leftJoin('mt_dashboard','tr_dashboard.dashboard_id','mt_dashboard.id')
    //             ->whereNull('tr_dashboard.deleted_at')
    //             ->where('tr_dashboard.user_id', Auth::user()->id)
    //             ->select(
    //                 'tr_dashboard.*',
    //                 'mt_dashboard.title as dashboard_title',
    //                 'mt_dashboard.type as dashboard_type',
    //                 'updated_by.name as updated_by_name'
    //             );
    //         return DataTables::eloquent($data)
    //             ->editColumn('updated_at', function($data) {
    //                 return Carbon::parse($data->updated_at)->format('Y-m-d H:i');
    //             })
    //             ->editColumn('active_flag', function($data) {
    //                 return '<span class="badge light badge-'.($data->active_flag ? 'success':'danger').'">'.($data->active_flag ? 'Aktif':'Nonaktif').'</span>';
    //             })
    //             ->addIndexColumn()
    //             ->addColumn('action', function($data){
    //                 $btn = '<div class="btn-group">
    //                     <a href="'.route($this->route_prefix.'edit',$data->id).'" class="btn btn-sm btn-info"><i class="fa fa-pencil"></i></a>
    //                     <a data-id="delete-'.$data->id.'" data-url="'.route('dashboard.delete').'" class="delete btn btn-sm btn-danger"><i class="fa fa-trash"></i></a></div>
    //                 ';
    //                 return $btn;
    //             })
    //             ->rawColumns(['active_flag','action'])
    //             ->make(true);
    //     }
    // }

    public function get_form_program()
    {
        $fields = [];

        $fields['budget_year_id'] = [
            'label' => 'Tahun Anggaran',
            'name' => 'budget_year_id',
            'placeholder' => 'Tahun Anggaran',
            'type' => 'data',
            'data_table' => 'mt_budget_year',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Tahun Anggaran wajib diisi'
        ];

        return $fields;
    }

    public function get_form_realization()
    {
        $fields = [];

        $fields['budget_year_id'] = [
            'label' => 'Tahun Anggaran',
            'name' => 'budget_year_id',
            'placeholder' => 'Tahun Anggaran',
            'type' => 'data',
            'data_table' => 'mt_budget_year',
            'required' => true,
            'show_only' => false,
            'validate_message' => 'Tahun Anggaran wajib diisi'
        ];

        return $fields;
    }

    public function report($report_name)
    {
        $data = [];

        switch ($report_name) {
            case 'program':
                $fields = $this->get_form_program();
                $title = 'Laporan Program Kegiatan';
                $action = route('download.progress', ['report_name' => $report_name]);
                break;
            case 'realization':
                $fields = $this->get_form_realization();
                $title = 'Laporan Realisasi Kegiatan';
                $action = route('download.progress', ['report_name' => $report_name]);
                break;

            default:
                return redirect('home');
                break;
        }

        $data = [
            'fields' => $fields,
            'datas' => [],
            '_be_page_title' => $title,
            '_be_page_title_desc' => 'Halaman ini untuk unduh '.$title,
            '_be_breadcrumbs' => ['Data Grafik',$title],
            '_be_card_title' => $title,
            '_be_btn_label' => 'Unduh',
            '_be_btn_variant' => 'success',
            '_be_method' => 'POST',
            '_be_action' => $action,
            '_be_home' => route('home')
        ];
        $this->compile_repo->make($data);

        return view('backpage.reports.form', compact('data'));
    }

    public function progress(Request $request)
    {
        $report_name = $request->report_name ?? '';
        switch ($report_name) {
            case 'program':
                $budget_year_id = $request->budget_year_id;
                $budget_year = $this->budget_year_repo->getRecord($budget_year_id);
                $file_name = "Laporan Realisasi.xlsx";
                if ($budget_year){
                    $file_name = "Laporan Program {$budget_year->name}.xlsx";
                }
                return Excel::download(new ProgramExport($budget_year_id), $file_name, \Maatwebsite\Excel\Excel::XLSX, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
                break;
            case 'realization':
                $budget_year_id = $request->budget_year_id;
                $budget_year = $this->budget_year_repo->getRecord($budget_year_id);
                $file_name = "Laporan Realisasi.xlsx";
                if ($budget_year){
                    $file_name = "Laporan Realisasi {$budget_year->name}.xlsx";
                }
                return Excel::download(new RealizationExport($budget_year_id), $file_name, \Maatwebsite\Excel\Excel::XLSX, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]);
                break;

            default:
                # code...
                break;
        }

        // $export = new RealizationExport();
        // $filename = 'Laporan Realisasi.xlsx';

        // // Simpan file sementara
        // $tempFilePath = storage_path('app/public/temp/' . $filename);
        // Excel::store($export, 'public/temp/'.$filename);

        // // Buat StreamedResponse untuk mengirimkan file sebagai respons
        // $response = new StreamedResponse(function () use ($tempFilePath) {
        //     $file = fopen($tempFilePath, 'rb');
        //     fpassthru($file);
        //     fclose($file);
        //     unlink($tempFilePath); // Hapus file setelah di-download
        // });

        // // Set headers untuk download
        // $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        // return $response;
    }
}
