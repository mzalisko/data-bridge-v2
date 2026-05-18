<?php

namespace App\Http\Middleware;

use App\Models\Site;
use App\Models\SiteGroup;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC enforcement for mutating web routes.
 *
 * Use as alias 'perm:{action}' where action is one of the permission
 * strings written by PermissionController: view|edit|delete|api_key.
 *
 * Resolution order:
 *   1. admin   → full access (matches PermissionController: admin needs no grants).
 *   2. manager → broad staff role: view|edit|delete allowed (user/permission
 *      management stays separately 'admin'-gated).
 *   3. editor/viewer → must hold a granted UserPermission for {action},
 *      resolved at site → group → global scope against the route's {site}.
 */
class EnforcePermission
{
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = $request->user();

        if ($user && $user->isAdmin()) {
            return $next($request);
        }

        if ($user && $user->role === 'manager' && in_array($action, ['view', 'edit', 'delete'], true)) {
            return $next($request);
        }

        if ($user && $this->granted($user, $action, $request)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json(['ok' => false, 'error' => 'permission_denied'], 403);
        }

        abort(403, 'Недостатньо прав для цієї дії');
    }

    private function granted($user, string $action, Request $request): bool
    {
        $siteParam  = $request->route('site');
        $groupParam = $request->route('site_group');

        $site = $siteParam instanceof Site
            ? $siteParam
            : ($siteParam ? Site::find($siteParam) : null);

        $group = $groupParam instanceof SiteGroup
            ? $groupParam
            : ($groupParam ? SiteGroup::find($groupParam) : null);

        return $user->permissions()
            ->where('permission', $action)
            ->where('granted', true)
            ->where(function ($scope) use ($site, $group) {
                // Global grant (no group, no site).
                $scope->where(function ($g) {
                    $g->whereNull('group_id')->whereNull('site_id');
                });

                if ($site) {
                    $scope->orWhere('site_id', $site->id);
                    if ($site->group_id) {
                        $scope->orWhere('group_id', $site->group_id);
                    }
                }

                if ($group) {
                    $scope->orWhere('group_id', $group->id);
                }
            })
            ->exists();
    }
}
