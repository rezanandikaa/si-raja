<script type="text/javascript">
$(document).ready(function(){
    'use strict'

    var table = $('.js-basic-datatable').DataTable({
        processing: true,
        serverSide: true,
        // ajax: "{{ $datatable['route_data'] }}",
        ajax: {
            url: "{{ $datatable['route_data'] }}",
            type: "POST", // Menggunakan metode POST
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        columns: {!! json_encode($datatable['json_data']) !!},
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        dom: 'Bfrtlip',
        buttons: [
            {
                text: '<i class="fa fa-refresh"></i> Refresh',
                action: function ( e, dt, node, config ) {
                    table.ajax.reload();
                },
                className: 'btn-primary',
            },
            {
                text: 'Buat Baru',
                className: 'btn-secondary',
                action: function ( e, dt, node, config ) {
                    window.open("{{ $datatable['route_insert'] ?? '#' }}", "_self");
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            }
        ],
        order: [[ 1, "desc" ]]
    });

    $('.js-basic-datatable tbody').on('click', 'a.delete', function () {
        swal({
            title: "Hapus !",
            text: "Anda yakin akan menghapus data ini ?",
            type: "warning",
            showCancelButton: !0,
            cancelButtonText: "Batal",
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Hapus",
            closeOnConfirm: !1
        }).then((result) => {
            if (result.value){
                let id = $(this).attr('data-id');
                let url_delete = $(this).attr('data-url');
                id = id.split("-");
                $(this).attr('disabled','disabled');
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type:"DELETE",
                    url: url_delete,
                    data: {id: id[1]},
                    success: function(response){
                        if(response.status === 'OK'){
                            toastr.success(response.message, "Berhasil", {
                                timeOut: 5000,
                                closeButton: !0,
                                debug: !1,
                                newestOnTop: !0,
                                progressBar: !0,
                                positionClass: "toast-bottom-right",
                                preventDuplicates: !0,
                                onclick: null,
                                showDuration: "300",
                                hideDuration: "1000",
                                extendedTimeOut: "1000",
                                showEasing: "swing",
                                hideEasing: "linear",
                                showMethod: "fadeIn",
                                hideMethod: "fadeOut",
                                tapToDismiss: !1
                            });
                            table.ajax.reload();
                        } else {
                            toastr.error(response.message, "Terjadi Kesalahan", {
                                timeOut: 5000,
                                closeButton: !0,
                                debug: !1,
                                newestOnTop: !0,
                                progressBar: !0,
                                positionClass: "toast-bottom-right",
                                preventDuplicates: !0,
                                onclick: null,
                                showDuration: "300",
                                hideDuration: "1000",
                                extendedTimeOut: "1000",
                                showEasing: "swing",
                                hideEasing: "linear",
                                showMethod: "fadeIn",
                                hideMethod: "fadeOut",
                                tapToDismiss: !1
                            });
                        }
                        $(this).removeAttr('disabled');
                    },
                    statusCode: {
                        500: function() {
                            toastr.error("Data tidak tersimpan", "Terjadi Kesalahan", {
                                timeOut: 5000,
                                closeButton: !0,
                                debug: !1,
                                newestOnTop: !0,
                                progressBar: !0,
                                positionClass: "toast-bottom-right",
                                preventDuplicates: !0,
                                onclick: null,
                                showDuration: "300",
                                hideDuration: "1000",
                                extendedTimeOut: "1000",
                                showEasing: "swing",
                                hideEasing: "linear",
                                showMethod: "fadeIn",
                                hideMethod: "fadeOut",
                                tapToDismiss: !1
                            });
                            $(this).removeAttr('disabled');
                        }
                    }
                });
            }
        })
    } );
});
</script>
