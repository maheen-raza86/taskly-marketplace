<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Taskly') — Taskly</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/provider.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body>

@include('layouts.navbar')

{{-- ── Global flash banners ──────────────────────────────────── --}}
@if(session('success') || session('error') || session('warning'))
    <div class="flash-wrap" id="flashWrap">
        @if(session('success'))
            <div class="flash flash-success" role="alert">
                <span class="flash-icon">✓</span>
                <span class="flash-msg">{{ session('success') }}</span>
                <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Dismiss">×</button>
            </div>
        @endif
        @if(session('error'))
            <div class="flash flash-error" role="alert">
                <span class="flash-icon">✕</span>
                <span class="flash-msg">{{ session('error') }}</span>
                <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Dismiss">×</button>
            </div>
        @endif
        @if(session('warning'))
            <div class="flash flash-warning" role="alert">
                <span class="flash-icon">⚠</span>
                <span class="flash-msg">{{ session('warning') }}</span>
                <button class="flash-close" onclick="this.parentElement.remove()" aria-label="Dismiss">×</button>
            </div>
        @endif
    </div>
    <script>
        // Auto-dismiss after 5 s
        setTimeout(function () {
            var w = document.getElementById('flashWrap');
            if (w) { w.style.opacity = '0'; w.style.transition = 'opacity .4s'; setTimeout(function(){ w.remove(); }, 400); }
        }, 5000);
    </script>
@endif

@yield('content')

@stack('scripts')
</body>
</html>
