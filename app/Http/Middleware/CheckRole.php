<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: middleware('role:admin')
     *                  middleware('role:provider')
     *                  middleware('role:customer')
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (Auth::user()->role !== $role) {
            abort(403, 'Unauthorized. You do not have the required role to access this page.');
        }

        return $next($request);
    }
}
