<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteRequest;
use App\Http\Requests\Admin\UpdateSiteRequest;
use App\Models\Country;
use App\Models\Site;
use App\Models\SiteGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(Request $request): View
    {
        $query = Site::with(['siteGroup', 'latestSyncLog'])
            ->orderBy(match($request->get('sort', 'date')) {
                'name'   => 'name',
                'status' => 'is_active',
                default  => 'created_at',
            }, match($request->get('sort', 'date')) {
                'name' => 'asc',
                default => 'desc',
            });

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->get('group_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        // Counts for pills
        $totalCount    = Site::count();
        $activeCount   = Site::where('is_active', true)->count();
        $inactiveCount = Site::where('is_active', false)->count();

        $sites  = $query->paginate(20)->withQueryString();
        $groups = SiteGroup::withCount('sites')->orderBy('name')->get();
        
        $favoriteIds = auth()->user()
            ->favoriteSites()
            ->pluck('site_id')
            ->toArray();

        return view('admin.sites.index', compact(
            'sites', 'groups', 'totalCount', 'activeCount', 'inactiveCount', 'favoriteIds'
        ));
    }

    public function show(Request $request, Site $site): View
    {
        $tab = $request->get('tab', 'phones');
        $site->load(['siteGroup', 'apiKey', 'phones', 'prices', 'addresses', 'socials', 'customFields']);
        $groups    = SiteGroup::orderBy('name')->get(['id', 'name', 'color']);
        $countries = Country::orderBy('sort_order')->orderBy('iso')->get(['iso', 'dial_code', 'name']);

        $syncLogs = null;
        $logStatus = $request->get('log_status');
        if ($tab === 'logs') {
            $logsQuery = $site->syncLogs();
            if ($logStatus) {
                $logsQuery->where('status', $logStatus);
            }
            $syncLogs = $logsQuery->paginate(20)->withQueryString();
        }

        return view('admin.sites.show', compact('site', 'groups', 'tab', 'countries', 'syncLogs', 'logStatus'));
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        Site::create($data);

        return redirect()->route('sites.index')
            ->with('success', 'Сайт додано');
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $site->update($data);

        return redirect()->route('sites.index')
            ->with('success', 'Сайт оновлено');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Сайт видалено');
    }
}
