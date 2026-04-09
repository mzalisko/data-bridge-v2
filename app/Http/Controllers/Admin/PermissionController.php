<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteGroup;
use App\Models\User;
use App\Models\UserPermission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function show(User $user): View
    {
        $groups = SiteGroup::with('sites')->orderBy('name')->get();

        // Build permission map: "global|view" => true, "group_5|edit" => true, "site_12|delete" => true
        $perms = [];
        foreach ($user->permissions as $p) {
            if ($p->group_id === null && $p->site_id === null) {
                $perms["global|{$p->permission}"] = $p->granted;
            } elseif ($p->site_id !== null) {
                $perms["site_{$p->site_id}|{$p->permission}"] = $p->granted;
            } else {
                $perms["group_{$p->group_id}|{$p->permission}"] = $p->granted;
            }
        }

        return view('admin.users.permissions', compact('user', 'groups', 'perms'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        // Admin always has full access — no need to store permissions
        if ($user->isAdmin()) {
            return redirect()->route('users.index')
                ->with('success', "Admin має повний доступ — збереження не потрібне");
        }

        // Delete all existing permissions for this user
        UserPermission::where('user_id', $user->id)->delete();

        $incoming = $request->input('perms', []);
        $permTypes = ['view', 'edit', 'delete', 'api_key'];
        $toInsert  = [];

        foreach ($incoming as $key => $actions) {
            foreach ($permTypes as $perm) {
                if (empty($actions[$perm])) {
                    continue;
                }

                if ($key === 'global') {
                    $toInsert[] = [
                        'user_id'    => $user->id,
                        'group_id'   => null,
                        'site_id'    => null,
                        'permission' => $perm,
                        'granted'    => true,
                    ];
                } elseif (str_starts_with($key, 'group_')) {
                    $groupId = (int) substr($key, 6);
                    $toInsert[] = [
                        'user_id'    => $user->id,
                        'group_id'   => $groupId,
                        'site_id'    => null,
                        'permission' => $perm,
                        'granted'    => true,
                    ];
                } elseif (str_starts_with($key, 'site_')) {
                    $siteId = (int) substr($key, 5);
                    $toInsert[] = [
                        'user_id'    => $user->id,
                        'group_id'   => null,
                        'site_id'    => $siteId,
                        'permission' => $perm,
                        'granted'    => true,
                    ];
                }
            }
        }

        UserPermission::insert($toInsert);

        return redirect()->route('users.index')
            ->with('success', "Права для «{$user->name}» збережено");
    }
}
