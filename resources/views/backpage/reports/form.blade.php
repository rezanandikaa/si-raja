@extends('baduyengine.app')

@section('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/parsleyjs/css/parsley.css') }}">
@endsection

@section('vendor-js')
<script src="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
<script src="{{ asset('assets/vendor/parsleyjs/js/parsley.min.js') }}"></script>
@include('baduyengine.component-js.form')
<script>
$(document).ready(function(){
    // initialize after multiselect
    // $('#beForm').parsley();
    // $('.select2').select2();

    // save
    var form = $("#beForm");
    $(form).submit(function(event){
        event.preventDefault();
        toastr['info']('Sedang menyimpan data ...', '', {
            positionClass: 'toast-top-center'
        });
        $('#submit').attr('disabled','disabled');
        $("input[type='text'], textarea").each(function() {
            // Ambil nilai input saat ini
            var inputValue = $(this).val();
            // Ubah nilai input menjadi huruf kapital
            var kapitalValue = inputValue.toUpperCase();
            // Setel kembali nilai input dengan yang sudah diubah menjadi huruf kapital
            $(this).val(kapitalValue);
        });
        // for (instance in CKEDITOR.instances) {
        //     CKEDITOR.instances[instance].updateElement();
        // }
        var formData = new FormData(this);
        $.ajax({
            type:"POST",
            url: $(this).attr("action"),
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            xhrFields: {
                responseType: 'blob',
            },
            success: function(response, status, xhr) {
                toastr.remove();
                toastr['success']('Data berhasil diekspor', '', {
                    positionClass: 'toast-top-center'
                });
                var disposition = xhr.getResponseHeader('content-disposition');
                var matches = /"([^"]*)"/.exec(disposition);
                var filename = (matches != null && matches[1] ? matches[1] : 'salary.xlsx');

                // The actual download
                var blob = new Blob([response], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                var link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = filename;

                document.body.appendChild(link);

                link.click();
                document.body.removeChild(link);
            },
            statusCode: {
                500: function() {
                    toastr.remove();
                    toastr['error'](response.message, '', {
                        positionClass: 'toast-top-center'
                    });
                    $('#submit').removeAttr('disabled');
                }
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
