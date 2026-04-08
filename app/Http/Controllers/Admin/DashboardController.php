<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteGroup;
use App\Models\SyncLog;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'sites_total'   => Site::count(),
            'sites_ok'      => Site::where('status', 'ok')->count(),
            'sites_off'     => Site::where('status', 'off')->count(),
            'groups_total'  => SiteGroup::count(),
            'users_total'   => User::where('is_active', true)->count(),
            'syncs_today'   => SyncLog::whereDate('synced_at', today())->count(),
            'syncs_failed'  => SyncLog::whereDate('synced_at', today())
                                    ->where('status', 'error')->count(),
        ];

        $recentLogs = SystemLog::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentLogs'));
    }
}
