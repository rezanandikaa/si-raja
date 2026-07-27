@extends('baduyengine.app')

@section('vendor-js')
<!-- Dependensi Highcharts -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script>
    $(document).ready(function() {
        // Data untuk chart pie
        var data = [
            ['Chrome', 45.0],
            ['Firefox', 26.8],
            ['Edge', 12.8],
            ['Safari', 8.5],
            ['Opera', 6.2],
            ['Lainnya', 0.7]
        ];

        // Membuat chart pie
        Highcharts.chart('#chartpie', {
            chart: {
                type: 'pie'
            },
            title: {
                text: 'Contoh Chart Pie'
            },
            series: [{
                name: 'Persentase',
                data: data
            }]
        });
    });
</script>
@endsection

@section('content')
<!-- mani page content body part -->
<div id="main-content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>Page Blank</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html"><i class="fa fa-dashboard"></i></a></li>
                        <li class="breadcrumb-item">Extra</li>
                        <li class="breadcrumb-item active">Page Blank</li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Download report</button>
                            <button type="button" class="btn btn-secondary"><i class="fa fa-send"></i> Send report</button>
                        </div>
                        <div class="p-2 d-flex">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row clearfix row-deck">
            <div class="col-lg-4 col-md-12">
                <div class="card">
                    <div class="header">
                        <h2>Use by Device</h2>
                        <ul class="header-dropdown">
                            <li class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"></a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li><a href="javascript:void(0);">Refresh</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="body">
                        <div id="chartpie" style="height: 16rem"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
