<?php

use App\Http\Middleware\ApiKeyAuth;
use App\Http\Middleware\CheckUserActive;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
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

        // Convert empty strings to null so nullable validation rules work correctly
        // with selects that have an empty first option and hidden inputs with no value.
        $middleware->append(ConvertEmptyStringsToNull::class);

        // Check is_active on every request + track online presence
        $middleware->web(append: [CheckUserActive::class]);

        // 'theme' cookie is set client-side via JS — exclude from encryption.
        $middleware->encryptCookies(except: ['theme']);

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
