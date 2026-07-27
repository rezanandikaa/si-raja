<!DOCTYPE html>
<html>
<head>
    <title>{{ env('APP_NAME') }} - Password Reset</title>
</head>
<body>
    <p>Hello,</p>

    <p>We received a request to reset your password. If you didn't make this request, you can ignore this email.</p>

    <p>To reset your password, click the following link:</p>

    <p><a href="{{ route('go.forgot.reset', ['token' => $data['token']]) }}">Reset Password</a></p>

    <p>If you're having trouble clicking the link, you can copy and paste it into your browser's address bar.</p>

    <p>This password reset link will expire in {{ $data['expired'] }}.</p>

    <p>If you did not request a password reset, no further action is required.</p>

    <p>Best regards,<br>Your Website Team</p>
</body>
</html>
