@php
$datatable = [];

$datatable['json_data'] = [
    ["data" => "name", "name" => "mt_budget_source.name"],
    ["data" => "updated_by_name", "name" => "updated_by.name"],
    ["data" => "updated_at", "name" => "mt_budget_source.updated_at"],
    ["data" => "action", "name" => "action", "orderable" => false, "searchable" => false],
];

$datatable['route_data'] = route('master.budget_source.get_data');
$datatable['route_insert'] = route('master.budget_source.insert');

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

@include('baduyengine.component.datatable', $datatable)
<script>
$(document).ready(function () {
    $('.js-basic-datatable tbody').on('click', 'a.import', function (e) {
        e.preventDefault();
        let data_url = $(this).attr('data-url');
        swal({
            title: "Impor Data!",
            text: "Anda yakin akan tambahkan berkas ini ke antrian ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#59C4BC",
            confirmButtonText: "Ya",
            cancelButtonText: "Kembali",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type: "POST",
                    url: data_url,
                    success: function(response){
                        if(response.status === 'OK'){
                            swal("Berhasil", response.message, "success");
                            window.location.href = "{{ route('master.destitution_nik.list') }}";
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
                swal("Dibatalkan", "Data tidak di impor", "error");
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
                                        <th>Sumber Pembiayaan</th>
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
