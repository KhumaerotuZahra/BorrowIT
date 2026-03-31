<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BorrowIT - Forgot Password</title>
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
            <h1 class="login-title">Forgot Password</h1>
            <p class="login-subtitle">Enter your @ptbpi.co.id email to receive a password reset link</p>

            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <i data-lucide="check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <i data-lucide="alert-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <i data-lucide="alert-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('forgot-password') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" placeholder="yourname@ptbpi.co.id" required autofocus>
                    <small style="font-size:11px;color:var(--text-muted);margin-top:4px;display:block;">Only @ptbpi.co.id email addresses are accepted</small>
                </div>
                <button type="submit" class="btn btn-primary login-btn">
                    <i data-lucide="mail"></i>
                    Send Reset Link
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
