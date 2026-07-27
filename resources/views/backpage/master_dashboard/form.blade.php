@extends('baduyengine.app')

@section('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/parsleyjs/css/parsley.css') }}">
@endsection

@section('vendor-js')
<script src="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
<script src="{{ asset('assets/vendor/parsleyjs/js/parsley.min.js') }}"></script>
@include('baduyengine.component-js.form',$data)
<script>
$(document).ready(function(){
    // $('.be-select-data').select2();
    // validation needs name of the element
    // $('#food').multiselect();

    // text blur
    this.textBlur = function(id) {
        console.log('textBlur', id);
    }

    $('#district_id').on('change', function() {
        var selectedValue = $(this).val();
        $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            url: '{{ route("ajax.data_select") }}',
            method: 'POST',
            dataType: 'json',
            data: {table: 'mt_region', condition: ' and mt_region.parent_id = "'+selectedValue+'"'},
            success: function(data) {
                var select = $("#subdistrict_id");
                // Data telah berhasil diambil
                // Remove all options
                select.empty();

                // Reinitialize the multiselect plugin
                select.multiselect('destroy');

                select.append($('<option></option>')
                    .attr('value', "")
                    .text("-- Pilih --")
                );
                $.each(data, function(key, value) {
                    select.append($('<option></option>')
                        .attr('value', value.id)
                        .text(value.label)
                    );
                });

                // Refresh the multiselect to reflect the changes
                select.multiselect({
                    enableFiltering: true,
                    enableCaseInsensitiveFiltering: true,
                    maxHeight: 400,
                });
            },
            error: function() {
                console.log('Terjadi kesalahan dalam permintaan AJAX');
            }
        });
    });

    // $('.be-select-data').on('mousedown', function() {
    //     var select = $(this);
    //     var dataTable = select.attr('data-table');
    //     var dataCondition = select.attr('data-condition');
    //     $.ajax({
    //         headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
    //         url: '{{ route("ajax.data_select") }}',
    //         method: 'POST',
    //         dataType: 'json',
    //         data: {table: dataTable, condition: dataCondition},
    //         success: function(data) {
    //             // Data telah berhasil diambil
    //             select.empty();

    //             select.append($('<option></option>')
    //                 .attr('value', "")
    //                 .text("-- Pilih --")
    //             );
    //             $.each(data, function(key, value) {
    //                 select.append($('<option></option>')
    //                     .attr('value', value.id)
    //                     .text(value.label)
    //                 );
    //             });
    //         },
    //         error: function() {
    //             console.log('Terjadi kesalahan dalam permintaan AJAX');
    //         }
    //     });
    // });
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
                        <li class="breadcrumb-item">Sistem</li>
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
            <div class="col-md-12">
                <div class="card">
                    <div class="header">
                        <h2>{{ $data['_be_page_title'] }}</h2>
                    </div>
                    <div class="body">
                        @include('baduyengine.component.form', $data)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
