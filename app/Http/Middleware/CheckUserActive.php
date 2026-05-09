<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CheckUserActive
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('error', 'Ваш акаунт деактивовано. Зверніться до адміністратора.');
            }

            // Track global online presence (one cache write per request)
            $onlineData = Cache::get('crm_online', []);
            $onlineData[Auth::id()] = now()->timestamp;
            Cache::put('crm_online', $onlineData, 300);
        }

        return $next($request);
    }
}
