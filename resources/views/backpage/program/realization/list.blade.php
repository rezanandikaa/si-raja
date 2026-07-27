@php
$datatable = [];

$datatable['json_data'] = [
    ["data" => "budget_year_name", "name" => "mt_budget_year.name"],
    ["data" => "program_code", "name" => "tr_program.code"],
    ['data' => "district_name", 'name' => "district.name"],
    ['data' => "subdistrict_name", 'name' => "subdistrict.name"],
    ['data' => "strategy_program_name", 'name' => "program_goal.value"],
    // ['data' => "organization_name", 'name' => "mt_organization.name"],
    ['data' => "created_by_organization_name", 'name' => "created_by_org.name"],
    ["data" => "program_program", "name" => "tr_program.program"],
    ["data" => "program_activity", "name" => "tr_program.activity"],
    ["data" => "program_sub_activity", "name" => "tr_program.sub_activity"],
    ["data" => "quarterly", "name" => "tr_program_realization.quarterly"],
    ["data" => "program_budget_allocation", "name" => "tr_program.budget_allocation"],
    ["data" => "budget_realization", "name" => "tr_program.budget_realization"],
    ["data" => "percentage", "name" => "percentage"],
    ["data" => "target", "name" => "tr_program_realization.target"],
    ["data" => "implementation_obstacle", "name" => "tr_program_realization.implementation_obstacle"],
    ["data" => "benefit", "name" => "tr_program_realization.benefit"],
    ["data" => "duration_note", "name" => "tr_program_realization.duration_note"],
    ["data" => "updated_by_name", "name" => "updated_by.name"],
    ["data" => "updated_at", "name" => "tr_program.updated_at"],
    ["data" => "action", "name" => "action", "orderable" => false, "searchable" => false],
];

$datatable['route_data'] = route('program.realization.get_data', ['source' => 'default']);
$datatable['route_insert'] = route('program.realization.insert');

@endphp

@extends('baduyengine.app')

@section('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/fixedeader/dataTables.fixedcolumns.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/fixedeader/dataTables.fixedheader.bootstrap4.min.css') }}">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
@endsection

@section('vendor-js')
<script src="{{ asset('assets/bundles/datatablescripts.bundle.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.print.min.js') }}"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

