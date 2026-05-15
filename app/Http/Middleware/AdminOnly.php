<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts access to a route to users with role='admin'.
 *
 * Use as alias 'admin' (registered in bootstrap/app.php) on routes
 * that perform destructive multi-record operations (bulk delete/edit/copy)
 * until a finer-grained Policy layer is in place.
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'admin_required',
                ], 403);
            }
            abort(403, 'Тільки адміністратор має доступ до цієї операції');
        }

        return $next($request);
    }
}
