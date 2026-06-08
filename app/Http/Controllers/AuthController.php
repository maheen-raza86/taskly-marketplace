<?php

namespace App\Http\Controllers;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ─── Register ─────────────────────────────────────────────────────────────

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'role'     => ['required', 'in:customer,provider'],
            'city'     => ['nullable', 'string', 'max:100'],
            'phone'    => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'city'     => $validated['city'] ?? null,
            'phone'    => $validated['phone'] ?? null,
        ]);

        // Auto-create an empty provider profile for providers
        if ($user->role === 'provider') {
            ProviderProfile::create([
                'user_id'          => $user->id,
                'is_approved'      => false,
                'experience_years' => 0,
                'avg_rating'       => 0.00,
                'total_reviews'    => 0,
            ]);
        }

        Auth::login($user);

        return redirect()->intended($this->redirectAfterLogin($user));
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->redirectAfterLogin(Auth::user()));
    }

    // ─── Logout ───────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function redirectAfterLogin(User $user): string
    {
        return match ($user->role) {
            'admin'    => route('admin.dashboard'),
            'provider' => route('provider.dashboard'),
            default    => route('home'),
        };
    }
}
