@php
$datatable = [];

$datatable['json_data'] = [
    ["data" => "budget_year_name", "name" => "mt_budget_year.name", "orderable" => false, "searchable" => true],
    ["data" => "code", "name" => "mt_program_template.code", "orderable" => false, "searchable" => true],
    ["data" => "concern", "name" => "mt_program_template.concern", "orderable" => false, "searchable" => true],
    ["data" => "performance", "name" => "mt_program_template.performance", "orderable" => false, "searchable" => true],
    ["data" => "indicator", "name" => "mt_program_template.indicator", "orderable" => false, "searchable" => true],
    ["data" => "measure", "name" => "mt_program_template.measure", "orderable" => false, "searchable" => true],
    ["data" => "organization_name", "name" => "mt_organization.name", "orderable" => false, "searchable" => true],
    ["data" => "updated_by_name", "name" => "updated_by.name", "orderable" => false, "searchable" => false],
    ["data" => "updated_at", "name" => "mt_program_template.updated_at", "orderable" => false, "searchable" => false],
    ["data" => "action", "name" => "action", "orderable" => false, "searchable" => false],
];

$datatable['route_data'] = route('master.program_template.get_data');
// $datatable['route_insert'] = route('master.program_template.insert');

@endphp

@extends('baduyengine.app')

@section('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/fixedeader/dataTables.fixedcolumns.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/fixedeader/dataTables.fixedheader.bootstrap4.min.css') }}">
@endsection

@section('vendor-js')
<script src="{{ asset('assets/bundles/datatablescripts.bundle.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-datatable/buttons/buttons.print.min.js') }}"></script>

<script type="text/javascript">
$(document).ready(function(){
    'use strict'

    var table = $('.js-basic-datatable').DataTable({
        processing: true,
        serverSide: true,
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
                className: 'btn-danger',Kegiatan
                action: function ( e, dt, node, config ) {
                    window.open("{{ $datatable['route_back'] ?? '#' }}", "_self");
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            @endif
            {
                text: '<i class="fa fa-refresh"></i> Refresh',
                action: function ( e, dt, node, config ) {
                    table.ajax.reload();
                },
                className: 'btn-primary',
            },
            {
                text: 'Buat Baru Program',
                className: 'btn-secondary',
                action: function ( e, dt, node, config ) {
                    window.open("{{ route('master.program_template.insert', ['type' => 'program']) ?? '#' }}", "_self");
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            {
                text: 'Buat Baru Kegiatan',
                className: 'btn-secondary',
                action: function ( e, dt, node, config ) {
                    window.open("{{ route('master.program_template.insert', ['type' => 'activity']) ?? '#' }}", "_self");
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            {
                text: 'Buat Baru Sub Kegiatan',
                className: 'btn-secondary',
                action: function ( e, dt, node, config ) {
                    window.open("{{ route('master.program_template.insert', ['type' => 'sub_activity']) ?? '#' }}", "_self");
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            }
        ],
        columnDefs: [
            {
                render: function (data, type, full, meta) {
                    return "<div class='text-wrap'>" + (data || '-') + "</div>";
                },
                targets: '_all'
            }
        ],
        order: [[ 1, "desc" ]]
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
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i></a></li>
                        <li class="breadcrumb-item">Master</li>
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
                                        <th>Kode</th>
                                        <th>Nomenklatur Urusan</th>
                                        <th>Kinerja</th>
                                        <th>Indikator</th>
                                        <th>Satuan</th>
                                        <th>Perangkat Daerah</th>
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
