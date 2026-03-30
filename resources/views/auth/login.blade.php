<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BorrowIT - Login</title>
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
            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to your account to continue</p>

            @if(session('success'))
                <div class="alert alert-success" style="margin-bottom: 20px;">
                    <i data-lucide="check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <i data-lucide="alert-circle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="login">Username or Email</label>
                    <input type="text" class="form-control" id="login" name="login" value="{{ old('login') }}" placeholder="Enter your username or email" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div style="position: relative;">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" onclick="togglePassword()" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-muted);">
                            <i data-lucide="eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>
                <div class="form-group" style="display:flex; align-items:center; justify-content:space-between;">
                    <label style="display:flex; align-items:center; gap:6px; font-size:13px; color:var(--text-secondary); cursor:pointer;">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                </div>
                <button type="submit" class="btn btn-primary login-btn">
                    <i data-lucide="log-in"></i>
                    Sign In
                </button>
            </form>
            <div class="login-footer">
                <a href="{{ route('forgot-password') }}">Forgot your password?</a>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwd.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }
    </script>
</body>
</html>
