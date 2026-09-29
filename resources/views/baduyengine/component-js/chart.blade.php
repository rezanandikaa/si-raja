@if ($chart['status'])
@php
    $route_ajax = isset($chart['route_ajax']) ? $chart['route_ajax'] : route('ajax.chart');
@endphp
<script>
$(function () {
    var $loading = $('#loading-screen-{{ $chart["id"] }}');
    var targetId = $loading.data('chart-target');
    // Fallback kalau markup lama masih ter-cache: spinner ada di dalam container chart.
    var $container = targetId ? $('#' + targetId) : $loading.parent();
    var type = @json($chart['type']);
    var title = @json($chart['title']);
    var routeAjax = @json($route_ajax);
    var geojsonUrl = @json(asset('assets/geojson/P3KE_LEBAK.geojson.json'));
    var dashboardId = {{ (int) $chart['dashboard_id'] }};

    if (!$container.length) {
        return console.error('SIRAJA chart: container chart tidak ditemukan');
    }

    // Loading hanya boleh berhenti lewat dua jalur: render berhasil, atau fail().
    // Sebelumnya spinner cuma hilang kalau Highcharts sukses mengganti isi container,
    // jadi request gagal / GeoJSON gagal / Highcharts error = loading selamanya.
    function fail(message) {
        $container.html(
            '<div class="text-muted d-flex align-items-center justify-content-center" ' +
            'style="height:100%;padding:1rem;text-align:center;font-size:13px">' + message + '</div>'
        );
    }

    function chartTitle() {
        return { text: title, style: { fontSize: '14px' } };
    }

    function chartSubtitle() {
        return { text: 'Sumber Data: P3KE Kemenko PMK<br>SIRAJA Kabupaten Lebak', style: { fontSize: '10px' } };
    }

    function permalinkClick() {
        return function (e) {
            if (e.point && e.point.permalink) {
                window.location.href = e.point.permalink;
            }
        };
    }

    function hasData(series) {
        if (Array.isArray(series) && series.length > 0) {
            return true;
        }
        fail('Data grafik belum tersedia.');
        return false;
    }

    // GeoJSON 1.8MB: ambil sekali per halaman, bukan sekali per chart.
    function loadGeojson() {
        if (!window.sirajaGeojson) {
            window.sirajaGeojson = $.getJSON(geojsonUrl).fail(function () {
                window.sirajaGeojson = null;
            });
        }
        return window.sirajaGeojson;
    }

    var renderers = {
        MAP: function (data) {
            if (!hasData(data.map)) return;

            loadGeojson().done(function (geojson) {
                var values = data.map.map(function (point) { return Number(point.value) || 0; });
                var min = Math.min.apply(null, values);
                var max = Math.max.apply(null, values);
                // Data kosong/seragam menghasilkan min=max=Infinity -> colorAxis rusak.
                if (!isFinite(min) || !isFinite(max)) { min = 0; max = 1; }
                if (min === max) { max = min + 1; }

                Highcharts.mapChart(targetId, {
                    chart: { map: geojson },
                    title: chartTitle(),
                    subtitle: chartSubtitle(),
                    mapNavigation: {
                        enabled: true,
                        buttonOptions: { verticalAlign: 'bottom', align: 'left' }
                    },
                    colorAxis: { min: min, max: max, minColor: '#EFEFFF', maxColor: '#33BAB0' },
                    series: [
                        {
                            type: 'map',
                            name: 'Desil 1-4',
                            data: data.map,
                            joinBy: 'hc-key',
                            states: { hover: { color: '#8BCEC9' } },
                            point: { events: { click: permalinkClick() } }
                        },
                        { type: 'mappoint', name: 'Marker', data: data.mappoint || [] }
                    ],
                    plotOptions: {
                        map: {
                            dataLabels: {
                                enabled: true,
                                format: '{point.name}',
                                style: {
                                    width: '80px', // force line-wrap
                                    fontWeight: 'bold',
                                    textOutline: 'none',
                                    color: '#333333',
                                    fontSize: '10px'
                                }
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size: 10px; font-weight: bold">Kec. {point.key}</span><br>',
                                backgroundColor: 'rgba(247,247,247,0.95)',
                                pointFormat: '<span style="font-size: 10px;">Total: {point.value}</span>',
                                style: { width: 200, fontSize: '10px', color: '#000000' },
                                padding: 10,
                                hideDelay: 1000000
                            }
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
                            dataLabels: { enabled: false },
                            tooltip: {
                                headerFormat: '',
                                backgroundColor: 'rgba(247,247,247,0.95)',
                                pointFormat: '<span style="font-size: 10px; font-weight: bold;"> {point.name}' +
                                    '</span><br><span style="font-size: 10px;"><strong>KOORDINAT:</strong> LAT {point.lat} / LNG {point.lon}<br><strong>NOMENKLATUR:</strong> {point.code}<br><strong>PROGRAM:</strong> {point.program}<br><strong>KEGIATAN:</strong> {point.activity}<br><strong>ALOKASI ANGGARAN:</strong> Rp {point.budget_allocation}</span>',
                                style: { width: 200, fontSize: '10px', color: '#000000' },
                                padding: 10,
                                hideDelay: 1000000
                            }
                        },
                        series: { states: { inactive: { opacity: 1 } } }
                    }
                });
            }).fail(function () {
                fail('Gagal memuat peta. Periksa koneksi lalu muat ulang halaman.');
            });
        },

        PIE: function (data) {
            if (!hasData(data.pie)) return;

            Highcharts.chart(targetId, {
                chart: { type: 'pie' },
                title: chartTitle(),
                subtitle: chartSubtitle(),
                plotOptions: {
                    pie: {
                        aspectRatio: '1:1',
                        size: '60%',
                        dataLabels: { style: { fontSize: '10px' } },
                        events: { click: permalinkClick() }
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-weight: bold">{point.key}</span><br />',
                    backgroundColor: 'rgba(247,247,247,0.95)',
                    pointFormat: 'Total: {point.y} ({point.percentage:.1f}%)',
                    style: { width: 200, fontSize: '10px', color: '#000000' },
                    padding: 10
                },
                series: [{ name: 'Jumlah', colorByPoint: true, data: data.pie, showInLegend: false }]
            });
        },

        BAR: function (data) {
            if (!data.bar || !hasData(data.bar.value)) return;

            Highcharts.chart(targetId, {
                chart: { type: 'bar' },
                title: chartTitle(),
                subtitle: chartSubtitle(),
                plotOptions: {
                    bar: {
                        dataLabels: { style: { fontSize: '10px' } },
                        events: {
                            click: function () {
                                if (data.bar.permalink) {
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
                    style: { width: 200, fontSize: '10px', color: '#000000' },
                    padding: 10
                },
                xAxis: {
                    categories: data.bar.x_axis,
                    labels: { style: { fontSize: '10px', width: 70 } }
                },
                yAxis: {
                    title: { text: 'Jumlah' },
                    labels: { style: { fontSize: '10px' } },
                    tickInterval: data.bar.interval
                },
                series: [{ name: 'Jumlah', colorByPoint: true, data: data.bar.value, showInLegend: false }]
            });
        },

        COLUMN: function (data) {
            if (!data.column || !hasData(data.column.value)) return;

            Highcharts.chart(targetId, {
                chart: { type: 'column' },
                title: chartTitle(),
                subtitle: chartSubtitle(),
                plotOptions: {
                    column: {
                        dataLabels: { style: { fontSize: '10px' } },
                        events: {
                            click: function () {
                                if (data.column.permalink) {
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
                    style: { width: 200, fontSize: '10px', color: '#000000' },
                    padding: 10
                },
                xAxis: {
                    categories: data.column.x_axis,
                    labels: { style: { fontSize: '10px' } }
                },
                yAxis: {
                    title: { text: 'Jumlah' },
                    labels: { style: { fontSize: '10px' } },
                    tickInterval: data.column.interval
                },
                series: [{ name: 'Jumlah', colorByPoint: true, data: data.column.value, showInLegend: false }]
            });
        },

        // Tipe di luar MAP/PIE/BAR/COLUMN: rencana vs realisasi.
        OTHER: function (data) {
            if (!data.column || !data.column.value) {
                return fail('Data grafik belum tersedia.');
            }

            Highcharts.chart(targetId, {
                chart: { type: 'column' },
                title: chartTitle(),
                subtitle: chartSubtitle(),
                plotOptions: { column: { dataLabels: { style: { fontSize: '10px' } } } },
                tooltip: {
                    headerFormat: '<span style="font-weight: bold">{point.key}</span><br />',
                    backgroundColor: 'rgba(247,247,247,0.95)',
                    pointFormat: '<span style="font-weight: bold">{series.name}</span><br />Total: {point.y}',
                    style: { width: 200, fontSize: '10px', color: '#000000' },
                    padding: 10
                },
                xAxis: {
                    categories: data.column.x_axis,
                    labels: { style: { fontSize: '10px' } }
                },
                yAxis: {
                    title: { text: 'Jumlah' },
                    labels: { style: { fontSize: '10px' } },
                    tickInterval: data.column.interval
                },
                series: [
                    { name: 'RENCANA KEGIATAN', data: data.column.value.program, showInLegend: false },
                    { name: 'REALISASI KEGIATAN', data: data.column.value.realization, showInLegend: false }
                ]
            });
        }
    };

    if (typeof Highcharts === 'undefined') {
        return fail('Library grafik belum termuat. Muat ulang halaman.');
    }

    $.ajax({
        type: 'POST',
        url: routeAjax,
        dataType: 'json',
        data: { id: dashboardId },
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    }).done(function (data) {
        try {
            (renderers[type] || renderers.OTHER)(data || {});
        } catch (e) {
            console.error('SIRAJA chart: gagal render', e);
            fail('Gagal menampilkan grafik.');
        }
    }).fail(function (xhr) {
        fail('Gagal memuat data grafik (' + (xhr.status || 'jaringan') + '). Muat ulang halaman.');
    });
});
</script>
@endif
