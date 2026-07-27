<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ get_preference('app_name', env('APP_NAME')) }}</title>
    <meta content="Siraja (Sistem Informasi Rakyat Sejahtera) Kabupaten Lebak. Dashboard Pengendalian Dan Pemantauan Konvergensi Pelaksanaan Program Penanggulangan Kemiskinan di Kabupaten Lebak" name="description" />
    <meta content="Siraja, Sistem Informasi Rakyat Sejahtera, Kabupaten Lebak, P3KE Kemenko PMK" name="keywords" />
    <meta content="Moch Diki Widianto - Bapelitbangda Kabupaten Lebak" name="author" />

    <!-- Favicons -->
    <link href="{{ asset('assets_public/logo.png') }}" rel="icon">
    <link href="{{ asset('assets_public/logo.png') }}" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets_public/vendor/aos/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_public/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_public/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_public/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_public/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets_public/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="{{ asset('assets_public/css/style.css') }}" rel="stylesheet">

    <!-- =======================================================
    * Template Name: Scaffold
    * Updated: Sep 18 2023 with Bootstrap v5.3.2
    * Template URL: https://bootstrapmade.com/scaffold-bootstrap-metro-style-template/
    * Author: BootstrapMade.com
    * License: https://bootstrapmade.com/license/
    ======================================================== -->
    <style>
        .carousel-caption {
            background: rgba(0, 0, 0, 0.5);
        }


        #loading-screen-map {
            position: relative;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #loading-screen-map img {
            width: 80px;
            /* Sesuaikan ukuran gambar */
            height: 80px;
        }
        .card {
            margin: 10px !important;
        }
    </style>
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
</head>