{{-- @include('baduyengine.component.datatable', $datatable) --}}
<script>
    $(document).ready(function(){
    'use strict'

    var table = $('.js-basic-datatable').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        language: {
            processing: "Sedang memuat data ..."
        },
        ajax: {
            url: "{{ $datatable['route_data'] }}",
            type: "POST", // Menggunakan metode POST
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        // ajax: "{{ $datatable['route_data'] }}",
        columns: {!! json_encode($datatable['json_data']) !!},
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        dom: 'Bfrtlip',
        buttons: [
            @if (isset($datatable['route_back']))
            {
                text: '<i class="fa fa-arrow-left"></i> Kembali',
                className: 'btn-danger',
                action: function ( e, dt, node, config ) {
                    window.open("{{ $datatable['route_back'] ?? '#' }}", "_self");
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            @endif
            {
                text: '<i class="fa fa-refresh"></i> Semua',
                action: function ( e, dt, node, config ) {
                    var settings = table.settings();
                    var newData = {
                        condition: ''
                    };
                    settings[0].ajax.data = newData;
                    table.ajax.reload();
                },
                className: 'btn-primary',
            },
            {
                text: 'Triwulan I',
                className: 'btn-info',
                action: function ( e, dt, node, config ) {
                    var settings = table.settings();
                    var newData = {
                        condition: ' and tr_program_realization.quarterly = 1'
                    };
                    settings[0].ajax.data = newData;
                    table.ajax.reload();
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            {
                text: 'Triwulan II',
                className: 'btn-info',
                action: function ( e, dt, node, config ) {
                    var settings = table.settings();
                    var newData = {
                        condition: ' and tr_program_realization.quarterly = 2'
                    };
                    settings[0].ajax.data = newData;
                    table.ajax.reload();
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            {
                text: 'Triwulan III',
                className: 'btn-info',
                action: function ( e, dt, node, config ) {
                    var settings = table.settings();
                    var newData = {
                        condition: ' and tr_program_realization.quarterly = 3'
                    };
                    settings[0].ajax.data = newData;
                    table.ajax.reload();
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            {
                text: 'Triwulan IV',
                className: 'btn-info',
                action: function ( e, dt, node, config ) {
                    var settings = table.settings();
                    var newData = {
                        condition: ' and tr_program_realization.quarterly = 4'
                    };
                    settings[0].ajax.data = newData;
                    table.ajax.reload();
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            {
                text: 'Buat Baru',
                className: 'btn-secondary',
                action: function ( e, dt, node, config ) {
                    window.open("{{ $datatable['route_insert'] ?? '#' }}", "_self");
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
        ],
        columnDefs: [
            {
                render: function (data, type, full, meta) {
                    return "<div class='text-wrap'>" + (data || '-') + "</div>";
                },
                targets: '_all'
            }
        ],
        order: [[ 0, "desc" ]]
    });

    $('.js-basic-datatable tbody').on('click', 'a.delete', function () {
        let id = $(this).attr('data-id');
        let url_delete = $(this).attr('data-url');
        id = id.split("-");
        $(this).attr('disabled','disabled');
        swal({
            title: "Hapus Data!",
            text: "Anda yakin akan menghapus data ini ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            confirmButtonText: "Hapus",
            cancelButtonText: "Kembali",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type:"DELETE",
                    url: url_delete,
                    data: {id: id[1]},
                    success: function(response){
                        if(response.status === 'OK'){
                            swal("Berhasil", response.message, "success");
                            table.ajax.reload();
                        } else {
                            swal("Terjadi Kesalahan", response.message, "error");
                        }
                        $(this).removeAttr('disabled');
                    },
                    statusCode: {
                        500: function() {
                            swal("Terjadi Kesalahan", 'Kesalahan sistem 500', "error");
                            $(this).removeAttr('disabled');
                        }
                    }
                });
            } else {
                swal("Dibatalkan", "Data tidak dihapus", "error");
            }
        });
    });
});
</script>
<script>
$(document).ready(function () {
    $('.js-basic-datatable tbody').on('click', 'a.confirm', function () {
        let id = $(this).attr('data-id');
        let url = $(this).attr('data-url');
        id = id.split("-");
        $(this).attr('disabled','disabled');
        swal({
            title: "Konfirmasi!",
            text: "Anda yakin mengubah dokumen ini menjadi ACTIVE ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#59c4bc",
            confirmButtonText: "Ya",
            cancelButtonText: "Kembali",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type:"POST",
                    url: url,
                    data: {id: id[1]},
                    success: function(response){
                        if(response.status === 'OK'){
                            swal("Berhasil", response.message, "success");
                            window.location.href = "{{ route('program.list') }}";
                        } else {
                            swal("Terjadi Kesalahan", response.message, "error");
                        }
                        $(this).removeAttr('disabled');
                    },
                    statusCode: {
                        500: function() {
                            swal("Terjadi Kesalahan", 'Kesalahan sistem 500', "error");
                            $(this).removeAttr('disabled');
                        }
                    }
                });
            } else {
                swal("Dibatalkan", "Data dikembalikan seperti semula", "error");
            }
        });
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
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <h2>{{ $data['_be_page_title'] }}</h2>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fa fa-program"></i></a></li>
                        <li class="breadcrumb-item">Program</li>
                        <li class="breadcrumb-item active">{{ $data['_be_page_title'] }}</li>
                    </ul>
                </div>
                {{-- <div class="col-lg-6 col-md-6 col-sm-12">
                    <div class="d-flex flex-row-reverse">
                        <div class="page_action">
                            <button type="button" class="btn btn-primary"><i class="fa fa-download"></i> Download report</button>
                            <button type="button" class="btn btn-secondary"><i class="fa fa-send"></i> Send report</button>
                        </div>
                        <div class="p-2 d-flex">

                        </div>
                    </div>
                </div> --}}
            </div>
        </div>

        @if (count($data['summary_data']) > 0)
        <div class="row clearfix row-deck">
            @foreach ($data['summary_data'] as $key => $item)
            <div id="action_{{ $key + 1}}" class="{{ $item['class'] }}">
                <div class="card top_widget">
                    <div class="body">
                        <div class="content">
                            <div class="text mb-2 text-uppercase">{{ $item['title'] }}</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $item['value'] }}</h4>
                            <small class="text-muted">{{ $item['description'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>{{ $data['_be_page_title']}}<small>{{ $data['_be_page_title_desc']}} </small> </h2>
                    </div>
                    <div class="body">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom dataTable js-basic-datatable" width="100%">
                                <thead>
                                    <tr>
                                        <th>Tahun Anggaran</th>
                                        <th>Nomenklatur</th>
                                        <th>Kecamatan</th>
                                        <th>Desa/Kelurahan</th>
                                        <th>Strategi</th>
                                        <th>Perangkat Daerah</th>
                                        <th>Program</th>
                                        <th>Kegiatan</th>
                                        <th>Sub Kegiatan</th>
                                        <th>Triwulan</th>
                                        <th>Pagu</th>
                                        <th>Realisasi</th>
                                        <th>Persentase</th>
                                        <th>Sasaran Penerima Manfaat</th>
                                        <th>Kendala Pelaksanaan</th>
                                        <th>Besaran Manfaat</th>
                                        <th>Durasi Pemberian Bantuan</th>
                                        <th>Diubah oleh</th>
                                        <th>Diubah pada</th>
                                        <th>Opsi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
