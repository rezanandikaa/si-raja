@extends('baduyengine.app')

@section('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/parsleyjs/css/parsley.css') }}">
@foreach ($data['charts'] as $chart)
<style>
#loading-screen-{{ $chart['id'] }} {
    position: relative;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
}

#loading-screen-{{ $chart['id'] }} img {
    width: 80px; /* Sesuaikan ukuran gambar */
    height: 80px;
}
</style>
@endforeach
@endsection

@section('vendor-js')
<!-- Dependensi Highcharts -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/maps/modules/map.js"></script>
{{-- <script src="https://code.highcharts.com/maps/modules/drilldown.js"></script> --}}
{{-- <script src="https://code.highcharts.com/maps/modules/offline-exporting.js"></script> --}}
<script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>
<script src="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.js') }}"></script>
<script src="{{ asset('assets/vendor/parsleyjs/js/parsley.min.js') }}"></script>

<script>
    $(document).ready(function () {
        $("#budget_year_id").multiselect({
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            maxHeight: 400,
        });
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
            $("input[type='text'].uppercase, textarea.uppercase").each(function() {
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
    });
</script>

@foreach ($data['charts'] as $chart)
    @if ($chart['active_flag'])
    @include('baduyengine.component-js.chart', $chart)
    @endif
@endforeach
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

        <div class="row clearfix row-deck">
            <div class="col-lg-12 col-md-12 col-12">
                <div class="card w_profile">
                    <div class="body">
                        <div class="row">
                            <div class="col-12">
                                <h6 class="m-t-0 mb-0">Selamat Datang di {{ get_preference('app_name', 'SIRAJA') }}<br /><br /><strong>{{ auth()->user()->name_with_title }}</strong></h6>
                                <br />
                                <span class="job_post">Perangkat Daerah: <br />{{ get_organization(auth()->user()->organization_id) }}</span>
                                <div class="m-t-15">
                                    <a href="{{ route('home') }}" class="btn btn-lg round btn-primary">Mulai Analisis</a>
                                    <a href="#modalBudgetYear" data-toggle="modal" data-target="#modalBudgetYear" data-id="{{ auth()->user()->budget_year_id }}" data-url="{{ route('master.user.update_budget_year', ['id' => auth()->user()->id]) }}" class="btn btn-lg round btn-success">Anggaran ({{ get_budget_year(auth()->user()->budget_year_id) }})</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row clearfix row-deck">
            @foreach ($data['charts'] as $chart)
                @if ($chart['active_flag'])
                @include('baduyengine.component.chart', $chart)
                @endif
            @endforeach
        </div>
    </div>
</div>
<!-- Default Size -->
<div class="modal fade" id="modalBudgetYear" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="beForm" action="{{ route('master.user.update_budget_year', ['id' => auth()->user()->id]) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h4 class="title" id="modalBudgetYearLabel">Tahun Anggaran</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="budget_year_id">Tahun Anggaran <span class='text-danger'>*</span></label>
                        <div class="c_multiselect">
                            <select class="multiselect multiselect-custom" id="budget_year_id" name="budget_year_id" required>
                                <option value="">-- Pilih --</option>
                                @foreach ($data['budget_years'] as $option)
                                    <option value="{{$option['id']}}" {!! auth()->user()->budget_year_id == $option['id'] ? 'selected="selected"':'' !!}>{{$option['label']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
