<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BorrowIT - @yield('title', 'Asset Borrowing Management')</title>
    <link rel="icon" href="data:,">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <div class="app-layout">
        @include('layouts.sidebar')
        <div class="main-wrapper">
            @include('layouts.header')
            <main class="main-content">
                @if(session('success'))
                    <div class="alert alert-success" id="alert-success">
                        <i data-lucide="check-circle"></i>
                        <span>{{ session('success') }}</span>
                        <button class="alert-close" onclick="this.parentElement.remove()"><i data-lucide="x"></i></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-error" id="alert-error">
                        <i data-lucide="alert-circle"></i>
                        <span>{{ session('error') }}</span>
                        <button class="alert-close" onclick="this.parentElement.remove()"><i data-lucide="x"></i></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-error">
                        <i data-lucide="alert-circle"></i>
                        <div>
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                        <button class="alert-close" onclick="this.parentElement.remove()"><i data-lucide="x"></i></button>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <div class="modal-overlay" id="modal-overlay" style="display:none;" onclick="closeAllModals()"></div>

    @stack('modals')

    <script>
        window.baseUrl = "{{ url('/') }}";
    </script>

    <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
            setTimeout(() => {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(a => { a.style.opacity = '0'; setTimeout(() => a.remove(), 400); });
            }, 4000);
        });
    </script>
</body>
</html>
