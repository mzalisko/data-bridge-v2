<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SyncLog;
use App\Models\SystemLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Last sync events (Show all up to 50, else paginate by 20)
        $syncsCount = SyncLog::count();
        $syncsLimit = $syncsCount > 50 ? 20 : 50;
        $recentSyncs = SyncLog::with('site')
            ->orderByDesc('synced_at')
            ->paginate($syncsLimit, ['*'], 'syncs_page');

        // Last system logs — always 8 per page (AJAX pagination in blade)
        $recentLogs = SystemLog::with('user')
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'logs_page');

        // Sites whose latest sync ended in error
        $problemSites = Site::with('latestSyncLog')
            ->where('is_active', true)
            ->get()
            ->filter(fn($s) => $s->latestSyncLog?->status === 'error')
            ->values();

        // User's favorited sites (with sync status)
        $favoriteSites = auth()->user()
            ->favoriteSites()
            ->with('latestSyncLog')
            ->orderBy('name')
            ->get();

        // Recently Synchronized Sites (Last 5 unique sites that had any sync)
        $recentSyncSiteIds = SyncLog::select('site_id')
            ->groupBy('site_id')
            ->orderByRaw('MAX(synced_at) DESC')
            ->limit(5)
            ->pluck('site_id');
            
        $quickSites = Site::with('latestSyncLog')
            ->whereIn('id', $recentSyncSiteIds)
            ->get()
            ->sortBy(fn($site) => array_search($site->id, $recentSyncSiteIds->toArray()))
            ->values(); // Reset keys after sort

        // IDs of favorites for the star toggle
        $favoriteIds = $favoriteSites->pluck('id')->toArray();

        return view('admin.dashboard', compact(
            'recentSyncs',
            'problemSites',
            'quickSites',
            'favoriteSites',
            'favoriteIds',
            'recentLogs',
        ));
    }
}
