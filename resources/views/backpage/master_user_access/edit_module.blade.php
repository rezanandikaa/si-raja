@extends('baduyengine.app')

@section('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/parsleyjs/css/parsley.css') }}">
@endsection

@section('vendor-js')
<script src="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
<script src="{{ asset('assets/vendor/parsleyjs/js/parsley.min.js') }}"></script>
<script>
$(document).ready(function(){
    // $('.be-select-data').select2();
    // validation needs name of the element
    // $('#food').multiselect();

    // text blur
    this.textBlur = function(id) {
        console.log('textBlur', id);
    }

    // initialize after multiselect
    $('#beForm').parsley();
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
            cache:false,
            contentType: false,
            processData: false,
            success: function(response){
                if(response.status === 'OK'){
                    toastr.remove();
                    toastr['success']('Data berhasil tersimpan', '', {
                        positionClass: 'toast-top-center'
                    });
                    window.location.href = "{{$data['_be_home']}}";
                } else {
                    toastr.remove();
                    toastr['error'](response.message, '', {
                        positionClass: 'toast-top-center'
                    });
                }
                $('#submit').removeAttr('disabled');
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

    this.checkboxChange = function (event, name = undefined) {
        console.log('checkboxChange', name, event.checked);
        var target = `.${name}`;

        switch (name) {
            case 'cb-module':
            case 'cb-read':
                event.checked ? $("table").find(target).prop('checked', event.checked=!0)
                    :$("table").find(target).prop('checked', event.checked=!1);
                break;

            default:
                event.checked ? $(this).prop('checked', event.checked=!0)
                    :$(this).prop('checked', event.checked=!1);
                break;
        }
    }

    // $(".select-all").on("click",function(){
    //     var target = $(this).prop('data-target');
    //     this.checked ? $(this).parents("table").find(`.checkbox-tick .${target}`).each(function(){this.checked=!0})
    //         :$(this).parents("table").find(`.checkbox-tick .${target}`).each(function(){this.checked=!1});
    // });
    // $(".checkbox-tick").on("click",function(){
    //     var target = $(this).prop('data-target');
    //     $(this).parents("table").find(".checkbox-tick:checked").length == $(this).parents("table").find(".checkbox-tick").length ? $(this).parents("table").find(`.select-all .${target}`).prop("checked",!0) : $(this).parents("table").find(`.select-all .${target}`).prop("checked",!1);
    // });

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
                        <form id="beForm" method="POST" action="{{ $data['_be_action'] }}" enctype="multipart/form-data" autocomplete="off" data-parsley-validate novalidate>
                            @csrf
                            @method($data['_be_method'])
                            <table class="table table-hover mb-0 c_list">
                                <thead>
                                    <tr>
                                        <th>Nama Modul</th>
                                        <th>
                                            <label class="fancy-checkbox">
                                                <input type="checkbox" class="pr-module" onchange="checkboxChange(this, 'cb-module')" />
                                                <span>Aktifkan Semua Modul</span>
                                            </label>
                                        </th>
                                        <th>
                                            <label class="fancy-checkbox">
                                                <input type="checkbox" class="pr-read"  onchange="checkboxChange(this, 'cb-read')" />
                                                <span>Aktifkan Lihat Semua</span>
                                            </label>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['modules'] as $module)
                                    <tr>
                                        <td>
                                            <p class="c_name">{{ $module['name'] }}</p>
                                            <input type="hidden" name="name[]" value="{{ $module['value'] }}">
                                        </td>
                                        <td style="width: 300px;">
                                            <label class="fancy-checkbox">
                                                <input class="cb-module" value="1" onchange="checkboxChange(this, 'check-pick')" data-target="pr-module" type="checkbox" name="active_flag[{{ $module['value'] }}]" {{ isset($data['datas'][$module['value']]['active_flag']) ? ($data['datas'][$module['value']]['active_flag'] ? 'checked' : '') : '' }}>
                                                <span></span>
                                            </label>
                                        </td>
                                        <td style="width: 300px;">
                                            <label class="fancy-checkbox">
                                                <input class="cb-read" value="1" onchange="checkboxChange(this, 'check-pick')" data-target="pr-read" type="checkbox" name="read_all_flag[{{ $module['value'] }}]" {{ isset($data['datas'][$module['value']]['read_all_flag']) ? ($data['datas'][$module['value']]['read_all_flag'] ? 'checked' : '') : '' }}>
                                                <span></span>
                                            </label>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <a href="{{ url()->previous() }}" class="btn btn-danger">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
