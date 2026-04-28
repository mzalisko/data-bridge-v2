<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SyncLog;
use App\Models\SystemLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Stat cards
        $totalSites    = Site::count();
        $activeSites   = Site::where('is_active', true)->count();
        $syncedToday   = SyncLog::whereDate('synced_at', today())->distinct('site_id')->count();
        $totalContacts = DB::table('site_phones')->count()
                       + DB::table('site_prices')->count()
                       + DB::table('site_addresses')->count();
        $totalUsers    = User::count();

        // Error count — sites with latest sync error
        $problemSites = Site::with('latestSyncLog')
            ->where('is_active', true)
            ->get()
            ->filter(fn($s) => $s->latestSyncLog?->status === 'error')
            ->values();
        $errorCount = $problemSites->count();

        // Recent activity — last 8 system log entries
        $recentActivity = SystemLog::with('user')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        // Top sites by total contact records
        $topSites = Site::with('latestSyncLog')
            ->leftJoin('site_phones',    'sites.id', '=', 'site_phones.site_id')
            ->leftJoin('site_prices',    'sites.id', '=', 'site_prices.site_id')
            ->leftJoin('site_addresses', 'sites.id', '=', 'site_addresses.site_id')
            ->select('sites.*', DB::raw('COUNT(DISTINCT site_phones.id) + COUNT(DISTINCT site_prices.id) + COUNT(DISTINCT site_addresses.id) AS contact_total'))
            ->groupBy('sites.id')
            ->orderByDesc('contact_total')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalSites',
            'activeSites',
            'syncedToday',
            'totalContacts',
            'totalUsers',
            'errorCount',
            'recentActivity',
            'topSites',
            'problemSites',
        ));
    }
}
