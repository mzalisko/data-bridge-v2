<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteRequest;
use App\Http\Requests\Admin\UpdateSiteRequest;
use App\Models\ActivityLog;
use App\Models\Country;
use App\Models\Site;
use App\Models\SiteGroup;
use App\Models\SiteFailoverLog;
use App\Models\SyncLog;
use App\Services\SyncPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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
        $tab = $request->query('tab', 'overview');
        $site->load(['siteGroup', 'apiKey', 'phones', 'prices', 'addresses', 'socials', 'customFields']);
        $groups    = SiteGroup::orderBy('name')->get(['id', 'name', 'color']);
        $countries = Country::orderBy('sort_order')->orderBy('iso')->get(['iso', 'dial_code', 'name']);

        $presenceOthers = $this->getPresence($site->id);

        $activityLogs = ActivityLog::where('site_id', $site->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->paginate(30, ['*'], 'act_page')
            ->withQueryString();

        $siteSyncs = SyncLog::where('site_id', $site->id)
            ->orderByDesc('synced_at')
            ->limit(20)
            ->get();

        $failoverLogs = SiteFailoverLog::where('site_id', $site->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'failover_page');

        $isFavorite = auth()->user()->favoriteSites()->where('site_id', $site->id)->exists();

        return view('admin.sites.show', compact(
            'site', 'groups', 'tab', 'countries', 'presenceOthers',
            'activityLogs', 'siteSyncs', 'failoverLogs', 'isFavorite',
        ));
    }

    public function presence(Request $request, Site $site): JsonResponse
    {
        $key    = "site_presence_{$site->id}";
        $userId = auth()->id();
        $now    = time();

        $list = Cache::get($key, []);
        // drop stale (>90s) and self
        $list = array_values(array_filter($list, fn($p) => $p['id'] !== $userId && ($now - $p['at']) < 90));
        // register self
        $list[] = ['id' => $userId, 'name' => auth()->user()->name, 'at' => $now];
        Cache::put($key, $list, 120);

        $others = array_values(array_filter($list, fn($p) => $p['id'] !== $userId));
        return response()->json(['others' => $others]);
    }

    private function getPresence(int $siteId): array
    {
        $key  = "site_presence_{$siteId}";
        $now  = time();
        $list = Cache::get($key, []);
        return array_values(array_filter($list, fn($p) => $p['id'] !== auth()->id() && ($now - $p['at']) < 90));
    }

    public function restoreActivity(Site $site, ActivityLog $log): RedirectResponse
    {
        if ($log->site_id !== $site->id || $log->action !== 'delete' || ! $log->snapshot) {
            return back()->with('error', 'Відновлення неможливе');
        }

        $modelMap = [
            'phone'   => \App\Models\SitePhone::class,
            'price'   => \App\Models\SitePrice::class,
            'address' => \App\Models\SiteAddress::class,
            'social'  => \App\Models\SiteSocial::class,
            'field'   => \App\Models\SiteCustomField::class,
        ];

        $modelClass = $modelMap[$log->entity_type] ?? null;
        if (! $modelClass) {
            return back()->with('error', 'Невідомий тип запису');
        }

        // snapshot is {before: {...}, after: {...}} for update/delete
        $raw  = $log->snapshot;
        $data = $raw['before'] ?? $raw; // fallback: old format was flat array
        $data = collect($data)->except(['id', 'created_at', 'updated_at'])->all();

        $modelClass::create($data);

        return back()->with('success', 'Запис відновлено');
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

    public function updatePushSettings(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'push_url'          => ['nullable', 'url', 'max:500'],
            'push_key'          => ['nullable', 'string', 'size:64'],
            'allow_plugin_edit' => ['nullable', 'boolean'],
        ]);

        $update = [
            'push_url'          => $data['push_url'] ?: null,
            'push_key'          => $data['push_key'] ?: null,
            'allow_plugin_edit' => (bool) ($data['allow_plugin_edit'] ?? false),
        ];

        if ($update['allow_plugin_edit'] && !$site->plugin_edit_token) {
            $update['plugin_edit_token'] = bin2hex(random_bytes(32));
        }

        $site->update($update);

        return redirect()->back()->with('success', 'Налаштування збережено');
    }

    public function syncPush(Site $site): RedirectResponse
    {
        $ok = SyncPushService::push($site);

        return redirect()->back()->with(
            $ok ? 'success' : 'error',
            $ok ? 'Дані синхронізовано успішно' : 'Помилка синхронізації — перевірте URL та ключ'
        );
    }
}
