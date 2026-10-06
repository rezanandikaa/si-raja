@extends('baduyengine.app')

@section('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/jquery-datatable/dataTables.bootstrap4.min.css') }}">
@endsection

@section('vendor-js')
<script src="{{ asset('assets/bundles/datatablescripts.bundle.js') }}"></script>
<script>
// Pratinjau unggahan, sama seperti halaman DTSEN KK.
let beBatchCode = @json($data['_be_preview_batch'] ?? null);
let beSheet = @json($data['_be_preview_sheet'] ?? null);

// Nilai sel ditulis lewat .text(), bukan .html() — isinya data warga.
function beRenderPreview(preview) {
    if (!preview || !preview.columns.length) {
        $('#bePreview').hide();
        return;
    }

    let head = $('<tr>');
    $.each(preview.columns, function (i, name) { head.append($('<th>').text(name)); });

    let body = $('<tbody>');
    $.each(preview.rows, function (i, row) {
        let tr = $('<tr>');
        $.each(row, function (j, value) { tr.append($('<td>').text(value === null ? '' : value)); });
        body.append(tr);
    });

    // DataTables membekukan jumlah baris di dalam DOM, jadi tabel dibongkar
    // ulang tiap ganti sheet: instance lama dibuang dulu, kalau tidak baris
    // dari sheet sebelumnya ikut tertinggal.
    if ($.fn.DataTable.isDataTable('#bePreviewTable')) {
        $('#bePreviewTable').DataTable().destroy();
    }
    $('#bePreviewTable').empty().append($('<thead>').append(head)).append(body);
    $('#bePreviewTable').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        order: [],
        columnDefs: [{targets: '_all', defaultContent: ''}]
    });

    let select = $('#bePreviewSheet').empty();
    $.each(preview.sheets, function (i, name) {
        select.append($('<option>').val(name).text(name).prop('selected', name === preview.sheet));
    });
    select.toggle(preview.sheets.length > 1);

    let note = 'Sheet "' + preview.sheet + '": ' + preview.columns.length + ' kolom, '
        + preview.rows.length + ' baris dimuat'
        + (preview.truncated ? ' (25 baris per halaman, sisanya masih ada di berkas)' : '') + '.';
    note += ' Impor memakai sheet ini.';
    $('#bePreviewNote').text(note);

    $('#bePreview').show();
}

function beSetLoading(loading) {
    $('#bePreviewLoading').toggle(loading);
    $('#bePreviewContent').toggle(!loading);
}

// silent dipakai saat memuat ulang halaman: kalau berkasnya sudah tidak ada,
// tabel cukup tidak ditampilkan tanpa muncul peringatan.
function beLoadPreview(sheet, silent) {
    $('#bePreview').show();
    beSetLoading(true);

    $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        type: "GET",
        url: "{{ route('master.destitution_nik.preview') }}",
        data: {batch_code: beBatchCode, sheet: sheet},
        success: function (response) {
            beSetLoading(false);
            if (response.status === 'OK' && response.preview && response.preview.columns.length) {
                beSheet = response.preview.sheet;
                beRenderPreview(response.preview);
            } else if (silent) {
                $('#bePreview').hide();
            } else {
                swal("Terjadi Kesalahan", response.message || 'Pratinjau tidak tersedia', "error");
            }
        },
        error: function (xhr) {
            beSetLoading(false);
            if (silent) {
                $('#bePreview').hide();
            } else {
                swal("Terjadi Kesalahan", 'Kesalahan sistem ' + xhr.status, "error");
            }
        }
    });
}

$(document).ready(function () {
    // Lanjutkan pratinjau unggahan terakhir setelah pindah halaman / refresh.
    if (beBatchCode) {
        beLoadPreview(beSheet, true);
    }

    $('#beUploadForm').on('submit', function (e) {
        e.preventDefault();
        let form = $(this);
        let btn = $('#beUploadBtn');
        btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Membaca berkas...');

        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type: "POST",
            url: form.attr('action'),
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function (response) {
                btn.removeAttr('disabled').html('<i class="fa fa-upload"></i> Upload DTSEN ART');
                if (response.status === 'OK') {
                    swal("Berhasil", response.message, "success");
                    form[0].reset();
                    beBatchCode = response.batch_code;
                    beSheet = response.preview ? response.preview.sheet : beSheet;
                    beRenderPreview(response.preview);
                } else {
                    swal("Terjadi Kesalahan", response.message, "error");
                }
            },
            error: function (xhr) {
                btn.removeAttr('disabled').html('<i class="fa fa-upload"></i> Upload DTSEN ART');
                swal("Terjadi Kesalahan", xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Kesalahan sistem ' + xhr.status, "error");
            }
        });
    });

    $('#bePreviewSheet').on('change', function () {
        beLoadPreview($(this).val(), false);
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
                        <h2>Unggah Berkas DTSEN ART<small>Berkas Excel (.xlsx) yang berisi data DTSEN ART</small></h2>
                    </div>
                    <div class="body">
                        <form id="beUploadForm" method="POST" action="{{ route('master.destitution_nik.upload') }}" enctype="multipart/form-data" autocomplete="off">
                            @csrf
                            <div class="form-group">
                                <label for="file">Berkas DTSEN ART <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="file" name="file" accept=".xlsx" required>
                                <small class="text-muted">Format Excel (.xlsx). Berkas masuk antrian impor.</small>
                            </div>
                            <button type="submit" class="btn btn-primary" id="beUploadBtn">
                                <i class="fa fa-upload"></i> Upload DTSEN ART
                            </button>
                        </form>

                        <div id="bePreview" class="mt-4" style="display: none;">
                            <hr>
                            <div id="bePreviewLoading" class="text-center py-5" style="display: none;">
                                <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                            </div>
                            <div id="bePreviewContent">
                                <div class="form-group">
                                    <label for="bePreviewSheet">Sheet</label>
                                    <select class="form-control" id="bePreviewSheet"></select>
                                    <small class="text-muted" id="bePreviewNote"></small>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered table-striped" id="bePreviewTable"></table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
