<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Taskly</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

@include('layouts.navbar')

<div class="auth-wrapper">
    <div class="auth-card">

        <h1>Welcome back</h1>
        <p class="subtitle">Sign in to your Taskly account</p>

        {{-- Session status (e.g. after password reset) --}}
        @if(session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="alert-errors">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            {{-- Email --}}
            <div class="form-group">
                <label for="email">Email address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="you@example.com"
                >
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
                @error('password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="remember" id="remember"
                        {{ old('remember') ? 'checked' : '' }}>
                    Keep me signed in
                </label>
            </div>

            <button type="submit" class="btn-submit">Sign In</button>
        </form>

        <div class="auth-divider">or</div>

        <p class="auth-footer">
            Don't have an account?
            <a href="{{ route('register') }}">Create one free</a>
        </p>

    </div>
</div>

</body>
</html>
