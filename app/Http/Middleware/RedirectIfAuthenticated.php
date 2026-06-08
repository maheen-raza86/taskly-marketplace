<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * Redirect already-authenticated users away from guest-only pages
     * (e.g. login, register) to their role-appropriate dashboard.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();

                // Redirect to the role-appropriate dashboard
                return redirect($this->homeForRole($user->role ?? 'customer'));
            }
        }

        return $next($request);
    }

    /**
     * Return the home URL for a given role.
     */
    private function homeForRole(string $role): string
    {
        return match ($role) {
            'admin'    => route('admin.dashboard'),
            'provider' => route('provider.dashboard'),
            default    => route('home'),
        };
    }
}
