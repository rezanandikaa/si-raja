@inject('menus', 'App\Libraries\Menu')
@php
    $request = request()->route()->getName();
@endphp
<!doctype html>
<html lang="en">

<head>
    <title>{{ env('APP_NAME') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="{{ env('APP_NAME') }}">
    <meta name="author" content="Moch Diki Widianto">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- <link rel="icon" href="favicon.ico" type="image/x-icon"> --}}
    <!-- VENDOR CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/font-awesome.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/fontawesome-free-6.4.2-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/toastr/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/sweetalert/sweetalert.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-multiselect/bootstrap-multiselect.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/multi-select/css/multi-select.css') }}">
    <link rel="stylesheet" href="{{asset('assets/vendor/cropperjs/dist/cropper.min.css')}}">
    <!-- Include Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flat-ui/2.3.0/css/flat-ui.min.css" integrity="sha512-6f7HT84a/AplPkpSRSKWqbseRTG4aRrhadjZezYQ0oVk/B+nm/US5KzQkyyOyh0Mn9cyDdChRdS9qaxJRHayww==" crossorigin="anonymous" referrerpolicy="no-referrer" /> --}}

    @yield('vendor-css')

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <style>
        /* Mengubah tampilan teks dan textarea menjadi huruf kapital */
        input[type="text"].uppercase, textarea.uppercase {
            text-transform: uppercase;
        }
        .multiselect-custom > li > a > label {
            padding: 3px 20px 5px 5px;
        }
        .multiselect-custom+.btn-group ul.multiselect-container>li>a label.checkbox input[type="checkbox"] {
            display: inline;
            visibility: hidden;
            margin-left: -15px;
        }
        .multiselect-custom+.btn-group ul.multiselect-container>li:hover {
            background-color: var(--text-muted)
        }
        .text-dots {
            display: inline-block;
            width: 200px;
            white-space: nowrap;
            overflow: hidden !important;
            text-overflow: ellipsis;
        }
        .text-wrap{
            white-space:normal;
        }
        .width-200{
            width:200px;
        }
        table.js-basic-datatable tbody td {
            vertical-align: top;
        }
        .map-leaf {
            height: 400px;
        }
        .leaflet-top, .leaflet-bottom {
            z-index: 850 !important;
        }
        .js-basic-datatable {
            font-size: 12px;
        }

        .google-maps {height: 400px;width: 500px;}
        .google-maps-controls {margin-top: 10px;border: 1px solid transparent;border-radius: 2px 0 0 2px;box-skegiatang: border-box;-moz-box-skegiatang: border-box;height: 32px;outline: none;box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);}
        .google-maps-pac-input {background-color: #fff;font-family: Roboto;font-size: 15px;font-weight: 300;margin-left: 12px;padding: 0 11px 0 13px;text-overflow: ellipsis;width: 300px;}
        .google-maps-pac-input:focus {border-color: #4d90fe;}
        .pac-container {font-family: Roboto;}
        /* #type-selector {color: #fff;background-color: #4d90fe;padding: 5px 11px 0px 11px;}
        #type-selector label {font-family: Roboto;font-size: 13px;font-weight: 300;}
        #target { width: 345px; } */
    </style>
</head>
<body data-theme="light" class="font-nunito">

<div id="wrapper" class="theme-cyan">

    <!-- Page Loader -->
    <div class="page-loader-wrapper">
        <div class="loader">
            <div class="m-t-30"><img src="{{ asset('assets/images/logo-icon.svg') }}" width="48" height="48" alt="Iconic"></div>
            <p>Please wait...</p>
        </div>
    </div>

    <!-- Top navbar div start -->
    <nav class="navbar navbar-fixed-top">
        <div class="container-fluid">
            <div class="navbar-brand">
                <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-bars"></i></button>
                <button type="button" class="btn-toggle-fullwidth"><i class="fa fa-bars"></i></button>
                {{-- <a href="index.html">ICONIC</a> --}}
            </div>

            <div class="navbar-right">
                {{-- <form id="navbar-search" class="navbar-form search-form">
                    <input value="" class="form-control" placeholder="Search here..." type="text">
                    <button type="button" class="btn btn-default"><i class="icon-magnifier"></i></button>
                </form> --}}

                <div id="navbar-menu">
                    <ul class="nav navbar-nav">
                        {{-- <li class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle icon-menu" data-toggle="dropdown">
                                <i class="fa fa-bell"></i>
                                <span class="notification-dot"></span>
                            </a>
                            <ul class="dropdown-menu notifications">
                                <li class="header"><strong>You have 4 new Notifications</strong></li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="media">
                                            <div class="media-left">
                                                <i class="icon-info text-warning"></i>
                                            </div>
                                            <div class="media-body">
                                                <p class="text">Campaign <strong>Holiday Sale</strong> is nearly reach budget limit.</p>
                                                <span class="timestamp">10:00 AM Today</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="media">
                                            <div class="media-left">
                                                <i class="icon-like text-success"></i>
                                            </div>
                                            <div class="media-body">
                                                <p class="text">Your New Campaign <strong>Holiday Sale</strong> is approved.</p>
                                                <span class="timestamp">11:30 AM Today</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                    <li>
                                    <a href="javascript:void(0);">
                                        <div class="media">
                                            <div class="media-left">
                                                <i class="icon-pie-chart text-info"></i>
                                            </div>
                                            <div class="media-body">
                                                <p class="text">Website visits from Twitter is 27% higher than last week.</p>
                                                <span class="timestamp">04:00 PM Today</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);">
                                        <div class="media">
                                            <div class="media-left">
                                                <i class="icon-info text-danger"></i>
                                            </div>
                                            <div class="media-body">
                                                <p class="text">Error on website analytics configurations</p>
                                                <span class="timestamp">Yesterday</span>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="footer"><a href="javascript:void(0);" class="more">See all notifications</a></li>
                            </ul>
                        </li> --}}
                        <li><a href="javascript:void(0);" class="btn btn-success">Tahun Anggaran: {{ get_budget_year(auth()->user()->budget_year_id) }}</a></li>
                        <li>
                            <a href="{{ route('logout') }}" class="btn btn-danger"
                            onclick="event.preventDefault();
                                          document.getElementById('logout-form').submit();" class="icon-menu"><i class="fa fa-power-off"></i></a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- main left menu -->
    <div id="left-sidebar" class="sidebar">
        <button type="button" class="btn-toggle-offcanvas"><i class="fa fa-arrow-left"></i></button>
        <div class="sidebar-scroll">
            <div class="user-account">
                <img src="{{ get_image(Auth::user()->image_id,asset('assets/images/user.png')) }}" class="rounded-circle user-photo" alt="User Profile Picture">
                <div class="dropdown">
                    <span>Selamat Datang,</span>
                    <a href="javascript:void(0);" class="dropdown-toggle user-name" data-toggle="dropdown"><strong>{{ Auth::user()->name_with_title }}</strong></a>
                    <ul class="dropdown-menu dropdown-menu-right account">
                        {{-- <li><a href="page-profile2.html"><i class="icon-user"></i>Profil</a></li> --}}
                        <li><a href="{{ route('master.user.edit_password') }}"><i class="icon-envelope-open"></i>Ubah Sandi</a></li>
                        <li class="divider"></li>
                        <li><a href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                                          document.getElementById('logout-form').submit();"><i class="icon-power"></i>Keluar</a></li>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </ul>
                </div>
                <hr>
            </div>

            <!-- Tab panes -->
            <div class="tab-content padding-0">
                <div class="tab-pane active" id="menu">
                    <nav id="left-sidebar-nav" class="sidebar-nav">
                        <ul id="main-menu" class="metismenu li_animation_delay">
                            @php
                            $menu = [
                                'home'
                            ];
                            @endphp
                            <li class="{{ is_active($menu, $request) }}">
                                <a href="#Dashboard" class="has-arrow"><i class="fa fa-dashboard"></i><span>Dashboard</span></a>
                                <ul>
                                    <li><a href="{{ route('home') }}">Analisis</a></li>
                                    <li><a href="{{ route('web.home') }}" target="_blank">Halaman Website</a></li>
                                </ul>
                            </li>
                            {!! $menus->getAll() !!}
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- rightbar icon div -->
    <div class="right_icon_bar">
        <ul>
            <li><a href="javascript:void(0);"><i class="fa fa-plus"></i></a></li>
            <li><a href="javascript:void(0);" class="right_icon_btn"><i class="fa fa-angle-right"></i></a></li>
        </ul>
    </div>
