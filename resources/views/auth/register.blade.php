<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — Taskly</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

@include('layouts.navbar')

<div class="auth-wrapper">
    <div class="auth-card">

        <h1>Create account</h1>
        <p class="subtitle">Join Taskly — find or offer local services</p>

        {{-- Validation errors (global) --}}
        @if($errors->any())
            <div class="alert-errors">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            {{-- Role selection --}}
            <div class="form-group">
                <label>I want to</label>
                <div class="role-group">

                    <div class="role-option">
                        <input
                            type="radio"
                            id="role_customer"
                            name="role"
                            value="customer"
                            {{ old('role', 'customer') === 'customer' ? 'checked' : '' }}
                        >
                        <label for="role_customer" class="role-label">
                            <span class="role-icon">🛒</span>
                            <span class="role-title">Hire Services</span>
                            <span class="role-desc">Book local professionals</span>
                        </label>
                    </div>

                    <div class="role-option">
                        <input
                            type="radio"
                            id="role_provider"
                            name="role"
                            value="provider"
                            {{ old('role') === 'provider' ? 'checked' : '' }}
                        >
                        <label for="role_provider" class="role-label">
                            <span class="role-icon">🔧</span>
                            <span class="role-title">Offer Services</span>
                            <span class="role-desc">Earn as a professional</span>
                        </label>
                    </div>

                </div>
                @error('role')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Full name --}}
            <div class="form-group">
                <label for="name">Full name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Fatima Ali"
                >
                @error('name')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

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
                    autocomplete="email"
                    placeholder="you@example.com"
                >
                @error('email')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password row --}}
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required
                        autocomplete="new-password"
                        placeholder="Min. 8 characters"
                    >
                    @error('password')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        required
                        autocomplete="new-password"
                        placeholder="Repeat password"
                    >
                </div>
            </div>

            {{-- City / Phone (optional) --}}
            <div class="form-row">
                <div class="form-group">
                    <label for="city">
                        City
                        <span class="optional">(optional)</span>
                    </label>
                    <input
                        type="text"
                        id="city"
                        name="city"
                        class="form-control @error('city') is-invalid @enderror"
                        value="{{ old('city') }}"
                        autocomplete="address-level2"
                        placeholder="Karachi"
                    >
                    @error('city')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">
                        Phone
                        <span class="optional">(optional)</span>
                    </label>
                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}"
                        autocomplete="tel"
                        placeholder="03001234567"
                    >
                    @error('phone')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn-submit">Create Account</button>
        </form>

        <div class="auth-divider">or</div>

        <p class="auth-footer">
            Already have an account?
            <a href="{{ route('login') }}">Sign in</a>
        </p>

    </div>
</div>

</body>
</html>
