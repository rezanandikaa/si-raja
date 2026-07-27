<!doctype html>
<html lang="en">

<head>
    <title>{{ env('APP_NAME') }} - Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="{{ env('APP_NAME') }}">
    <meta name="author" content="Moch Diki Widianto">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <!-- VENDOR CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/toastr/toastr.min.css') }}">

    <!-- MAIN CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

</head>

<body data-theme="light" class="font-nunito">
    <!-- WRAPPER -->
    <div id="wrapper" class="theme-dark">
        <div class="vertical-align-wrap">
            <div class="vertical-align-middle auth-main">
                <div class="auth-box">
                    <div class="top">
                        {{-- <img src="{{ asset('assets/images/logo-white.svg') }}" alt="Iconic"> --}}
                    </div>
                    <div class="card">
                        <div class="header">
                            <p class="lead">Masuk ke Aplikasi</p>
                        </div>
                        <div class="body">
                            <form class="form-auth-small" id="loginForm" method="POST" action="{{ route('login') }}">
                                @csrf
                                <div class="form-group">
                                    <label for="signin-email" class="control-label sr-only">Surat Elektronik</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" id="signin-email" placeholder="Masukkan Surel Anda">
                                    @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="signin-password" class="control-label sr-only">Sandi</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" id="signin-password" placeholder="Masukkan Sandi Anda">
                                    @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="signin-captcha" class="control-label sr-only">Captcha</label>
                                    <input id="captcha" type="text" class="form-control ps-15 bg-transparent" placeholder="Masukan Captcha" name="captcha" required>
                                </div>
                                <div class="form-group">
                                    <span class="captcha">{!! captcha_img('flat') !!}</span>
                                    <button type="button" style="margin-left: 10px; margin-top: 0px;" class="btn btn-danger" class="reload" id="reload">
                                        &#x21bb;
                                    </button>
                                </div>
                                <div class="form-group clearfix">
                                    <label class="fancy-checkbox element-left">
                                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <span>Biarkan saya tetap masuk</span>
                                    </label>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg btn-block">MASUK</button>
                                <div class="bottom">
                                    <span class="helper-text m-b-10"><i class="fa fa-lock"></i> <a href="{{ route('forgot') }}">Lupa Sandi?</a></span>
                                    <!-- <span>Belum punya akun? <a href="{{ route('web.home') }}">Daftar</a></span> -->
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END WRAPPER -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/vendor/toastr/toastr.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('#loginForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: $(this).attr("action"),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        toastr.remove();
                        toastr['success']('Login berhasil ...', '', {
                            positionClass: 'toast-top-center'
                        });
                        window.location.href = "{{ route('home') }}";
                    },
                    error: function(xhr) {
                        $('input[type=text]').val('');
                        $('input[type=password]').val('');
                        $('#reload').click();
                        toastr.remove();
                        toastr['error'](xhr.responseJSON.message, '', {
                            positionClass: 'toast-top-center'
                        });
                    }
                });
            });
            $('#reload').click(function() {
                $.ajax({
                    type: 'GET',
                    url: 'reload-captcha',
                    success: function(data) {
                        $("span.captcha").html(data.captcha);
                    }
                });
            });
        });
    </script>
</body>

</html>