<body>

    <div id="preloader"></div>

    <!-- ======= Header ======= -->
    <header id="header" class="fixed-top ">
        <div class="container-fluid">

            <div class="row justify-content-center">
                <div class="col-xl-9 d-flex align-items-center justify-content-lg-between">
                    <!-- <h1 class="logo me-auto me-lg-0">
                        <a href="{{ route('web.home') }}"> </a>
                    </h1> -->

                    <!-- Uncomment below if you prefer to use an image logo -->
                    <a href="{{ route('web.home') }}" class="logo me-auto me-lg-0"><img src="{{asset('assets_public/img/logo.png')}}" alt="Logo" class="img-fluid"></a>

                    <nav id="navbar" class="navbar order-last order-lg-0">
                        <ul>
                            <li><a class="nav-link scrollto active" href="#hero">Beranda</a></li>
                            <li><a class="nav-link scrollto" href="#data">Data</a></li>
                            <li><a class="nav-link scrollto" href="#maps">Peta Kegiatan</a></li>
                            <li><a class="nav-link scrollto" href="#services">Layanan</a></li>
                            <li><a class="nav-link scrollto" href="#portfolio">Infografis</a></li>
                            <li><a class="nav-link scrollto" href="#contact">Hubungi Kami</a></li>
                        </ul>
                        <i class="bi bi-list mobile-nav-toggle"></i>
                    </nav><!-- .navbar -->

                    <a href="{{ route('login') }}" class="get-started-btn scrollto"><span class="bi bi-person-circle"></span>&nbsp; {{ auth()->check() ? auth()->user()->name : 'Login' }}</a>
                </div>
            </div>

        </div>
    </header>
    <!-- End Header -->


    <!-- ======= Hero Section ======= -->
    <section id="hero" class="d-flex flex-column justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8">
                    <h1 id="typed-text"></h1>
                    <h2>Dashboard Pengendalian Dan Pemantauan Konvergensi Pelaksanaan Program Penanggulangan Kemiskinan Kabupaten Lebak</h2>
                    <a href="https://www.youtube.com/watch?v=jDDaplaOz7Q" class="glightbox play-btn mb-4"></a>
                </div>
            </div>
        </div>
    </section><!-- End Hero -->

    <main id="main">

        <!-- ======= Data Section ======= -->
        <section id="data" class="services section-bg">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                    <h2>Data Kemiskinan Ekstrem</h2>
                    <p>Informasi data kemiskinan terkini, dimutakhirkan pada {{ $data['total']['updated_at'] }}</p>
                </div>

                <div class="row align-items-center justify-content-center counters">
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0" data-aos="zoom-in">
                        <div class="icon-box icon-box-pink">
                            <div class="icon"><img src="{{asset('assets_public/img/icon/home.png')}}" alt="" class="mb-3 pb-2" height="100"></div>
                            <h4 class="title"><a href="">Kepala Keluarga</a></h4>
                            <span data-purecounter-start="0" data-purecounter-end="{{ $data['total']['destitution_kk'] }}" data-purecounter-duration="1" class="purecounter"></span>
                            <p class="description">Jumlah Angka Kemiskinan Ekstrem per Kepala Keluarga</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0" data-aos="zoom-in" data-aos-delay="100">
                        <div class="icon-box icon-box-cyan">
                            <div class="icon"><img src="{{asset('assets_public/img/icon/user.png')}}" alt="" class="mb-3 pb-2" height="100"></div>
                            <h4 class="title"><a href="">Individu</a></h4>
                            <span data-purecounter-start="0" data-purecounter-end="{{ $data['total']['destitution_nik'] }}" data-purecounter-duration="1" class="purecounter"></span>
                            <p class="description">Jumlah Angka Kemiskinan Ekstrem per Individu</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0" data-aos="zoom-in" data-aos-delay="100">
                        <div class="icon-box icon-box-green">
                            <div class="icon"><img src="{{asset('assets_public/img/icon/children.png')}}" alt="" class="mb-3 pb-2" height="100"></div>
                            <h4 class="title"><a href="">Risiko Stunting</a></h4>
                            <span data-purecounter-start="0" data-purecounter-end="{{ $data['total']['destitution_kk_stunting'] }}" data-purecounter-duration="1" class="purecounter"></span>
                            <p class="description">Jumlah Angka Keluarga Berisiko Stunting</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0" data-aos="zoom-in" data-aos-delay="100">
                        <div class="icon-box icon-box-blue">
                            <div class="icon"><img src="{{asset('assets_public/img/icon/map.png')}}" alt="" class="mb-3 pb-2" height="100"></div>
                            <h4 class="title"><a href="">Kec. {{ $data['total']['destitution_kk_district'] }}</a></h4>
                            <span data-purecounter-start="0" data-purecounter-end="{{ $data['total']['destitution_kk_district_total'] }}" data-purecounter-duration="1" class="purecounter"></span>
                            <p class="description">Jumlah Angka Kemiskinan Ekstrem Tertinggi</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- End Data Section -->


        <!-- ======= Map Section ======= -->
        <section id="maps" class="maps">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                    <h2>Peta Kegiatan</h2>
                    <p>Berikut Peta Kegiatan Pengentasan Kemiskinan Ekstrem di Kabupaten Lebak.</p>
                </div>

                @include('chart')
            </div>
        </section>
        <!-- End Map Section -->

        <!-- ======= Chart Section ======= -->
        <section id="chart" class="chart">
            <div class="container">
                <div class="row clearfix row-deck">
                    @foreach ($data['charts'] as $chart)
                        @if ($chart['active_flag'])
                        @include('baduyengine.component.chart', $chart)
                        @endif
                    @endforeach
                </div>
            </div>
        </section>

        <!-- ======= Services Section ======= -->
        <section id="services" class="services section-bg">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                    <h2>Layanan</h2>
                    <p>Dashboard ini menyediakan layanan sebagai berikut :</p>
                </div>

                <div class="row">
                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0" data-aos="zoom-in">
                        <div class="icon-box icon-box-pink">
                            <div class="icon"><i class="bx bx-line-chart"></i></div>
                            <h4 class="title"><a href="">Analisis Data</a></h4>
                            <p class="description">Analisis Data Pengentasan Kemiskinan Ekstrem</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0" data-aos="zoom-in" data-aos-delay="100">
                        <div class="icon-box icon-box-cyan">
                            <div class="icon"><i class="bx bx-trending-up"></i></div>
                            <h4 class="title"><a href="">Perencanaan</a></h4>
                            <p class="description">Perencanaan Kegiatan Pengentasan Kemiskinan</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0" data-aos="zoom-in" data-aos-delay="200">
                        <div class="icon-box icon-box-green">
                            <div class="icon"><i class="bx bx-crosshair"></i></div>
                            <h4 class="title"><a href="">Realisasi</a></h4>
                            <p class="description">Realisasi Kegiatan Pengentasan Kemiskinan</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-flex align-items-stretch mb-5 mb-lg-0" data-aos="zoom-in" data-aos-delay="300">
                        <div class="icon-box icon-box-blue">
                            <div class="icon"><i class="bx bx-file"></i></div>
                            <h4 class="title"><a href="">Laporan</a></h4>
                            <p class="description">Laporan Realisasi Kegiatan Pengentasan Kemiskinan</p>
                        </div>
                    </div>

                </div>

            </div>
        </section>
        <!-- End Services Section -->

        <!-- ======= Cta Section ======= -->
        <section id="cta" class="cta">
            <div class="container">

                <div class="row" data-aos="zoom-in">
                    <div class="col-lg-9 text-center text-lg-start">
                        <h3>Analisis Data Kemiskinan Ekstrem</h3>
                        <p> Membantu pemerintah daerah memprioritaskan intervensi berdasarkan kondisi pencapaian dan ketimpangan.</p>
                    </div>
                    <div class="col-lg-3 cta-btn-container text-center">
                        <a class="cta-btn align-middle" href="#">Mulai Analisis</a>
                    </div>
                </div>

            </div>
        </section>
        <!-- End Cta Section -->

        @if (count($data['gallery']) > 0)
        <!-- ======= Infographic Section ======= -->
        <section id="portfolio" class="portfolio">
            <div class="container">

                <div class="section-title aos-init aos-animate" data-aos="fade-up">
                    <h2>Infografis</h2>
                    <p>Infografis Tentang Kemiskinan Ekstrim.</p>
                </div>

                <div class="row">
                    <div class="col-lg-12 d-flex justify-content-center aos-init aos-animate" data-aos="fade-up" data-aos-delay="100">
                        <ul id="portfolio-flters">
                            <li data-filter="*" class="filter-active">Semua</li>
                            <li data-filter=".filter-kegiatan">Kegiatan</li>
                            <li data-filter=".filter-sosialisasi">Sosialisasi</li>
                        </ul>
                    </div>
                </div>

                <div class="row portfolio-container aos-init aos-animate" data-aos="fade-up" data-aos-delay="200" style="position: relative; height: 891px;">
                    @forelse ($data['gallery'] as $item)
                    <div class="col-lg-4 col-md-6 portfolio-item filter-{{strtolower($item['category'])}}" style="position: absolute; left: 0px; top: 0px;">
                        <div class="portfolio-wrap">
                            <img src="{{$item['image_id']}}" class="img-fluid" alt="">
                            <div class="portfolio-info">
                                <h4>{{$item['title']}}</h4>
                                <p>{{$item['category']}}</p>
                            </div>
                            <div class="portfolio-links">
                                <a href="{{$item['image_id']}}" data-gallery="portfolioGallery" class="portfolio-lightbox" title="{{$item['title']}}"><i class="bx bx-plus"></i></a>
                                <a href="{{route('web.home')}}" title="Detail"><i class="bx bx-link"></i></a>
                            </div>
                        </div>
                    </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </section>
        <!-- ======= End Infographic Section ======= -->
        @endif

        <!-- ======= Lebak Section======= -->
        <section id="clients" class="clients">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                    <h3>Pemerintah Kabupaten Lebak</h3>
                    <p>Link Situs Terkait :</p>
                </div>

                <div class="row no-gutters clients-wrap clearfix wow fadeInUp">

                    <div class="col-lg-3 col-md-4 col-xs-6">
                        <div class="client-logo" data-aos="zoom-in">
                            <img src="{{ asset('assets_public/img/clients/1.png') }}" class="img-fluid" alt="">
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-xs-6">
                        <div class="client-logo" data-aos="zoom-in" data-aos-delay="100">
                            <img src="{{ asset('assets_public/img/clients/2.png') }}" class="img-fluid" alt="">
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-xs-6">
                        <div class="client-logo" data-aos="zoom-in" data-aos-delay="150">
                            <img src="{{ asset('assets_public/img/clients/3.png') }}" class="img-fluid" alt="">
                        </div>
                    </div>

                    <div class="col-lg-3 col-md-4 col-xs-6">
                        <div class="client-logo" data-aos="zoom-in" data-aos-delay="200">
                            <img src="{{ asset('assets_public/img/clients/4.png') }}" class="img-fluid" alt="">
                        </div>
                    </div>

                </div>
            </div>
        </section>
        <!-- End Lebak Section -->

        <!-- ======= Testimonials Section ======= -->
        <!-- <section id="testimonials" class="testimonials">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                <h2>Testimonials</h2>
                <p>Magnam dolores commodi suscipit. Necessitatibus eius consequatur ex aliquid fuga eum quidem. Sit sint consectetur velit. Quisquam quos quisquam cupiditate. Et nemo qui impedit suscipit alias ea. Quia fugiat sit in iste officiis commodi quidem hic quas.</p>
                </div>

                <div class="testimonials-slider swiper" data-aos="fade-up" data-aos-delay="100">
                <div class="swiper-wrapper">

                    <div class="swiper-slide">
                    <div class="testimonial-item">
                        <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        Proin iaculis purus consequat sem cure digni ssim donec porttitora entum suscipit rhoncus. Accusantium quam, ultricies eget id, aliquam eget nibh et. Maecen aliquam, risus at semper.
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                        <img src="{{ asset('assets_public/img/testimonials/testimonials-1.jpg') }}" class="testimonial-img" alt="">
                        <h3>Saul Goodman</h3>
                        <h4>Ceo &amp; Founder</h4>
                    </div>
                    </div>

                    <div class="swiper-slide">
                    <div class="testimonial-item">
                        <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        Export tempor illum tamen malis malis eram quae irure esse labore quem cillum quid cillum eram malis quorum velit fore eram velit sunt aliqua noster fugiat irure amet legam anim culpa.
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                        <img src="{{ asset('assets_public/img/testimonials/testimonials-2.jpg') }}" class="testimonial-img" alt="">
                        <h3>Sara Wilsson</h3>
                        <h4>Designer</h4>
                    </div>
                    </div>

                    <div class="swiper-slide">
                    <div class="testimonial-item">
                        <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        Enim nisi quem export duis labore cillum quae magna enim sint quorum nulla quem veniam duis minim tempor labore quem eram duis noster aute amet eram fore quis sint minim.
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                        <img src="{{ asset('assets_public/img/testimonials/testimonials-3.jpg') }}" class="testimonial-img" alt="">
                        <h3>Jena Karlis</h3>
                        <h4>Store Owner</h4>
                    </div>
                    </div>

                    <div class="swiper-slide">
                    <div class="testimonial-item">
                        <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        Fugiat enim eram quae cillum dolore dolor amet nulla culpa multos export minim fugiat minim velit minim dolor enim duis veniam ipsum anim magna sunt elit fore quem dolore labore illum veniam.
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                        <img src="{{ asset('assets_public/img/testimonials/testimonials-4.jpg') }}" class="testimonial-img" alt="">
                        <h3>Matt Brandon</h3>
                        <h4>Freelancer</h4>
                    </div>
                    </div>

                    <div class="swiper-slide">
                    <div class="testimonial-item">
                        <p>
                        <i class="bx bxs-quote-alt-left quote-icon-left"></i>
                        Quis quorum aliqua sint quem legam fore sunt eram irure aliqua veniam tempor noster veniam enim culpa labore duis sunt culpa nulla illum cillum fugiat legam esse veniam culpa fore nisi cillum quid.
                        <i class="bx bxs-quote-alt-right quote-icon-right"></i>
                        </p>
                        <img src="{{ asset('assets_public/img/testimonials/testimonials-5.jpg') }}" class="testimonial-img" alt="">
                        <h3>John Larson</h3>
                        <h4>Entrepreneur</h4>
                    </div>
                    </div>

                </div>
                <div class="swiper-pagination"></div>
                </div>
            </div>
        </section> -->
        <!-- End Testimonials Section -->

        <!-- ======= Team Section ======= -->
        <!-- <section id="team" class="team">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                <h2>Team</h2>
                <p>Magnam dolores commodi suscipit. Necessitatibus eius consequatur ex aliquid fuga eum quidem. Sit sint consectetur velit. Quisquam quos quisquam cupiditate. Et nemo qui impedit suscipit alias ea. Quia fugiat sit in iste officiis commodi quidem hic quas.</p>
                </div>

                <div class="row">

                <div class="col-lg-4 col-md-6">
                    <div class="member" data-aos="zoom-in">
                    <div class="pic"><img src="{{ asset('assets_public/img/team/team-1.jpg') }}" class="img-fluid" alt=""></div>
                    <div class="member-info">
                        <h4>Walter White</h4>
                        <span>Chief Executive Officer</span>
                        <div class="social">
                        <a href=""><i class="bi bi-twitter"></i></a>
                        <a href=""><i class="bi bi-facebook"></i></a>
                        <a href=""><i class="bi bi-instagram"></i></a>
                        <a href=""><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="member" data-aos="zoom-in" data-aos-delay="100">
                    <div class="pic"><img src="{{ asset('assets_public/img/team/team-2.jpg') }}" class="img-fluid" alt=""></div>
                    <div class="member-info">
                        <h4>Sarah Jhonson</h4>
                        <span>Product Manager</span>
                        <div class="social">
                        <a href=""><i class="bi bi-twitter"></i></a>
                        <a href=""><i class="bi bi-facebook"></i></a>
                        <a href=""><i class="bi bi-instagram"></i></a>
                        <a href=""><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="member" data-aos="zoom-in" data-aos-delay="200">
                    <div class="pic"><img src="{{ asset('assets_public/img/team/team-3.jpg') }}" class="img-fluid" alt=""></div>
                    <div class="member-info">
                        <h4>William Anderson</h4>
                        <span>CTO</span>
                        <div class="social">
                        <a href=""><i class="bi bi-twitter"></i></a>
                        <a href=""><i class="bi bi-facebook"></i></a>
                        <a href=""><i class="bi bi-instagram"></i></a>
                        <a href=""><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                    </div>
                </div>

                </div>

            </div>
        </section> -->
        <!-- End Team Section -->

        <!-- ======= Clients Section ======= -->
        <!-- <section id="clients" class="clients">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                <h2>Clients</h2>
                <p>Magnam dolores commodi suscipit. Necessitatibus eius consequatur ex aliquid fuga eum quidem. Sit sint consectetur velit. Quisquam quos quisquam cupiditate. Et nemo qui impedit suscipit alias ea. Quia fugiat sit in iste officiis commodi quidem hic quas.</p>
                </div>

                <div class="row no-gutters clients-wrap clearfix wow fadeInUp">

                <div class="col-lg-3 col-md-4 col-xs-6">
                    <div class="client-logo" data-aos="zoom-in">
                    <img src="{{ asset('assets_public/img/clients/client-1.png') }}" class="img-fluid" alt="">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-xs-6">
                    <div class="client-logo" data-aos="zoom-in" data-aos-delay="100">
                    <img src="{{ asset('assets_public/img/clients/client-2.png') }}" class="img-fluid" alt="">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-xs-6">
                    <div class="client-logo" data-aos="zoom-in" data-aos-delay="150">
                    <img src="{{ asset('assets_public/img/clients/client-3.png') }}" class="img-fluid" alt="">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-xs-6">
                    <div class="client-logo" data-aos="zoom-in" data-aos-delay="200">
                    <img src="{{ asset('assets_public/img/clients/client-4.png') }}" class="img-fluid" alt="">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-xs-6">
                    <div class="client-logo" data-aos="zoom-in" data-aos-delay="250">
                    <img src="{{ asset('assets_public/img/clients/client-5.png') }}" class="img-fluid" alt="">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-xs-6">
                    <div class="client-logo" data-aos="zoom-in" data-aos-delay="300">
                    <img src="{{ asset('assets_public/img/clients/client-6.png') }}" class="img-fluid" alt="">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-xs-6">
                    <div class="client-logo" data-aos="zoom-in" data-aos-delay="350">
                    <img src="{{ asset('assets_public/img/clients/client-7.png') }}" class="img-fluid" alt="">
                    </div>
                </div>

                <div class="col-lg-3 col-md-4 col-xs-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="client-logo">
                    <img src="{{ asset('assets_public/img/clients/client-8.png') }}" class="img-fluid" alt="">
                    </div>
                </div>

                </div>

            </div>
        </section> -->
        <!-- End Clients Section -->

        <!-- ======= Pricing Section ======= -->
        <!-- <section id="pricing" class="pricing section-bg">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                <h2>Pricing</h2>
                <p>Magnam dolores commodi suscipit. Necessitatibus eius consequatur ex aliquid fuga eum quidem. Sit sint consectetur velit. Quisquam quos quisquam cupiditate. Et nemo qui impedit suscipit alias ea. Quia fugiat sit in iste officiis commodi quidem hic quas.</p>
                </div>

                <div class="row">

                <div class="col-lg-3 col-md-6">
                    <div class="box" data-aos="zoom-in">
                    <h3>Free</h3>
                    <h4><sup>$</sup>0<span> / month</span></h4>
                    <ul>
                        <li>Aida dere</li>
                        <li>Nec feugiat nisl</li>
                        <li>Nulla at volutpat dola</li>
                        <li class="na">Pharetra massa</li>
                        <li class="na">Massa ultricies mi</li>
                    </ul>
                    <div class="btn-wrap">
                        <a href="#" class="btn-buy">Buy Now</a>
                    </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-4 mt-md-0">
                    <div class="box featured" data-aos="zoom-in" data-aos-delay="100">
                    <h3>Business</h3>
                    <h4><sup>$</sup>19<span> / month</span></h4>
                    <ul>
                        <li>Aida dere</li>
                        <li>Nec feugiat nisl</li>
                        <li>Nulla at volutpat dola</li>
                        <li>Pharetra massa</li>
                        <li class="na">Massa ultricies mi</li>
                    </ul>
                    <div class="btn-wrap">
                        <a href="#" class="btn-buy">Buy Now</a>
                    </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-4 mt-lg-0">
                    <div class="box" data-aos="zoom-in" data-aos-delay="200">
                    <h3>Developer</h3>
                    <h4><sup>$</sup>29<span> / month</span></h4>
                    <ul>
                        <li>Aida dere</li>
                        <li>Nec feugiat nisl</li>
                        <li>Nulla at volutpat dola</li>
                        <li>Pharetra massa</li>
                        <li>Massa ultricies mi</li>
                    </ul>
                    <div class="btn-wrap">
                        <a href="#" class="btn-buy">Buy Now</a>
                    </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mt-4 mt-lg-0">
                    <div class="box" data-aos="zoom-in" data-aos-delay="300">
                    <span class="advanced">Advanced</span>
                    <h3>Ultimate</h3>
                    <h4><sup>$</sup>49<span> / month</span></h4>
                    <ul>
                        <li>Aida dere</li>
                        <li>Nec feugiat nisl</li>
                        <li>Nulla at volutpat dola</li>
                        <li>Pharetra massa</li>
                        <li>Massa ultricies mi</li>
                    </ul>
                    <div class="btn-wrap">
                        <a href="#" class="btn-buy">Buy Now</a>
                    </div>
                    </div>
                </div>

                </div>

            </div>
        </section> -->
        <!-- End Pricing Section -->

        <!-- ======= F.A.Q Section ======= -->
        <!-- <section id="faq" class="faq">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                <h2>Frequently Asked Questions</h2>
                </div>

                <ul class="faq-list">

                <li>
                    <div data-bs-toggle="collapse" class="collapsed question" href="#faq1">Non consectetur a erat nam at lectus urna duis? <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq1" class="collapse" data-bs-parent=".faq-list">
                    <p>
                        Feugiat pretium nibh ipsum consequat. Tempus iaculis urna id volutpat lacus laoreet non curabitur gravida. Venenatis lectus magna fringilla urna porttitor rhoncus dolor purus non.
                    </p>
                    </div>
                </li>

                <li>
                    <div data-bs-toggle="collapse" href="#faq2" class="collapsed question">Feugiat scelerisque varius morbi enim nunc faucibus a pellentesque? <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq2" class="collapse" data-bs-parent=".faq-list">
                    <p>
                        Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim. Mauris ultrices eros in cursus turpis massa tincidunt dui.
                    </p>
                    </div>
                </li>

                <li>
                    <div data-bs-toggle="collapse" href="#faq3" class="collapsed question">Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi? <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq3" class="collapse" data-bs-parent=".faq-list">
                    <p>
                        Eleifend mi in nulla posuere sollicitudin aliquam ultrices sagittis orci. Faucibus pulvinar elementum integer enim. Sem nulla pharetra diam sit amet nisl suscipit. Rutrum tellus pellentesque eu tincidunt. Lectus urna duis convallis convallis tellus. Urna molestie at elementum eu facilisis sed odio morbi quis
                    </p>
                    </div>
                </li>

                <li>
                    <div data-bs-toggle="collapse" href="#faq4" class="collapsed question">Ac odio tempor orci dapibus. Aliquam eleifend mi in nulla? <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq4" class="collapse" data-bs-parent=".faq-list">
                    <p>
                        Dolor sit amet consectetur adipiscing elit pellentesque habitant morbi. Id interdum velit laoreet id donec ultrices. Fringilla phasellus faucibus scelerisque eleifend donec pretium. Est pellentesque elit ullamcorper dignissim. Mauris ultrices eros in cursus turpis massa tincidunt dui.
                    </p>
                    </div>
                </li>

                <li>
                    <div data-bs-toggle="collapse" href="#faq5" class="collapsed question">Tempus quam pellentesque nec nam aliquam sem et tortor consequat? <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq5" class="collapse" data-bs-parent=".faq-list">
                    <p>
                        Molestie a iaculis at erat pellentesque adipiscing commodo. Dignissim suspendisse in est ante in. Nunc vel risus commodo viverra maecenas accumsan. Sit amet nisl suscipit adipiscing bibendum est. Purus gravida quis blandit turpis cursus in
                    </p>
                    </div>
                </li>

                <li>
                    <div data-bs-toggle="collapse" href="#faq6" class="collapsed question">Tortor vitae purus faucibus ornare. Varius vel pharetra vel turpis nunc eget lorem dolor? <i class="bi bi-chevron-down icon-show"></i><i class="bi bi-chevron-up icon-close"></i></div>
                    <div id="faq6" class="collapse" data-bs-parent=".faq-list">
                    <p>
                        Laoreet sit amet cursus sit amet dictum sit amet justo. Mauris vitae ultricies leo integer malesuada nunc vel. Tincidunt eget nullam non nisi est sit amet. Turpis nunc eget lorem dolor sed. Ut venenatis tellus in metus vulputate eu scelerisque. Pellentesque diam volutpat commodo sed egestas egestas fringilla phasellus faucibus. Nibh tellus molestie nunc non blandit massa enim nec.
                    </p>
                    </div>
                </li>

                </ul>

            </div>
        </section> -->
        <!-- End Frequently Asked Questions Section -->

        <!-- ======= Contact Section ======= -->
        <section id="contact" class="contact section-bg">
            <div class="container">

                <div class="section-title" data-aos="fade-up">
                    <h2>Hubungi Kami</h2>
                </div>

                <div class="row">

                    <div class="col-lg-5 d-flex align-items-stretch" data-aos="fade-right">
                        <div class="info">
                            <div class="address">
                                <i class="bi bi-geo-alt"></i>
                                <h4>Lokasi:</h4>
                                <p>Jl. Rm Nata Atmaja No.5, Rangkasbitung Barat., Kec. Rangkasbitung, Kabupaten Lebak, Banten 42312</p>
                            </div>

                            <!-- <div class="email">
                                <i class="bi bi-envelope"></i>
                                <h4>Email:</h4>
                                <p>info@example.com</p>
                            </div> -->

                            <div class="phone">
                                <i class="bi bi-phone"></i>
                                <h4>Telepon:</h4>
                                <p>(0252) 201431</p>
                            </div>

                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3309.5392063845243!2d106.24460377409828!3d-6.361507362230616!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e4216d432ebe781%3A0xbef920dc5406e33d!2sBadan%20Perencanaan%20Pembangunan%20Daerah%20(Bappeda)%20Kabupaten%20Lebak!5e1!3m2!1sen!2sid!4v1700728151104!5m2!1sen!2sid" frameborder="0" style="border:0; width: 100%; height: 290px;" allowfullscreen></iframe>
                        </div>


                    </div>

                    <div class="col-lg-7 mt-5 mt-lg-0 d-flex align-items-stretch" data-aos="fade-left">
                        <form action="forms/contact.php" method="post" role="form" class="php-email-form">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="name">Your Name</label>
                                    <input type="text" name="name" class="form-control" id="name" required>
                                </div>
                                <div class="form-group col-md-6 mt-3 mt-md-0">
                                    <label for="name">Your Email</label>
                                    <input type="email" class="form-control" name="email" id="email" required>
                                </div>
                            </div>
                            <div class="form-group mt-3">
                                <label for="name">Subject</label>
                                <input type="text" class="form-control" name="subject" id="subject" required>
                            </div>
                            <div class="form-group mt-3">
                                <label for="name">Message</label>
                                <textarea class="form-control" name="message" rows="10" required></textarea>
                            </div>
                            <div class="my-3">
                                <div class="loading">Loading</div>
                                <div class="error-message"></div>
                                <div class="sent-message">Your message has been sent. Thank you!</div>
                            </div>
                            <div class="text-center"><button type="submit">Send Message</button></div>
                        </form>
                    </div>

                </div>

            </div>
        </section><!-- End Contact Section -->

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->
    <footer id="footer">
        <div class="footer-top">
            <div class="container">
                <div class="row">

                    <div class="col-lg-3 col-md-6">
                        <div class="footer-info">
                            <h3>{{ get_preference('app_name', env('APP_NAME')) }}</h3>
                            {{-- <p>
                    A108 Adam Street <br>
                    NY 535022, USA<br><br>
                    <strong>Phone:</strong> +1 5589 55488 55<br>
                    <strong>Email:</strong> info@example.com<br>
                </p> --}}
                            <div class="social-links mt-3">
                                <a href="#" class="twitter"><i class="bx bxl-twitter"></i></a>
                                <a href="#" class="facebook"><i class="bx bxl-facebook"></i></a>
                                <a href="#" class="instagram"><i class="bx bxl-instagram"></i></a>
                                <a href="#" class="google-plus"><i class="bx bxl-skype"></i></a>
                                <a href="#" class="linkedin"><i class="bx bxl-linkedin"></i></a>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="col-lg-2 col-md-6 footer-links">
                <h4>Useful Links</h4>
                <ul>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Home</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">About us</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Services</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Terms of service</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Privacy policy</a></li>
                </ul>
            </div> --}}

                    {{-- <div class="col-lg-3 col-md-6 footer-links">
                <h4>Our Services</h4>
                <ul>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Web Design</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Web Development</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Product Management</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Marketing</a></li>
                <li><i class="bx bx-chevron-right"></i> <a href="#">Graphic Design</a></li>
                </ul>
            </div> --}}

                    <div class="col-lg-4 col-md-6 footer-newsletter">
                        <h4>Berlangganan Berita</h4>
                        {{-- <p>Tamen quem nulla quae legam multos aute sint culpa legam noster magna</p> --}}
                        <form action="" method="post">
                            <input type="email" name="email"><input type="submit" value="Subscribe">
                        </form>

                    </div>

                </div>
            </div>
        </div>

        <div class="container">
            <div class="copyright">
                Hak Cipta &copy; {{ \Carbon\Carbon::now()->format('Y') }} <strong><span>Bapelitbangda Kabupaten Lebak</span></strong>
            </div>
            <div class="credits">
                <!-- All the links in the footer should remain intact. -->
                <!-- You can delete the links only if you purchased the pro version. -->
                <!-- Licensing information: https://bootstrapmade.com/license/ -->
                <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/scaffold-bootstrap-metro-style-template/ -->
                {{-- Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> --}}
            </div>
        </div>
    </footer><!-- End Footer -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Custom JS Files -->
    <script>
        /**
         * Typing Effect
         */
        const textArray = [
            "Selamat Datang di SIRAJA",
            "Sistem Informasi Rakyat Sejahtera Kabupaten Lebak",
        ];
        let index = 0;
        let isDeleting = false;
        let delay = 100; // Waktu jeda setiap karakter

        function type() {
            const text = textArray[index];
            const typedTextElement = document.getElementById("typed-text");

            if (isDeleting) {
                // Menghapus karakter
                typedTextElement.textContent = text.substring(
                    0,
                    typedTextElement.textContent.length - 1
                );
            } else {
                // Menambah karakter
                typedTextElement.textContent = text.substring(
                    0,
                    typedTextElement.textContent.length + 1
                );
            }

            // Mengatur waktu jeda
            delay = isDeleting ? 50 : 100;

            // Cek apakah mengetik selesai atau menghapus selesai
            if (!isDeleting && typedTextElement.textContent === text) {
                isDeleting = true;
                delay = 1000; // Waktu jeda setelah selesai mengetik
            } else if (isDeleting && typedTextElement.textContent === "") {
                isDeleting = false;
                index = (index + 1) % textArray.length; // Pindah ke teks berikutnya setelah selesai menghapus
            }

            // Panggil rekursif untuk fungsi type
            setTimeout(type, delay);
        }

        // Mulai animasi ketika halaman dimuat
        document.addEventListener("DOMContentLoaded", function() {
            type();
        });

        /**
         * Easy selector helper function
         */
        const select = (el, all = false) => {
            el = el.trim();
            if (all) {
                return [...document.querySelectorAll(el)];
            } else {
                return document.querySelector(el);
            }
        };
    </script>

    <!-- Vendor JS Files -->
    <script src="{{ asset('assets_public/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('assets_public/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets_public/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets_public/vendor/isotope-layout/isotope.pkgd.min.js') }}"></script>
    <script src="{{ asset('assets_public/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('assets_public/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('assets_public/vendor/purecounter/purecounter_vanilla.js') }}"></script>


    <!-- Template Main JS File -->
    <script src="{{ asset('assets_public/js/main.js') }}"></script>


    <!-- Include Bootstrap 5 JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    @foreach ($data['charts'] as $chart)
        @if ($chart['active_flag'])
        @include('baduyengine.component-js.chart', $chart)
        @endif
    @endforeach

</body>

</html>
