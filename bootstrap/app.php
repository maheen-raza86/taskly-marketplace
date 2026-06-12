<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders()
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies — required for Render.com (and any reverse-proxy host)
        // so that asset(), url(), and redirect() use the correct https:// scheme.
        $middleware->trustProxies(at: '*');

        // Register the CheckRole middleware alias
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
