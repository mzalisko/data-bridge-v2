<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SyncLog;
use App\Models\SystemLog;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $recentSyncs = SyncLog::with('site')
            ->orderByDesc('synced_at')
            ->paginate(20, ['*'], 'syncs_page');

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
            
        $quickSites = Site::with(['latestSyncLog', 'apiKey'])
            ->whereIn('id', $recentSyncSiteIds)
            ->get()
            ->sortBy(fn($site) => array_search($site->id, $recentSyncSiteIds->toArray()))
            ->values();

        // IDs of favorites for the star toggle
        $favoriteIds = $favoriteSites->pluck('id')->toArray();

        // Stat cards
        $totalSites   = Site::count();
        $activeSites  = Site::where('is_active', true)->count();
        $syncedToday  = SyncLog::whereDate('synced_at', today())->distinct('site_id')->count();
        $errorCount   = $problemSites->count();
        $totalContacts = DB::table('phones')->count()
                       + DB::table('prices')->count()
                       + DB::table('addresses')->count();

        return view('admin.dashboard', compact(
            'recentSyncs',
            'problemSites',
            'quickSites',
            'favoriteSites',
            'favoriteIds',
            'recentLogs',
            'totalSites',
            'activeSites',
            'syncedToday',
            'errorCount',
            'totalContacts',
        ));
    }
}
