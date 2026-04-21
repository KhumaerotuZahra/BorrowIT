<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BorrowIT - Reset Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo">
                <span class="logo-text"><span class="logo-borrow">Borrow</span><span class="logo-it">IT</span></span>
            </div>
            <h1 class="login-title">Reset Password</h1>
            <p class="login-subtitle">Enter your new password below</p>

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <i data-lucide="alert-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('reset-password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $email ?? old('email') }}" placeholder="yourname@gmail.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Minimum 8 characters" required minlength="8">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Repeat your new password" required>
                </div>
                <button type="submit" class="btn btn-primary login-btn">
                    <i data-lucide="key"></i>
                    Reset Password
                </button>
            </form>
            <div class="login-footer">
                <a href="{{ route('login') }}">Back to Login</a>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>
