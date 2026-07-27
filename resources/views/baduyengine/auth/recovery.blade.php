<!doctype html>
<html lang="en">

<head>
<title>{{ env('APP_NAME') }} - Lupa Sandi</title>
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
	<div id="wrapper" class="theme-cyan">
		<div class="vertical-align-wrap">
			<div class="vertical-align-middle auth-main">
				<div class="auth-box">
                    <div class="top">
                        <img src="{{ asset('assets/images/logo-white.svg') }}" alt="Iconic">
                    </div>
					<div class="card">
                        <div class="header">
                            <p class="lead">Pulihkan Sandi</p>
                        </div>
                        <div class="body">
                            <p>Masukkan Sandi Baru</p>
                            <form class="form-auth-small" id="forgotForm" method="POST" action="{{ route('go.recovery', ['token' => $data['token']]) }}">
                                @csrf
                                <div class="form-group">
                                    <input type="password" name="password" class="form-control" placeholder="Sandi" required />
                                    <br />
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi Sandi" required />
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg btn-block">UBAH SANDI</button>
                                <div class="bottom">
                                    <span class="helper-text">Sudah Ingat? <a href="{{ route('login') }}">Kembali</a></span>
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
            $('#forgotForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                $.ajax({
                    url: $(this).attr("action"),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        toastr.remove();
                        if (response.status == 'OK'){
                            toastr['success'](response.message, '', {
                                positionClass: 'toast-top-center'
                            });
                            window.location.href = '{{ route('login') }}';
                        } else {
                            toastr['error'](response.message, '', {
                                positionClass: 'toast-top-center'
                            });
                        }
                    },
                    error: function(xhr) {
                        $('input[type=email]').val('');
                        toastr.remove();
                        toastr['error'](xhr.responseJSON.message, '', {
                            positionClass: 'toast-top-center'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>

