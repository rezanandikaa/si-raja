<script type="text/javascript">
$(document).ready(function(){
    'use strict'

    var table = $('.js-basic-datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ $datatable['route_data'] }}",
            type: "POST", // Menggunakan metode POST
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
        },
        // ajax: "{{ $datatable['route_data'] }}",
        columns: {!! json_encode($datatable['json_data']) !!},
        lengthMenu: [
            [10, 25, 50, -1],
            [10, 25, 50, "All"]
        ],
        dom: 'Bfrtlip',
        buttons: [
            @if (isset($datatable['route_back']))
            {
                text: '<i class="fa fa-arrow-left"></i> Kembali',
                className: 'btn-danger',
                action: function ( e, dt, node, config ) {
                    window.open("{{ $datatable['route_back'] ?? '#' }}", "_self");
                },
                init: function(api, node, config) {
                    $(node).removeClass('btn-primary')
                }
            },
            @endif
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
        columnDefs: [
            {
                render: function (data, type, full, meta) {
                    return "<div class='text-wrap'>" + (data || '-') + "</div>";
                },
                targets: '_all'
            }
        ],
        order: [[ 1, "desc" ]]
    });

    $('.js-basic-datatable tbody').on('click', 'a.delete', function () {
        let id = $(this).attr('data-id');
        let url_delete = $(this).attr('data-url');
        id = id.split("-");
        $(this).attr('disabled','disabled');
        swal({
            title: "Hapus Data!",
            text: "Anda yakin akan menghapus data ini ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            confirmButtonText: "Hapus",
            cancelButtonText: "Kembali",
            closeOnConfirm: false,
            closeOnCancel: false
        }, function (isConfirm) {
            if (isConfirm) {
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type:"DELETE",
                    url: url_delete,
                    data: {id: id[1]},
                    success: function(response){
                        if(response.status === 'OK'){
                            swal("Berhasil", response.message, "success");
                            table.ajax.reload();
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
                swal("Dibatalkan", "Data tidak dihapus", "error");
            }
        });
    });
});
</script>
