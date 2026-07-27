@php
$datatable = [];

$datatable['json_data'] = [
    ["data" => "file_name_original", "name" => "sy_attachment.file_name_original"],
    ["data" => "size", "name" => "sy_attachment.size"],
    ["data" => "updated_by_name", "name" => "updated_by.name"],
    ["data" => "updated_at", "name" => "sy_attachment.updated_at"],
    ["data" => "action", "name" => "action", "orderable" => false, "searchable" => false],
];

$datatable['route_data'] = route('program.realization.attachment.get_data', ['id' => $data['_parent_id']]);
$datatable['route_insert'] = route('program.realization.attachment.insert', ['id' => $data['_parent_id']]);
$datatable['route_back'] = route('program.realization.list');

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
    $('.js-basic-datatable tbody').on('click', 'a.download', function () {
        let id = $(this).attr('data-id');
        let url = $(this).attr('data-url');
        id = id.split("-");
        $(this).attr('disabled','disabled');
        swal({
            title: "Konfirmasi!",
            text: "Anda yakin untuk mengunduh berkas ini ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#59c4bc",
            confirmButtonText: "Ya",
            cancelButtonText: "Kembali",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                window.location.href = url;
                swal("Berhasil", "Berhasil mengunduh berkas lampiran", "success");
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
                                        <th>Nama Lampiran</th>
                                        <th>Ukuran Berkas</th>
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
