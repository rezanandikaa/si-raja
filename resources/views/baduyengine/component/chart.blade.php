@if ($chart['status'])
@switch($chart['type'])
    @case('PIE')
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="body">
                    <div id="pie-{{$chart["id"]}}" style="height: 24rem">
                        <div id="loading-screen-{{ $chart["id"] }}">
                            <img src="{{ asset('assets/images/gif/loading.gif') }}" alt="Loading..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @break
    @case('BAR')
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="body">
                    <div id="bar-{{$chart["id"]}}" style="height: 24rem">
                        <div id="loading-screen-{{ $chart["id"] }}">
                            <img src="{{ asset('assets/images/gif/loading.gif') }}" alt="Loading..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @break
    @case('COLUMN')
        <div class="col-lg-6 col-md-12">
            <div class="card">
                <div class="body">
                    <div id="column-{{$chart["id"]}}" style="height: 24rem">
                        <div id="loading-screen-{{ $chart["id"] }}">
                            <img src="{{ asset('assets/images/gif/loading.gif') }}" alt="Loading..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @break
    @case('MAP')
        <div class="col-lg-6 col-md-12 col-12">
            <div class="card">
                <div class="body">
                    <div id="map-{{$chart["id"]}}" style="height: 48rem">
                        <div id="loading-screen-{{ $chart["id"] }}">
                            <img src="{{ asset('assets/images/gif/loading.gif') }}" alt="Loading..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @break
    @default
        <div class="col-lg-6 col-md-12 col-12">
            <div class="card">
                <div class="body">
                    <div id="custom-{{$chart["id"]}}" style="height: 24rem">
                        <div id="loading-screen-{{ $chart["id"] }}">
                            <img src="{{ asset('assets/images/gif/loading.gif') }}" alt="Loading..." />
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endswitch
@endif

