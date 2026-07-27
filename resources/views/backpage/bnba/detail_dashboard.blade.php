@extends('baduyengine.app')

@section('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/charts-c3/plugin.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/fixedeader/dataTables.fixedcolumns.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/fixedeader/dataTables.fixedheader.bootstrap4.min.css') }}">
<style>
    #loading-screen {
        position: relative;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #loading-screen img {
        width: 80px;
        /* Sesuaikan ukuran gambar */
        height: 80px;
    }
</style>
@endsection

@section('vendor-js')
<!-- Dependensi Highcharts -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/maps/modules/map.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>

<script src="{{ asset('assets/bundles/datatablescripts.bundle.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.print.min.js') }}"></script>

<script src="{{ asset('assets/bundles/c3.bundle.js') }}"></script>
@if ($data['_be_bnba_flag'])
<script>
    $(document).ready(function() {
        let url = '#';
        var table = $('.js-basic-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ $data['_datatable_route'] }}",
                type: "POST", // Menggunakan metode POST
                data: {
                    limit_org: 0
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: {!! json_encode($data['_datatable_headers']) !!},
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            // dom: 'Bfrtip',
            dom: 'Bfrtlip',
            buttons: [{
                    text: '<i class="fa fa-refresh"></i> Refresh',
                    action: function(e, dt, node, config) {
                        table.ajax.reload();
                    },
                    className: 'btn-primary',
                },
            ],
            columnDefs: [{
                render: function(data, type, full, meta) {
                    return "<div class='text-wrap'>" + (data || '-') + "</div>";
                },
                targets: '_all'
            }],
            order: [
                [1, "desc"]
            ]
        });

        $('#action_total').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                limit_org: 0
            };
            settings[0].ajax.data = newData;
            swal({
                title: "Konfirmasi!",
                text: "Tampilkan informasi tersebut ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#59c4bc",
                confirmButtonText: "Ya",
                cancelButtonText: "Batal",
                closeOnConfirm: false,
                closeOnCancel: false
            }, function(isConfirm) {
                if (isConfirm) {
                    swal({
                        title: 'Memuat Data',
                        text: 'Proses pengambilan data sedang berlangsung...',
                        allowOutsideClick: false,
                        showCancelButton: false,
                        showConfirmButton: false
                    });
                    table.ajax.reload(function() {
                        swal("Berhasil", "Data tersaji", "success");
                    });
                } else {
                    swal("Dibatalkan", "Dikembalikan ke semula", "error");
                }
            });
        });


        @foreach($data['options'] as $key => $item)
        $('#action_{{$key + 1}}').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                limit_org: 0,
                condition: '{{ $data["_datatable_column"] }} = "{{ $item["id"] }}"'
            };
            settings[0].ajax.data = newData;
            swal({
                title: "Konfirmasi!",
                text: "Tampilkan informasi tersebut ?",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#59c4bc",
                confirmButtonText: "Ya",
                cancelButtonText: "Batal",
                closeOnConfirm: false,
                closeOnCancel: false
            }, function(isConfirm) {
                if (isConfirm) {
                    swal({
                        title: 'Memuat Data',
                        text: 'Proses pengambilan data sedang berlangsung...',
                        allowOutsideClick: false,
                        showCancelButton: false,
                        showConfirmButton: false
                    });
                    table.ajax.reload(function() {
                        swal("Berhasil", "Data tersaji", "success");
                    });
                } else {
                    swal("Dibatalkan", "Dikembalikan ke semula", "error");
                }
            });
        });
        @endforeach
    });
</script>
@endif
@endsection

@section('content')
<!-- mani page content body part -->
<div id="main-content">
    <div class="container-fluid">
        <div class="block-header">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <h2>{{ $data['_be_page_subtitle'] }}</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i></a></li>
                        <li class="breadcrumb-item">{{ $data['_be_page_title'] }}</li>
                        <li class="breadcrumb-item active">{{ $data['_be_page_subtitle'] }}</li>
                    </ul>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <a href="{{ route('home') }}" class="btn btn-danger"><i class="fa-solid fa-chevron-left"></i> Kembali</a>
                        </div>
                        <div class="p-2 d-flex">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DASHBOARD --}}
        <div class="row clearfix row-deck">
            <div id="action_total" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        {{-- <div class="icon"><i class="fa fa-user"></i> </div> --}}
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Total</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total'] }}</h4>
                            <small class="text-muted">Terdata pada Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            @foreach ($data['options'] as $key => $item)
            <div id="action_{{ $key + 1}}" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        {{-- <div class="icon"><i class="fa-solid fa-plate-wheat"></i></div> --}}
                        <div class="content">
                            <div class="text mb-2 text-uppercase">{{ $item['value'] }}</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total_'. $key + 1] }}</h4>
                            <small class="text-muted">Terdata pada sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if ($data['_be_bnba_flag'])
        {{-- BNBA LIST --}}
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>{{ $data['_be_page_title']}}<small>{{ $data['_be_page_subtitle']}} </small> </h2>
                    </div>
                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom dataTable js-basic-datatable">
                                <thead>
                                    <tr>
                                        @foreach ($data['_datatable_headers'] as $item)
                                        <th>{{$item['label']}}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
