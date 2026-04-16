<?php

use App\Http\Middleware\ApiKeyAuth;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'api.key' => ApiKeyAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->booted(function () {
        RateLimiter::for('api', function (Request $request) {
            $key = $request->bearerToken() ?? $request->ip();
            return Limit::perMinute(60)->by($key)->response(function () {
                return response()->json([
                    'status'  => 'error',
                    'code'    => 429,
                    'message' => 'Too many requests',
                ], 429, ['Retry-After' => '60']);
            });
        });
    })
    ->create();
