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
            // ajax: "{{ route('master.destitution_kk.get_data', ['kemdagri_code' => $data['data']['kemdagri_code']]) }}",
            ajax: {
                url: "{{ route('master.destitution_kk.get_data', ['kemdagri_code' => $data['data']['kemdagri_code']]) }}",
                type: "POST", // Menggunakan metode POST
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
            },
            columns: [{
                    data: "data_name",
                    name: "sy_data.name"
                },
                {
                    data: "p3ke",
                    name: "mt_destitution_kk.p3ke"
                },
                {
                    data: "last_update_year",
                    name: "mt_destitution_kk.last_update_year"
                },
                {
                    data: "subdistrict_code",
                    name: "subdistrict.code"
                },
                {
                    data: "district_name",
                    name: "district.name"
                },
                {
                    data: "subdistrict_name",
                    name: "subdistrict.name"
                },
                {
                    data: "nik",
                    name: "mt_destitution_kk.nik"
                },
                {
                    data: "name",
                    name: "mt_destitution_kk.name"
                },
                {
                    data: "decile",
                    name: "mt_destitution_kk.decile"
                },
                {
                    data: "percentile",
                    name: "mt_destitution_kk.percentile"
                },
                {
                    data: "updated_by_name",
                    name: "updated_by.name"
                },
                {
                    data: "updated_at",
                    name: "mt_destitution_kk.updated_at"
                },
                {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false
                },
            ],
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
            }, ],
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
                condition: ''
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

        $('#action_bpnt').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                condition: 'mt_destitution_kk.is_bpnt = 1'
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

        $('#action_bpum').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                condition: 'mt_destitution_kk.is_bpum = 1'
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

        $('#action_bst').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                condition: 'mt_destitution_kk.is_bst = 1'
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

        $('#action_pkh').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                condition: 'mt_destitution_kk.is_pkh = 1'
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

        $('#action_sembako').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                condition: 'mt_destitution_kk.is_sembako = 1'
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

        $('#action_prakerja').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                condition: 'mt_destitution_kk.is_prakerja = 1'
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

        $('#action_kur').on('click', '.card', function() {
            var settings = table.settings();
            var newData = {
                condition: 'mt_destitution_kk.is_kur = 1'
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
    });
</script>
@endif
<script>
    $(document).ready(function() {
        $(document).ajaxStart(function() {
            $('.loading-screen').show();
        });

        $(document).ajaxStop(function() {
            $('.loading-screen').hide();
        });

        // Data untuk chart pie
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: "POST",
            url: "{{ route('ajax.chart_by_type') }}",
            dataType: 'json',
            data: {
                type: "kk",
                kemdagri_code: "{{ $data['data']['kemdagri_code'] }}"
            },
            success: function(data) {
                createMapChart(data, 'map');
            }
        });

        function createMapChart(data, id) {

            // URL GeoJSON Kustom
            var geojsonUrl = "{{ $data['data']['map'] }}";

            // Menentukan nilai terendah dan tertinggi
            var minValue = 0;
            var maxValue = 100000;

            // Mengambil GeoJSON dari URL menggunakan jQuery
            $.getJSON(geojsonUrl, async function(geojson) {
                // Menghitung nilai minimum dan maksimum dari data
                var min = Infinity;
                var max = -Infinity;
                data.map.forEach(function(point) {
                    min = Math.min(min, point.value);
                    max = Math.max(max, point.value);
                });

                // Konfigurasi Chart
                Highcharts.mapChart(id, {
                    chart: {
                        map: geojson // Menggunakan GeoJSON kustom
                    },
                    title: {
                        text: '{{ $data["_be_page_title"] }}'
                    },
                    subtitle: {
                        text: 'Sumber Data: P3KE Kab. Lebak 2023'
                    },
                    mapNavigation: {
                        enabled: true,
                        buttonOptions: {
                            verticalAlign: 'bottom',
                            align: 'left'
                        }
                    },
                    colorAxis: {
                        min: min,
                        max: max,
                        minColor: '#EFEFFF',
                        maxColor: '#33BAB0'
                    },
                    series: [{
                        data: data.map,
                        // dataLabels: {
                        //     enabled: true,
                        //     format: '{point.properties.name}'
                        // },
                        joinBy: 'hc-key',
                        name: 'Percentil 1 - 2',
                        states: {
                            hover: {
                                color: '#8BCEC9'
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}'
                        },
                        point: {
                            events: {
                                click: function() {
                                    // Mengarahkan ke tautan lain saat provinsi diklik
                                    // alert(this.properties['hc-key']);
                                    window.location.href = '{{ route("bnba.detail", ["source" => "kk"]) }}?kemdagri_code=' + this.properties['hc-key'];
                                }
                            }
                        }
                    }],
                });
            });
        }
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
                            <a href="{{ route('bnba.detail', ['source' => 'kk', 'kemdagri_code' => $data['data']['back_kemdagri_code']]) }}" class="btn btn-danger"><i class="fa-solid fa-chevron-left"></i> Kembali</a>
                        </div>
                        <div class="p-2 d-flex">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MAP CHART --}}
        <div class="row clearfix">
            <div class="col-lg-12">
                <div class="card">
                    <div class="body">
                        <div id="map" style="height: 48rem">
                            <div id="loading-screen">
                                <img src="{{ asset('assets/images/gif/loading.gif') }}" alt="Loading..." />
                            </div>
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
                        <div class="icon"><img src="{{ asset('assets/images/ic-sm/user.png') }}" alt="user" /></div>
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Total</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total'] }}</h4>
                            <small class="text-muted">Kepala Keluarga Tercatat di Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            <div id="action_bpnt" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        <div class="icon"><img src="{{ asset('assets/images/ic-sm/food-donation.png') }}" alt="bpnt" /></div>
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Penerima BPNT</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total_bpnt'] }}</h4>
                            <small class="text-muted">Kepala Keluarga Tercatat di Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            <div id="action_bpum" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        <div class="icon"><img src="{{ asset('assets/images/ic-sm/water-scarcity.png') }}" alt="bpum" /></div>
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Penerima BPUM</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total_bpum'] }}</h4>
                            <small class="text-muted">Kepala Keluarga Tercatat di Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            <div id="action_bst" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        <div class="icon"><img src="{{ asset('assets/images/ic-sm/payment.png') }}" alt="bst" /></div>
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Penerima BST</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total_bst'] }}</h4>
                            <small class="text-muted">Kepala Keluarga Tercatat di Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            <div id="action_pkh" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        <div class="icon"><img src="{{ asset('assets/images/ic-sm/home.png') }}" alt="pkh" /></div>
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Penerima PKH</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total_pkh'] }}</h4>
                            <small class="text-muted">Kepala Keluarga Tercatat di Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            <div id="action_sembako" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        <div class="icon"><img src="{{ asset('assets/images/ic-sm/seed-bag.png') }}" alt="sembako" /></div>
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Penerima Sembako</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total_sembako'] }}</h4>
                            <small class="text-muted">Kepala Keluarga Tercatat di Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            <div id="action_prakerja" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        <div class="icon"><img src="{{ asset('assets/images/ic-sm/resume.png') }}" alt="prakerja" /></div>
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Penerima Prakerja</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total_prakerja'] }}</h4>
                            <small class="text-muted">Kepala Keluarga Tercatat di Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
            <div id="action_kur" class="col-lg-3 col-md-6 col-sm-6">
                <div class="card top_widget">
                    <div class="body">
                        <div class="icon"><img src="{{ asset('assets/images/ic-sm/saving.png') }}" alt="kur" /></div>
                        <div class="content">
                            <div class="text mb-2 text-uppercase">Penerima KUR</div>
                            {{-- <h4 class="number mb-0">3,251 <span class="font-12 text-muted"><i class="fa fa-level-up"></i> 13%</span></h4> --}}
                            <h4 class="number mb-0">{{ $data['data']['total_kur'] }}</h4>
                            <small class="text-muted">Kepala Keluarga Tercatat di Sistem</small>
                        </div>
                    </div>
                </div>
            </div>
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
                            <table class="table table-hover js-basic-datatable dataTable table-custom mb-0">
                                <thead>
                                    <tr>
                                        <th>Sumber Data</th>
                                        <th>ID P3KE</th>
                                        <th>Dimuktakhirkan Tahun</th>
                                        <th>Kode Kemdagri</th>
                                        <th>Kecamatan</th>
                                        <th>Desa/Kelurahan</th>
                                        <th>NIK</th>
                                        <th>Nama</th>
                                        <th>Desil Kesejahteraan</th>
                                        <th>Persentil</th>
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
        @endif
    </div>
</div>
@endsection