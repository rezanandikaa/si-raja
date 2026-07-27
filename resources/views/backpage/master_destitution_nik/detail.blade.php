@php
$datatable = [];

$datatable['json_data'] = [
    ["data" => "budget_year_name", "name" => "mt_budget_year.name"],
    ["data" => "program_code", "name" => "tr_program.code"],
    ["data" => "program_program", "name" => "tr_program.program"],
    ["data" => "program_activity", "name" => "tr_program.activity"],
    ["data" => "program_sub_activity", "name" => "tr_program.sub_activity"],
    ["data" => "organization_name", "name" => "mt_organization.name"],
    ["data" => "updated_by_name", "name" => "updated_by.name"],
    ["data" => "updated_at", "name" => "tr_program_realization_bnba.updated_at"],
];

$datatable['route_data'] = route('master.destitution_nik.bnba.get_data', ['id' => $data['_parent_id']]);
// $datatable['route_back'] = route('master.destitution_nik.list');

@endphp

@extends('baduyengine.app')

@section('vendor-css')
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
                        <li class="breadcrumb-item">P3KE Individu</li>
                        <li class="breadcrumb-item active">{{ $data['_be_page_title'] }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="header">
                        <h2>{{ $data['_be_page_title']}}<small>{{ $data['_be_page_title_desc']}} </small> </h2>
                    </div>
                    <div class="body">
                        <ul class="nav nav-tabs">
                            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#detail-profile">Biodata</a></li>
                            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#detail-history">Riwayat Intervensi</a></li>
                        </ul>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="detail-profile">
                            <div class="container-fluid">
                                    <div class="row">
                                        <div class="col-12 col-lg-6">
                                            <div class="card">
                                                <div class="body">
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <h5 class="m-t-0">Desil</h5>
                                                            <small class="text-small">Data P3KE Desil dari 1 sampai dengan 4</small>
                                                        </div>
                                                        <div class="col-5 text-right">
                                                            <h2 class="mb-0">#{{ $data['datas']['decile'] }}</h2>
                                                            <small class="info">dari 4</small>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="progress m-t-20">
                                                            <div class="progress-bar progress-bar-{{ $data['datas']['decile'] * 25 > 50 ? "warning" : "danger" }}" role="progressbar" aria-valuenow="{{ (int) $data['datas']['decile'] * 25 }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ (int) $data['datas']['decile'] * 25 }}%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="card">
                                                <div class="body">
                                                    <div class="row">
                                                        <div class="col-7">
                                                            <h5 class="m-t-0">Persentil</h5>
                                                            <small class="text-small">Data P3KE Persentil dari 1 sampai dengan 100</small>
                                                        </div>
                                                        <div class="col-5 text-right">
                                                            <h2 class="mb-0">#{{ $data['datas']['percentile'] }}</h2>
                                                            <small class="info">dari 100</small>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="progress m-t-20">
                                                            <div class="progress-bar progress-bar-{{ ($data['datas']['percentile'] > 20 ? "warning" : "danger") }}" role="progressbar" aria-valuenow="{{ (float ) $data['datas']['percentile'] }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ (float ) $data['datas']['percentile'] }}%;"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 col-lg-6">
                                            <small class="text-muted">NIK / ID P3KE: </small>
                                            <p>{{ $data['datas']['nik'] }} / {{ $data['datas']['p3ke'] }}</p>
                                            <small class="text-muted">Nama Lengkap: </small>
                                            <p>{{ $data['datas']['name'] }}</p>
                                            <small class="text-muted">Tanggal Lahir: </small>
                                            <p>{{ \Carbon\Carbon::parse($data['datas']['birth_date'])->format('Y-m-d') }}</p>
                                            <small class="text-muted">Jenis Kelamin: </small>
                                            <p>{{ get_option($data['datas']['gender_id']) }}</p>
                                            <small class="text-muted">Alamat: </small>
                                            <p>{{ $data['datas']['address'] }}</p>
                                            <small class="text-muted">Desa/Kelurahan: </small>
                                            <p>{{ get_region($data['datas']['subdistrict_id'], 'code') }} - {{ get_region($data['datas']['subdistrict_id']) }}</p>
                                            <small class="text-muted">Kecamatan: </small>
                                            <p>{{ get_region($data['datas']['district_id'], 'code') }} - {{ get_region($data['datas']['district_id']) }}</p>
                                            <small class="text-muted">Hubungan dengan Kepala Keluarga: </small>
                                            <p>{{ get_option($data['datas']['relationship_id']) }}</p>
                                            <small class="text-muted">Status Kawin: </small>
                                            <p>{{ get_option($data['datas']['marital_status_id']) }}</p>
                                            <small class="text-muted">Pendidikan: </small>
                                            <p>{{ get_option($data['datas']['education_id']) }}</p>
                                            <small class="text-muted">Pekerjaan: </small>
                                            <p>{{ get_option($data['datas']['job_id']) }}</p>
                                            <small class="text-muted">Status Pekerjaan: </small>
                                            <p>{{ get_option($data['datas']['job_status_id']) }}</p>
                                            <small class="text-muted">Padan Dukcapil: </small>
                                            <p>{{ get_option($data['datas']['padan_dukcapil_id']) }}</p>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <ul class="list-group">
                                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                                    <p class="mb-0">Penerima BPNT</p>
                                                    <span class="badge badge-{{ $data['datas']['is_bpnt'] ? 'success' : 'danger' }}">{{ $data['datas']['is_bpnt'] ? 'Ya' : 'Tidak' }}</span>
                                                </li>
                                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                                    <p class="mb-0">Penerima BPUM</p>
                                                    <span class="badge badge-{{ $data['datas']['is_bpum'] ? 'success' : 'danger' }}">{{ $data['datas']['is_bpum'] ? 'Ya' : 'Tidak' }}</span>
                                                </li>
                                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                                    <p class="mb-0">Penerima BST</p>
                                                    <span class="badge badge-{{ $data['datas']['is_bst'] ? 'success' : 'danger' }}">{{ $data['datas']['is_bst'] ? 'Ya' : 'Tidak' }}</span>
                                                </li>
                                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                                    <p class="mb-0">Penerima PKH</p>
                                                    <span class="badge badge-{{ $data['datas']['is_pkh'] ? 'success' : 'danger' }}">{{ $data['datas']['is_pkh'] ? 'Ya' : 'Tidak' }}</span>
                                                </li>
                                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                                    <p class="mb-0">Penerima Sembako</p>
                                                    <span class="badge badge-{{ $data['datas']['is_sembako'] ? 'success' : 'danger' }}">{{ $data['datas']['is_sembako'] ? 'Ya' : 'Tidak' }}</span>
                                                </li>
                                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                                    <p class="mb-0">Penerima Prakerja</p>
                                                    <span class="badge badge-{{ $data['datas']['is_prakerja'] ? 'success' : 'danger' }}">{{ $data['datas']['is_prakerja'] ? 'Ya' : 'Tidak' }}</span>
                                                </li>
                                                <li class="d-flex justify-content-between list-group-item align-items-center">
                                                    <p class="mb-0">Penerima KUR</p>
                                                    <span class="badge badge-{{ $data['datas']['is_kur'] ? 'success' : 'danger' }}">{{ $data['datas']['is_kur'] ? 'Ya' : 'Tidak' }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="detail-history">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-custom dataTable js-basic-datatable" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Tahun Anggaran</th>
                                                    <th>Nomenklatur</th>
                                                    <th>Program</th>
                                                    <th>Kegiatan</th>
                                                    <th>Sub Kegiatan</th>
                                                    <th>Perangkat Daerah</th>
                                                    <th>Diubah oleh</th>
                                                    <th>Diubah pada</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12 m-b-15">
                                <a href="{{ $data['_be_home'] }}" class="btn btn-outline btn-danger">Kembali</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
