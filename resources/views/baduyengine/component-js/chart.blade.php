@if ($chart['status'])
@php
    $route_ajax = isset($chart['route_ajax']) ? $chart['route_ajax'] : route('ajax.chart');
@endphp
@switch($chart['type'])
    @case('MAP')
        <script>
        $(document).ready(function() {
            $(document).ajaxStart(function () {
                $('.loading-screen-{{ $chart["id"] }}').show();
            });

            $(document).ajaxStop(function () {
                $('.loading-screen-{{ $chart["id"] }}').hide();
            });

            // Data untuk chart pie
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type: "POST",
                url: "{{ $route_ajax }}",
                dataType: 'json',
                data: { id: "{{ $chart['dashboard_id'] }}" },
                success: function(data) {
                    createMapChart(data, 'map-{{ $chart["id"] }}');
                }
            });

            function createMapChart (data, id) {
                // Data Provinsi Indonesia
                // var dataProvinsiIndonesia = [
                //     ['36.02.10', Math.floor(Math.random() * 100)], // Cileles
                //     ['36.02.15', Math.floor(Math.random() * 100)], // Cileles
                //     // Tambahkan data random untuk provinsi lainnya
                // ];

                // URL GeoJSON Kustom
                var geojsonUrl = "{{ asset('assets/geojson/P3KE_LEBAK.geojson.json') }}";

                // Menentukan nilai terendah dan tertinggi
                var minValue = 0;
                var maxValue = 100000;

                // Mengambil GeoJSON dari URL menggunakan jQuery
                $.getJSON(geojsonUrl, async function (geojson) {
                    // Menghitung nilai minimum dan maksimum dari data
                    var min = Infinity;
                    var max = -Infinity;
                    data.map.forEach(function(point) {
                        min = Math.min(min, point.value);
                        max = Math.max(max, point.value);
                    });

                    // const geojson_kab = {};
                    // for (const gjson of geojson.features) {
                    //     const key = "P3KE_" + gjson.properties['hc-key'];
                    //     var url = '{{ asset("assets/geojson/data_.geojson") }}';
                    //     var new_url = url.replace("data_.geojson", `${key}.geojson.json`);
                    //     geojson_kab[key] = await fetch(
                    //         new_url
                    //     ).then(response => response.json());
                    // }

                    // Konfigurasi Chart
                    Highcharts.mapChart(id, {
                        chart: {
                            map: geojson // Menggunakan GeoJSON kustom
                        },
                        title: {
                            text: '{{ $chart["title"] }}',
                            style: {
                                fontSize: '14px'
                            }
                        },
                        subtitle: {
                            text: 'Sumber Data: P3KE Kemenko PMK<br>SIRAJA Kabupaten Lebak',
                            style: {
                                fontSize: '10px'
                            }
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
                        series: [
                            {
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
                                joinBy: 'hc-key',
                                name: 'Desil 1-4',
                                states: {
                                    hover: {
                                        color: '#8BCEC9'
                                    }
                                },
                                point: {
                                    events: {
                                        click: function(e) {
                                            // console.log('ok', e.point);
                                            if (e.point.permalink != '') {
                                                window.location.href = e.point.permalink;
                                            }
                                        }
                                    }
                                },
                                // dataLabels: {
                                //     enabled: true,
                                //     format: '{point.name}'
                                // }
                            },
                            {
                                type: 'mappoint',
                                name: 'Marker',
                                data: data.mappoint
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
                                        color: '#333333',
                                        fontSize: '10px',
                                    }
                                },
                                tooltip: {
                                    headerFormat: '<span style="font-size: 10px; font-weight: bold">Kec. {point.key}</span><br>',
                                    backgroundColor: 'rgba(247,247,247,0.95)',
                                    pointFormat: '<span style="font-size: 10px;">Total: {point.value}</span>',
                                    style: {
                                        width: 200,
                                        fontSize: '10px'
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
                                    radius: 6
                                },
                                dataLabels: {
                                    enabled: false
                                },
                                tooltip: {
                                    headerFormat: '',
                                    backgroundColor: 'rgba(247,247,247,0.95)',
                                    pointFormat: '<span style="font-size: 10px; font-weight: bold;"> {point.name}' +
                                        '</span><br><span style="font-size: 10px;"><strong>KOORDINAT:</strong> LAT {point.lat} / LNG {point.lon}<br><strong>NOMENKLATUR:</strong> {point.code}<br><strong>PROGRAM:</strong> {point.program}<br><strong>KEGIATAN:</strong> {point.activity}<br><strong>ALOKASI ANGGARAN:</strong> Rp {point.budget_allocation}</span>',
                                    style: {
                                        width: 200,
                                        fontSize: '10px'
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
                        // drilldown: {
                        //     activeDataLabelStyle: {
                        //         color: '#FFFFFF',
                        //         textDecoration: 'none',
                        //         textOutline: '1px #000000'
                        //     },
                        //     breadcrumbs: {
                        //         floating: true,
                        //         showFullPath: false
                        //     },
                        //     mapZooming: true,
                        //     series: [
                        //         {
                        //             id: '360201',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360201,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360202',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360202,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360203',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360203,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360204',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360204,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360205',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360205,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360206',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360206,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360207',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360207,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360208',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360208,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360209',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360209,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360210',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360210,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360201',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360201,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360211',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360211,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360212',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360212,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360213',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360213,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360201',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360201,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360214',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360214,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360215',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360215,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360216',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360216,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360217',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360217,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360218',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360218,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360219',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360219,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360220',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360220,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360221',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360221,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360222',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360222,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360223',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360223,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360224',
                        //             name: 'Kalanganyar',
                        //             mapData: geojson_kab.P3KE_360224,
                        //             data: [{
                        //                         'hc-key': '3602242001',
                        //                         url: 'http://google.com',
                        //                         value: Math.floor(Math.random() * 10000),
                        //                         events: {
                        //                             click: function(e) {
                        //                                 console.log('ok', e.point);
                        //                                 if (e.point.url) {
                        //                                     window.location.href = e.point.url;
                        //                                 }
                        //                             }
                        //                         }
                        //                     },
                        //                 ['3602242002', Math.floor(Math.random() * 10000)],
                        //                 ['3602242003', Math.floor(Math.random() * 10000)],
                        //                 ['3602242004', Math.floor(Math.random() * 10000)],
                        //                 ['3602242005', Math.floor(Math.random() * 10000)],
                        //                 ['3602242006', Math.floor(Math.random() * 10000)],
                        //                 ['3602242007', Math.floor(Math.random() * 10000)],
                        //             ],
                        //         },
                        //         {
                        //             id: '360225',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360225,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360226',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360226,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360227',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360227,
                        //             data: [],
                        //         },
                        //         {
                        //             id: '360228',
                        //             name: 'Desil 1-4',
                        //             mapData: geojson_kab.P3KE_360228,
                        //             data: [],
                        //         },
                        //     ],
                        // },
                    });
                });
            }
        });
        </script>
        @break
    @case('PIE')
        <script>
            $(document).ready(function() {
                $(document).ajaxStart(function () {
                    $('.loading-screen-{{ $chart["id"] }}').show();
                });

                $(document).ajaxStop(function () {
                    $('.loading-screen-{{ $chart["id"] }}').hide();
                });

                // Data untuk chart pie
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type: "POST",
                    url: "{{ $route_ajax }}",
                    dataType: 'json',
                    data: { id: "{{ $chart['dashboard_id'] }}" },
                    success: function(data) {
                        createPieChart(data, 'pie-{{ $chart["id"] }}');
                    }
                });

                function createPieChart (data, id) {
                    // Membuat chart pie
                    Highcharts.chart(id, {
                        chart: {
                            type: 'pie'
                        },
                        title: {
                            text: '{{ $chart["title"] }}',
                            style: {
                                fontSize: '14px'
                            }
                        },
                        subtitle: {
                            text: 'Sumber Data: P3KE Kemenko PMK<br>SIRAJA Kabupaten Lebak',
                            style: {
                                fontSize: '10px'
                            }
                        },
                        plotOptions: {
                            pie: {
                                aspectRatio: '1:1',
                                size: '60%',
                                dataLabels: {
                                    style: {
                                        fontSize: '10px',
                                    }
                                },
                                events: {
                                    click: function(e) {
                                        if (e.point.permalink != '') {
                                            window.location.href = e.point.permalink;
                                        }
                                    }
                                }
                            }
                        },
                        // tooltip: {
                        //     pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                        // },
                        tooltip: {
                            headerFormat: '<span style="font-weight: bold">{point.key}</span><br />',
                            backgroundColor: 'rgba(247,247,247,0.95)',
                            pointFormat: 'Total: {point.y} ({point.percentage:.1f}%)',
                            style: {
                                width: 200,
                                fontSize: '10px'
                            },
                            padding: 10,
                        },
                        series: [{
                            name: 'Jumlah',
                            colorByPoint: true,
                            data: data.pie,
                            showInLegend: false
                        }]
                    });
                }
            });
        </script>
        @break
    @case('BAR')
        <script>
            $(document).ready(function() {
                $(document).ajaxStart(function () {
                    $('.loading-screen-{{ $chart["id"] }}').show();
                });

                $(document).ajaxStop(function () {
                    $('.loading-screen-{{ $chart["id"] }}').hide();
                });

                // Data untuk chart pie
                $.ajax({
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type: "POST",
                    url: "{{ $route_ajax }}",
                    dataType: 'json',
                    data: { id: "{{ $chart['dashboard_id'] }}" },
                    success: function(data) {
                        createBarChart(data, 'bar-{{ $chart["id"] }}');
                    }
                });

                function createBarChart (data, id) {
                    // Membuat chart pie
                    Highcharts.chart(id, {
                        chart: {
                            type: 'bar'
                        },
                        title: {
                            text: '{{ $chart["title"] }}',
                            style: {
                                fontSize: '14px'
                            }
                        },
                        subtitle: {
                            text: 'Sumber Data: P3KE Kemenko PMK<br>SIRAJA Kabupaten Lebak',
                            style: {
                                fontSize: '10px'
                            }
                        },
                        plotOptions: {
                            bar: {
                                dataLabels: {
                                    style: {
                                        fontSize: '10px',
                                    }
                                },
                                events: {
                                    click: function(e) {
                                        if (data.bar.permalink != '') {
                                            window.location.href = data.bar.permalink;
                                        }
                                    }
                                }
                            }
                        },
                        tooltip: {
                            headerFormat: '<span style="font-weight: bold">{point.key}</span><br />',
                            backgroundColor: 'rgba(247,247,247,0.95)',
                            pointFormat: 'Total: {point.y}',
                            style: {
                                width: 200,
                                fontSize: '10px'
                            },
                            padding: 10,
                        },
                        xAxis: {
                            categories: data.bar.x_axis,
                            labels: {
                                style: {
                                    fontSize: '10px', // Ubah ukuran font di sini,
                                    width: 70,
                                }
                            }
                        },
                        yAxis: {
                            title: {
                                text: 'Jumlah'
                            },
                            labels: {
                                style: {
                                    fontSize: '10px' // Ubah ukuran font di sini
                                }
                            },
                            tickInterval: data.bar.interval,
                        },
                        series: [{
                            name: 'Jumlah',
                            colorByPoint: true,
                            data: data.bar.value,
                            showInLegend: false
                        }]
                    });
                }
            });
        </script>
        @break
    @case('COLUMN')
    <script>
        $(document).ready(function() {
            $(document).ajaxStart(function () {
                $('.loading-screen-{{ $chart["id"] }}').show();
            });

            $(document).ajaxStop(function () {
                $('.loading-screen-{{ $chart["id"] }}').hide();
            });

            // Data untuk chart pie
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type: "POST",
                url: "{{ $route_ajax }}",
                dataType: 'json',
                data: { id: "{{ $chart['dashboard_id'] }}" },
                success: function(data) {
                    createColumnChart(data, 'column-{{ $chart["id"] }}');
                }
            });

            function createColumnChart (data, id) {
                // Membuat chart pie
                Highcharts.chart(id, {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '{{ $chart["title"] }}',
                        style: {
                            fontSize: '14px'
                        }
                    },
                    subtitle: {
                        text: 'Sumber Data: P3KE Kemenko PMK<br>SIRAJA Kabupaten Lebak',
                        style: {
                            fontSize: '10px'
                        }
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                style: {
                                    fontSize: '10px',
                                }
                            },
                            events: {
                                click: function(e) {
                                    if (data.column.permalink != '') {
                                        window.location.href = data.column.permalink;
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-weight: bold">{point.key}</span><br />',
                        backgroundColor: 'rgba(247,247,247,0.95)',
                        pointFormat: 'Total: {point.y}',
                        style: {
                            width: 200,
                            fontSize: '10px'
                        },
                        padding: 10,
                    },
                    xAxis: {
                        categories: data.column.x_axis,
                        labels: {
                            style: {
                                fontSize: '10px', // Ubah ukuran font di sini
                            }
                        }
                    },
                    yAxis: {
                        title: {
                            text: 'Jumlah'
                        },
                        labels: {
                            style: {
                                fontSize: '10px' // Ubah ukuran font di sini
                            }
                        },
                        tickInterval: data.column.interval,
                    },
                    series: [{
                        name: 'Jumlah',
                        colorByPoint: true,
                        data: data.column.value,
                        showInLegend: false
                    }]
                });
            }
        });
    </script>
    @break
    @default
    <script>
        $(document).ready(function() {
            $(document).ajaxStart(function () {
                $('.loading-screen-{{ $chart["id"] }}').show();
            });

            $(document).ajaxStop(function () {
                $('.loading-screen-{{ $chart["id"] }}').hide();
            });

            // Data untuk chart pie
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type: "POST",
                url: "{{ $route_ajax }}",
                dataType: 'json',
                data: { id: "{{ $chart['dashboard_id'] }}" },
                success: function(data) {
                    createColumnChart(data, 'custom-{{ $chart["id"] }}');
                }
            });

            function createColumnChart (data, id) {
                // Membuat chart pie
                Highcharts.chart(id, {
                    chart: {
                        type: 'column'
                    },
                    title: {
                        text: '{{ $chart["title"] }}',
                        style: {
                            fontSize: '14px'
                        }
                    },
                    subtitle: {
                        text: 'Sumber Data: P3KE Kemenko PMK<br>SIRAJA Kabupaten Lebak',
                        style: {
                            fontSize: '10px'
                        }
                    },
                    plotOptions: {
                        column: {
                            dataLabels: {
                                style: {
                                    fontSize: '10px',
                                }
                            }
                        }
                    },
                    tooltip: {
                        headerFormat: '<span style="font-weight: bold">{point.key}</span><br />',
                        backgroundColor: 'rgba(247,247,247,0.95)',
                        pointFormat: '<span style="font-weight: bold">{series.name}</span><br />Total: {point.y}',
                        style: {
                            width: 200,
                            fontSize: '10px'
                        },
                        padding: 10,
                    },
                    xAxis: {
                        categories: data.column.x_axis,
                        labels: {
                            style: {
                                fontSize: '10px', // Ubah ukuran font di sini
                            }
                        }
                    },
                    yAxis: {
                        title: {
                            text: 'Jumlah'
                        },
                        labels: {
                            style: {
                                fontSize: '10px' // Ubah ukuran font di sini
                            }
                        },
                        tickInterval: data.column.interval,
                    },
                    series: [
                        {
                            name: 'RENCANA KEGIATAN',
                            data: data.column.value.program,
                            showInLegend: false
                        },
                        {
                            name: 'REALISASI KEGIATAN',
                            data: data.column.value.realization,
                            showInLegend: false
                        },
                    ]
                });
            }
        });
    </script>
@endswitch
@endif
