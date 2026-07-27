<script type="text/javascript">
$(document).ready(function(){

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

        if ($('input').hasClass('map-input')) {
            if( $('input.map-input').val() == 'null' || $('input.map-input').val() == '' || $('input.map-input').val() == null) {
                toastr.remove();
                toastr['error']('Pilih lokasi terlebih dahulu', '', {
                    positionClass: 'toast-top-center'
                });
                return false;
            }
        }

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
<script>
$(document).ready(function(){

    var $modal = $('#modalUpload');
    var image = document.getElementById('sampleImage');
    var cropper;
    var targetId = '';
    var targetUploadedIdOriginal = '';
    var targetUploadedId = '';
    var wCrop = 400;
    var hCrop = 400;
    var aspectRatio = 1;
    var referenceName = '';
    var referenceId = '';

    this.imageChange = function(cb, key, target, width = 400, height = 400, reference_name = '', reference_id = 0) {
        targetId = target;
        targetUploadedIdOriginal = 'uploaded_' + key;
        targetUploadedId = '#uploaded_' + key;
        wCrop = width;
        hCrop = height;
        aspectRatio = width / height;
        referenceName = reference_name;
        referenceId = reference_id;
        var files = event.target.files;
        var done = function (url) {
            image.src = url;
            $modal.modal('show');
        };
        if (files && files.length > 0) {
            reader = new FileReader();
            reader.onload = function (event) {
                done(reader.result);
            };
            reader.readAsDataURL(files[0]);
        }
        $('input[type=file]').val('');
    }

    $modal.on('shown.bs.modal', function() {
        cropper = new Cropper(image, {
            aspectRatio: aspectRatio,
            viewMode: 2,
            preview: '.preview'
        });
    }).on('hidden.bs.modal', function() {
            cropper.destroy();
            cropper = null;
    });

    $("#crop").click(function(){
        canvas = cropper.getCroppedCanvas({
                width: wCrop,
                height: hCrop,
        });

        canvas.toBlob(function(blob) {
            //url = URL.createObjectURL(blob);
            var reader = new FileReader();
            reader.readAsDataURL(blob);
            reader.onload = function() {
                var base64Data = reader.result;
                displayBase64Image(base64Data);

                var formData = new FormData();
                var newBlob = base64ToBlob(base64Data);
                formData.append('file', newBlob, 'image.png'); // Ganti nama berkas sesuai kebutuhan
                formData.append('width', wCrop);
                formData.append('height', hCrop);
                formData.append('reference_name', referenceName);
                formData.append('reference_id', referenceId);

                // Kirim data berkas ke server menggunakan Ajax
                uploadImage(formData);

                $modal.modal('hide');
            }
        });
    });

    function base64ToBlob(base64Data) {
        var binaryData = atob(base64Data.split(',')[1]);

        // Buat objek Blob
        var blob = new Blob([new Uint8Array(binaryData.length).map(function(_, i) { return binaryData.charCodeAt(i); })], { type: 'image/png' }); // Ganti tipe MIME sesuai dengan data Anda

        return blob;
    }

    function displayBase64Image(base64Data) {
        var imagePreview = document.getElementById(targetUploadedIdOriginal);
        imagePreview.src = base64Data;
    }

    function uploadImage(formData) {
        $.ajax({
            url: "{{route('ajax.upload')}}",
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type: "POST",
            data: formData,
            processData: false, // Diperlukan agar FormData tidak diproses oleh jQuery
            contentType: false, // Diperlukan agar FormData tidak diproses oleh jQuery
            success: function(response) {
                if (response.status === 'FAIL') {
                    customToastr('error', response.message);
                } else {
                    customToastr('success', response.message);
                }
                $(targetId).val(response.data.id);
            },
            error: function(error) {
                console.error("Error:", error);
            }
        });
    }

    function customToastr(status, message) {
        switch (status) {
            case 'error':
                toastr['error']("Terjadi Kesalahan", {
                    positionClass: 'toast-top-center'
                });
                break;

            default:
                toastr['success'](message, "Berhasil", {
                    positionClass: 'toast-top-center'
                });
                break;
        }
    }
});
</script>

@forelse ($data['fields'] as $key => $item)

        @switch($item['type'])
            @case('select')
            @case('data')
                <script>
                    $(document).ready(function () {
                        $("#{{ $key }}").multiselect({
                            enableFiltering: true,
                            enableCaseInsensitiveFiltering: true,
                            maxHeight: 400,
                        });
                    });
                </script>
                @break
            @case('map-marker')
                @if (isset($item['map_type']) && $item['map_type'] == 'google-map')
                    <script type="text/javascript" async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAdJMC6B3wLW1EQRWTw379aOPTp3fHTHMg&libraries=places&callback=initMap">
                    </script>
                    
                    <script type="text/javascript">
                        let map;
                        let latlng = {};
                        var markers = [];
                        function initMap() {
                            var kablebak = {lat: -6.360093328432204, lng: 106.24607078004048};
                            map = new google.maps.Map(document.getElementById('map-{{ $key }}'), {
                                zoom: 14,
                                center: kablebak,
                                mapTypeId: 'hybrid' // hybrid, satellite, roadmap
                            });
                            var kmzUrl = "{{ asset('html/assets/kml/batas-kecamatan.kmz') }}";
                            var ctaLayer = new google.maps.KmlLayer({
                                url: kmzUrl,
                                map: map
                            });
                            google.maps.event.addListener(map, "click", function (event) {
                                var latitude = event.latLng.lat();
                                var longitude = event.latLng.lng();
                                for (i=0; i < markers.length; i++) {
                                    markers[i].setMap(null);
                                }
                                var marker = new google.maps.Marker({
                                    position: {lat: latitude, lng: longitude},
                                    title: 'Lokasi kegiatan',
                                    map: map,
                                    draggable: true
                                });	

                                // $('#t_latitude').val(event.latLng.lat());
                                // $('#t_longitude').val(event.latLng.lng());
                                // $('#latitude').val(event.latLng.lat());
                                // $('#longitude').val(event.latLng.lng());

                                latlng = {lat: latitude, lng: longitude};
                                // console.log(latlng);
                                $('#{{$key}}').val(JSON.stringify(latlng));

                                markers.push(marker);

                                google.maps.event.addListener(marker, 'click', function () {
                                    map.panTo(marker.getPosition());
                                });
                            });

                            //Function searching gmap
                            var input = document.getElementById('pac-input-{{$key}}');
                            var searchBox = new google.maps.places.SearchBox(input);
                            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
                            map.addListener('bounds_changed', function() {
                                searchBox.setBounds(map.getBounds());
                            });
                            searchBox.addListener('places_changed', function() {
                                var places = searchBox.getPlaces();
                                if (places.length == 0) {
                                    return;
                                }

                                var bounds = new google.maps.LatLngBounds();
                                places.forEach(function(place) {
                                    if (!place.geometry) {
                                        console.log("Returned place contains no geometry");
                                        return;
                                    }

                                    if (place.geometry.viewport) {
                                        bounds.union(place.geometry.viewport);
                                    } else {
                                        bounds.extend(place.geometry.location);
                                    }
                                });
                                map.fitBounds(bounds);
                            });
                        }
                        $(document).ready(function () {
                            function mainLocation() {
                                return "Lebak Regency, Banten, Banten, Indonesia";
                            }
                            function googleMap() {
                                return map;
                            }
                            function googleMapSearch(key, location, marker = undefined) {
                                // console.log('googleMapSearch: ', key, location);
                                const mainLocation = window.mainLocation();
                                $("#pac-input-"+key).val(location + ', ' + mainLocation);
                                var searchLocation = $("#pac-input-"+key).val();
                                var geocoder = new google.maps.Geocoder();
                                for (i=0; i < markers.length; i++) {
                                    markers[i].setMap(null);
                                }
                                geocoder.geocode(
                                    {'address': searchLocation}, 
                                    function(results, status) { 
                                        if (status == google.maps.GeocoderStatus.OK) { 
                                            var bounds = new google.maps.LatLngBounds();
                                            bounds.extend(results[0].geometry.location);
                                            window.googleMap().fitBounds(bounds);
                                            window.googleMap().setZoom(14);
                                        } 
                                        else {
                                            alert("Not found: " + status); 
                                        } 
                                    }
                                );
                                if (marker !== undefined) {
                                    marker_obj = JSON.parse(marker);
                                    window.googleMapAddMarker(marker_obj.lat, marker_obj.lng);
                                }
                            }
                            function googleMapAddMarker(lat, lng) {
                                var latitude = lat;
                                var longitude = lng;
                                for (i=0; i < markers.length; i++) {
                                    markers[i].setMap(null);
                                }
                                var marker = new google.maps.Marker({
                                    position: {lat: latitude, lng: longitude},
                                    title: 'Lokasi kegiatan',
                                    map: window.googleMap(),
                                    draggable: true
                                });	

                                // latlng = {lat: latitude, lng: longitude};
                                // console.log(latlng);
                                // $('#{{$key}}').val(JSON.stringify(latlng));

                                markers.push(marker);

                                google.maps.event.addListener(marker, 'click', function () {
                                    window.googleMap().panTo(marker.getPosition());
                                });
                            }

                            window.googleMapSearch = googleMapSearch;
                            window.googleMapAddMarker = googleMapAddMarker;
                            window.mainLocation = mainLocation;
                            window.googleMap = googleMap;
                        });
                    // function initMap() {
                    //     var markers = [];
                    //     var kablebak = {lat: -6.360093328432204, lng: 106.24607078004048};
                    //     var map = new google.maps.Map(document.getElementById('map-{{ $key }}'), {
                    //         zoom: 15,
                    //         center: kablebak
                    //     });
                    //     var kmzUrl = "{{ asset('html/assets/kml/batas-kecamatan.kmz') }}";

                    //     var ctaLayer = new google.maps.KmlLayer({
                    //         url: kmzUrl,
                    //         map: map
                    //     });

                    //     google.maps.event.addListener(map, "click", function (event) {
                    //         var latitude = event.latLng.lat();
                    //         var longitude = event.latLng.lng();
                    //         for (i=0; i < markers.length; i++) {
                    //             markers[i].setMap(null);
                    //         }
                    //         var marker = new google.maps.Marker({
                    //             position: {lat: latitude, lng: longitude},
                    //             title: 'Lokasi kegiatan',
                    //             map: map,
                    //             draggable: true
                    //         });	

                    //         jQuery('#t_latitude').val(event.latLng.lat());
                    //         jQuery('#t_longitude').val(event.latLng.lng());
                    //         jQuery('#latitude').val(event.latLng.lat());
                    //         jQuery('#longitude').val(event.latLng.lng());

                    //         markers.push(marker);

                    //         google.maps.event.addListener(marker, 'click', function () {
                    //             map.panTo(marker.getPosition());
                    //         });
                    //     });


                    //     //Function searching gmap
                    //     var input = document.getElementById('pac-input');
                    //     var searchBox = new google.maps.places.SearchBox(input);
                    //     map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
                    //     map.addListener('bounds_changed', function() {
                    //         searchBox.setBounds(map.getBounds());
                    //     });
                    //     searchBox.addListener('places_changed', function() {
                    //         var places = searchBox.getPlaces();
                    //         if (places.length == 0) {
                    //             return;
                    //         }

                    //         var bounds = new google.maps.LatLngBounds();
                    //         places.forEach(function(place) {
                    //             if (!place.geometry) {
                    //                 console.log("Returned place contains no geometry");
                    //                 return;
                    //             }

                    //             if (place.geometry.viewport) {
                    //                 bounds.union(place.geometry.viewport);
                    //             } else {
                    //                 bounds.extend(place.geometry.location);
                    //             }
                    //         });
                    //         map.fitBounds(bounds);
                    //     });


                    //     //Map default lebak
                    //     function map_lebak() {
                    //         document.getElementById("pac-input").value = 'Lebak Regency, Banten, Indonesia';
                    //         var geocoder = new google.maps.Geocoder();
                    //         geocoder.geocode(
                    //             {'address': jQuery("#pac-input").val()}, 
                    //             function(results, status) { 
                    //                 if (status == google.maps.GeocoderStatus.OK) { 
                    //                     var bounds = new google.maps.LatLngBounds();
                    //                     bounds.extend(results[0].geometry.location);
                    //                     map.fitBounds(bounds);
                    //                     map.setZoom(11);
                    //                 } 
                    //                 else {
                    //                     alert("Not found: " + status); 
                    //                 } 
                    //             }
                    //         );
                    //         for (i=0; i < markers.length; i++) {
                    //             markers[i].setMap(null);
                    //         }
                    //         jQuery('#alamatkegiatan').val('');
                    //         jQuery('#t_latitude').val('');
                    //         jQuery('#t_longitude').val('');
                    //         jQuery('#latitude').val('');
                    //         jQuery('#longitude').val('');
                    //     }
                    // }

                            //Action change kecamatan
                        // document.getElementById("lokasikecamatan_id").addEventListener("change", map_kecamatan, false);
                        // function map_kecamatan() {
                        //     if(jQuery("#lokasikecamatan_id").val() != '') {
                        //         document.getElementById("pac-input").value = jQuery("#lokasikecamatan_id option:selected").text() + ', Lebak Regency, Banten, Banten, Indonesia';
                        //         var geocoder = new google.maps.Geocoder();
                        //         geocoder.geocode(
                        //             {'address': jQuery("#pac-input").val()}, 
                        //             function(results, status) { 
                        //                 if (status == google.maps.GeocoderStatus.OK) { 
                        //                     var bounds = new google.maps.LatLngBounds();
                        //                     bounds.extend(results[0].geometry.location);
                        //                     map.fitBounds(bounds);
                        //                     map.setZoom(14);
                        //                 } 
                        //                 else {
                        //                     alert("Not found: " + status); 
                        //                 } 
                        //             }
                        //         );
                        //     } else {
                        //         map_lebak();
                        //     }
                        // }

                        // document.getElementById("lokasikelurahan_id").addEventListener("change", map_kelurahan, false);
                        // function map_kelurahan() {
                        //     if(jQuery("#lokasikecamatan_id").val() != '' && jQuery("#lokasikelurahan_id").val() != '') {
                        //         document.getElementById("pac-input").value = jQuery("#lokasikelurahan_id option:selected").text() + ',' + jQuery("#lokasikecamatan_id option:selected").text() + ', Lebak Regency, Banten, Banten, Indonesia';
                        //         var geocoder = new google.maps.Geocoder();
                        //         geocoder.geocode(
                        //             {'address': jQuery("#pac-input").val()}, 
                        //             function(results, status) { 
                        //                 if (status == google.maps.GeocoderStatus.OK) { 
                        //                     var bounds = new google.maps.LatLngBounds();
                        //                     bounds.extend(results[0].geometry.location);
                        //                     map.fitBounds(bounds);
                        //                     map.setZoom(14);
                        //                 } 
                        //                 else {
                        //                     alert("Not found: " + status); 
                        //                 } 
                        //             }
                        //         );
                        //     } else {
                        //         map_lebak();
                        //     }
                        // }
                    // }
                    </script>
                    <input id="pac-input-{{$key}}" class="google-maps-controls google-maps-pac-input" type="text" placeholder="Search Box">
                @else
                    <script type="text/javascript">
                    $(document).ready(function () {
                        var map = L.map('map-{{ $key }}').setView([-6.5783, 106.1207], 10); // Set view to Lebak District

                        // Example GeoJSON data with a polygon feature
                        fetch('{{ asset("assets/geojson/P3KE_LEBAK.geojson.json") }}')
                            .then(response => response.json())
                            .then(data => {
                                // Adding GeoJSON layer to the map
                                L.geoJSON(data, {
                                    onEachFeature: function (feature, layer) {
                                        if (feature.properties) {
                                            var tooltipContent = "<b>Kecamatan:</b> " + feature.properties.name + "<br><b>Kode Kemdagri:</b> " + feature.properties['hc-key'];
                                            layer.bindTooltip(tooltipContent);
                                        }
                                    }
                                }).addTo(map);
                            })
                            .catch(error => {
                                console.error('Error loading GeoJSON:', error);
                            });

                        var marker = null; // Variable to store the marker reference
                        // console.log($('#{{$key}}').val());
                        @if (isset($item['value']) && $item['value'] != '')
                        var current_marker = $.parseJSON($('#{{$key}}').val());
                        if (current_marker.lat != undefined && current_marker.lng != undefined) {
                            marker = L.marker([current_marker.lat, current_marker.lng]).addTo(map);
                        }
                        @endif
                        // Function to add or update the marker on map click
                        function addOrUpdateMarker(e) {
                            if (marker) {
                            map.removeLayer(marker); // Remove the existing marker if it exists
                            }

                            // console.log(e.latlng);
                            var latlng = {lat: e.latlng.lat, lng: e.latlng.lng};
                            $('#{{$key}}').val(JSON.stringify(latlng));

                            marker = L.marker(e.latlng).addTo(map); // Add a new marker at the clicked coordinates
                        }

                        // Event listener for map click
                        map.on('click', addOrUpdateMarker);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                        }).addTo(map);
                    });
                    </script>
                @endif
                <div id="map-{{$key}}"></div>

                @break
            @case('data-multi')
                <script>
                    $(document).ready(function () {
                        $("#{{ $key }}").multiselect({
                            allSelectedText: 'Semua',
                            enableFiltering: true,
                            enableCaseInsensitiveFiltering: true,
                            maxHeight: 400,
                            includeSelectAllOption: true
                        });
                    });
                </script>
                @break
            @default

        @endswitch
@empty
@endforelse
