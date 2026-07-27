    <!-- Ajax JS File -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- HighChart JS File -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/maps/modules/map.js"></script>
    <script src="https://code.highcharts.com/maps/modules/data.js"></script>
    <script src="https://code.highcharts.com/maps/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/maps/modules/offline-exporting.js"></script>
    <script src="https://code.highcharts.com/maps/modules/accessibility.js"></script>

    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <link href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet" type="text/css" />

    <script>
        $(document).ready(function() {
            $(document).ajaxStart(function() {
                $(".loading-screen-map").show();
            });

            $(document).ajaxStop(function() {
                $(".loading-screen-map").hide();
            });

            // Data untuk chart pie
            $.ajax({
                type: "POST",
                url: "{{ route('web.ajax.chart_by_type') }}",
                dataType: "json",
                data: {
                    type: "kk",
                    referer: "api",
                    sub_type: "realization"
                },
                success: function(data) {
                    createMapChart(data);
                },
            });

            function createMapChart(data) {
                // Data Provinsi Indonesia
                // var dataProvinsiIndonesia = [
                //     ['36.02.10', Math.floor(Math.random() * 100)], // Cileles
                //     ['36.02.15', Math.floor(Math.random() * 100)], // Cileles
                //     // Tambahkan data random untuk provinsi lainnya
                // ];

                // URL GeoJSON Kustom
                var geojsonUrl =
                    "{{ asset('assets/geojson/P3KE_LEBAK.geojson.json') }}";

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

                    // const geojson_kab = {};
                    // for (const gjson of geojson.features) {
                    //     const key = "P3KE_" + gjson.properties["hc-key"];
                    //     var url = '{{ asset("assets/geojson/data_.geojson") }}';
                    //     var new_url = url.replace(
                    //         "data_.geojson",
                    //         `${key}.geojson.json`
                    //     );
                    //     geojson_kab[key] = await fetch(new_url).then((response) =>
                    //         response.json()
                    //     );
                    // }

                    // Konfigurasi Chart
                    Highcharts.mapChart('map-container', {
                        chart: {
                            map: geojson, // Menggunakan GeoJSON kustom
                        },
                        title: {
                            text: null,
                        },
                        subtitle: {
                            text: "Sumber Data: P3KE Kemenko PMK - Kabupaten Lebak",
                        },
                        mapNavigation: {
                            enabled: true,
                            buttonOptions: {
                                verticalAlign: "bottom",
                                align: "left",
                            },
                        },
                        colorAxis: {
                            min: 0,
                            stops: [
                                [0, '#EFEFFF'],
                                [0.5, Highcharts.getOptions().colors[0]],
                                [
                                    1,
                                    Highcharts.color(Highcharts.getOptions().colors[0])
                                    .brighten(-0.5).get()
                                ]
                            ]
                        },
                        series: [{
                                type: 'map',
                                // name: 'Kemiskinan',
                                data: data.map,
                                // dataLabels: {
                                //     enabled: true,
                                //     format: '{point.properties.name}'
                                // },
                                // colorAxis: {
                                //     min: minValue,
                                //     max: maxValue,
                                //     minColor: '#FF0000', // Warna terendah
                                //     maxColor: '#00FF00' // Warna tertinggi
                                // },
                                joinBy: "hc-key",
                                name: "Percentil 1 - 2",
                                states: {
                                    hover: {
                                        color: "#8BCEC9",
                                    },
                                },
                                // dataLabels: {
                                //     enabled: true,
                                //     format: '{point.name}'
                                // }
                            },
                            {
                                type: 'mappoint',
                                name: 'Marker',
                                data: data.mappoint,
                            }
                        ],
                        plotOptions: {
                            map: {
                                dataLabels: {
                                    enabled: true,
                                    format: '{point.name}',
                                    style: {
                                        width: '80px', // force line-wrap
                                        // textTransform: 'uppercase',
                                        fontWeight: 'bold',
                                        textOutline: 'none',
                                        // fontSize: '11px',
                                        color: '#333333'
                                    }
                                },
                                tooltip: {
                                    headerFormat: '',
                                    backgroundColor: 'rgba(247,247,247,0.95)',
                                    pointFormat: '<span style="font-size: 13px; font-weight: bold">Kecamatan {point.name}' +
                                        '</span><br>{point.value} Kepala Keluarga',
                                    style: {
                                        width: 170
                                    },
                                    padding: 10,
                                    hideDelay: 1000000
                                },
                            },
                            mappoint: {
                                keys: ['name', 'lat', 'lon', 'activity', 'program', 'budget_allocation'],
                                marker: {
                                    lineWidth: 1,
                                    lineColor: 'red',
                                    fillColor: '#F47174',
                                    symbol: 'mapmarker',
                                    radius: 8
                                },
                                dataLabels: {
                                    enabled: false
                                },
                                tooltip: {
                                    headerFormat: '',
                                    backgroundColor: 'rgba(247,247,247,0.95)',
                                    pointFormat: '<span style="font-size: 12px; font-weight: bold;"> {point.name}' +
                                        '</span><br><span style="font-size: 11px;"><strong>KOORDINAT:</strong> LAT {point.lat} / LNG {point.lon}<br><strong>NOMENKLATUR:</strong> {point.code}<br><strong>PROGRAM:</strong> {point.program}<br><strong>KEGIATAN:</strong> {point.activity}<br><strong>ALOKASI ANGGARAN:</strong> Rp {point.budget_allocation}<br><strong>REALISASI ANGGARAN:</strong> Rp {point.sum_budget_realization}</span>',
                                    style: {
                                        width: 10
                                    },
                                    padding: 10,
                                    hideDelay: 1000000
                                },
                            },
                            series: {
                                states: {
                                    inactive: {
                                        opacity: 1 // Inactive series opacity
                                    }
                                }
                            }
                        },
                    });
                });
            }
        });
    </script>


    <div class="row">
        <div class="body">
            <div id="map-container" class="d-flex align-items-center justify-content-center" style="height: 40rem">
                <div id="loading-screen-map">
                    <img src="{{ asset('assets/images/gif/loading.gif') }}" alt="Loading..." />
                </div>
            </div>
        </div>
    </div>
