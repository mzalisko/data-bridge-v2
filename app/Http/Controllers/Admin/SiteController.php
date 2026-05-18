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
use App\Rules\PublicHttpUrl;
use App\Services\ActivityService;
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
        $site->load(['siteGroup', 'apiKey', 'phones', 'prices', 'addresses', 'socials', 'customFields']);
        $groups    = SiteGroup::orderBy('name')->get(['id', 'name', 'color']);
        $countries = Country::orderBy('sort_order')->orderBy('iso')->get(['iso', 'dial_code', 'name']);

        // View-model prep (audit S3 — hoisted verbatim out of show.blade.php's
        // 119-line header @php so the god-view holds markup, not logic).
        $statusName = $site->is_active ? 'Online' : 'Offline';
        $syncLog    = $site->latestSyncLog;
        $syncWhen   = $syncLog?->synced_at?->diffForHumans() ?? '—';
        $tab        = in_array($request->query('tab'), ['overview', 'data', 'activity', 'settings'])
            ? $request->query('tab') : 'overview';
        $country    = $request->query('country', 'all');

        $allIsoCountries = [
            'AL'=>'Albania','AM'=>'Armenia','AT'=>'Austria','AZ'=>'Azerbaijan',
            'BA'=>'Bosnia and Herzegovina','BE'=>'Belgium','BG'=>'Bulgaria','BY'=>'Belarus',
            'CH'=>'Switzerland','CY'=>'Cyprus','CZ'=>'Czech Republic',
            'DE'=>'Germany','DK'=>'Denmark','EE'=>'Estonia','ES'=>'Spain',
            'FI'=>'Finland','FR'=>'France','GB'=>'United Kingdom','GE'=>'Georgia',
            'GR'=>'Greece','HR'=>'Croatia','HU'=>'Hungary','IE'=>'Ireland',
            'IL'=>'Israel','IT'=>'Italy','KG'=>'Kyrgyzstan','KZ'=>'Kazakhstan',
            'LT'=>'Lithuania','LU'=>'Luxembourg','LV'=>'Latvia',
            'MD'=>'Moldova','ME'=>'Montenegro','MK'=>'North Macedonia','MT'=>'Malta',
            'NL'=>'Netherlands','NO'=>'Norway','PL'=>'Poland','PT'=>'Portugal',
            'RO'=>'Romania','RS'=>'Serbia','RU'=>'Russia',
            'SE'=>'Sweden','SI'=>'Slovenia','SK'=>'Slovakia',
            'TJ'=>'Tajikistan','TM'=>'Turkmenistan','TR'=>'Turkey',
            'UA'=>'Ukraine','UZ'=>'Uzbekistan',
            'AE'=>'UAE','SA'=>'Saudi Arabia','CN'=>'China','IN'=>'India',
            'JP'=>'Japan','KR'=>'South Korea','US'=>'United States',
            'CA'=>'Canada','AU'=>'Australia','BR'=>'Brazil','MX'=>'Mexico',
            'ZA'=>'South Africa','NG'=>'Nigeria','EG'=>'Egypt',
        ];

        $activeGeosRaw = (array) ($site->active_geos ?? []);
        $geoNames = array_is_list($activeGeosRaw)
            ? array_fill_keys($activeGeosRaw, '')
            : $activeGeosRaw;
        $usedIso  = array_keys($geoNames);
        sort($usedIso);

        $countriesByIso = $countries->keyBy('iso');
        $geoRules = (array) ($site->geo_rules ?? []);

        $geoVis = function ($geoMode, $geoCountries, $visitorIso, $itemCountryIso = null) use ($geoRules): bool {
            $mode   = $geoMode ?? 'all';
            $ctries = (array) ($geoCountries ?? []);
            $itemOk = match($mode) {
                'include' => in_array($visitorIso, $ctries),
                'exclude' => !in_array($visitorIso, $ctries),
                default   => true,
            };
            if (!$itemOk) return false;
            if ($itemCountryIso && isset($geoRules[$itemCountryIso])) {
                $rule    = (array) $geoRules[$itemCountryIso];
                $rMode   = $rule['mode'] ?? 'all';
                $rCtries = (array) ($rule['countries'] ?? []);
                $tabOk   = match($rMode) {
                    'include' => in_array($visitorIso, $rCtries),
                    'exclude' => !in_array($visitorIso, $rCtries),
                    default   => true,
                };
                if (!$tabOk) return false;
            }
            return true;
        };

        $visRuleOptions = $usedIso;

        $filterByGeo = function ($collection) use ($country) {
            if ($country === 'all') return $collection;
            return $collection->filter(function ($item) use ($country) {
                $iso = $item->country_iso ?: null;
                return $iso === null || $iso === $country;
            })->values();
        };

        $shownPhones    = $filterByGeo($site->phones);
        $shownPrices    = $filterByGeo($site->prices);
        $shownAddresses = $filterByGeo($site->addresses);
        $shownSocials   = $filterByGeo($site->socials);

        $url = function ($newParams) use ($site, $request) {
            return route('sites.show', $site) . '?' . http_build_query(array_merge($request->query(), $newParams));
        };

        $socialIcon = config('social_icons');

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
            'statusName', 'syncLog', 'syncWhen', 'country', 'allIsoCountries',
            'geoNames', 'usedIso', 'countriesByIso', 'geoRules', 'geoVis',
            'visRuleOptions', 'filterByGeo', 'shownPhones', 'shownPrices',
            'shownAddresses', 'shownSocials', 'url', 'socialIcon',
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

        $modelClass = \App\Support\EntityTypeRegistry::model($log->entity_type);
        if (! $modelClass) {
            return back()->with('error', 'Невідомий тип запису');
        }

        // snapshot is {before: {...}, after: {...}} for update/delete
        $raw  = $log->snapshot;
        $data = $raw['before'] ?? $raw; // fallback: old format was flat array
        // Drop identity + tenancy keys from snapshot; force site_id from URL
        // (defense against snapshots that contain a different site_id).
        $data = collect($data)->except(['id', 'site_id', 'created_at', 'updated_at'])->all();
        $data['site_id'] = $site->id;

        $modelClass::create($data);

        return back()->with('success', 'Запис відновлено');
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $site = Site::create($data);
        ActivityService::log('site', 'create', $site, "Сайт «{$site->name}» додано", $site);

        return redirect()->route('sites.index')
            ->with('success', 'Сайт додано');
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $before = $site->toArray();
        $site->update($data);
        ActivityService::log('site', 'update', $site, "Сайт «{$site->name}» оновлено", $site, $before);

        return redirect()->route('sites.index')
            ->with('success', 'Сайт оновлено');
    }

    public function destroy(Site $site): RedirectResponse
    {
        ActivityService::log('site', 'delete', $site, "Сайт «{$site->name}» видалено", $site);
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Сайт видалено');
    }

    public function updatePushSettings(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'push_url'          => ['nullable', 'url', 'max:500', new PublicHttpUrl()],
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

        $before = $site->toArray();
        $site->update($update);
        ActivityService::log('site', 'update', $site, "Налаштування плагіна оновлено", $site, $before);

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
