@if ($chart['status'])
@php
    // Prefix DOM lama dipertahankan supaya CSS/JS yang sudah menunjuk ke #map-/#pie-/dst tidak berubah.
    $chart_prefix = ['MAP' => 'map', 'PIE' => 'pie', 'BAR' => 'bar', 'COLUMN' => 'column'][$chart['type']] ?? 'custom';
    $chart_dom_id = $chart_prefix . '-' . $chart['id'];
    $chart_height = $chart['type'] === 'MAP' ? '48rem' : '24rem';
@endphp
<div class="col-lg-6 col-md-12 col-12">
    <div class="card">
        <div class="body">
            <div id="{{ $chart_dom_id }}" style="height: {{ $chart_height }}">
                <div id="loading-screen-{{ $chart['id'] }}" data-chart-target="{{ $chart_dom_id }}">
                    <img src="{{ asset('assets/images/gif/loading.gif') }}" alt="Memuat..." />
                </div>
            </div>
        </div>
    </div>
</div>
@endif

