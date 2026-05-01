@extends('layouts.app')

@section('title', $site->name)

@section('content')
@php
    $statusName = $site->is_active ? 'Online' : 'Offline';
    $syncLog    = $site->latestSyncLog;
    $syncWhen   = $syncLog?->synced_at?->diffForHumans() ?? '—';
    // Sub-tab: overview | data | activity | settings  (default: overview)
    $tab        = in_array(request('tab'), ['overview','data','activity','settings']) ? request('tab') : 'overview';
    // Geo top-tab: all | ISO
    $country    = request('country', 'all');

    // Countries used in actual data + countries explicitly added via Add geo
    $dataIso = collect()
        ->merge($site->phones->pluck('country_iso'))
        ->merge($site->addresses->pluck('country_iso'))
        ->filter()->unique()->values()->toArray();
    $activeGeos = (array) ($site->active_geos ?? []);
    $usedIso = array_values(array_unique(array_merge($dataIso, $activeGeos)));
    sort($usedIso);

    $countriesByIso = $countries->keyBy('iso');

    // Geo rules: visitor-country → list of allowed data-country isos.
    // Default rule (when no entry): show records whose country_iso === visitor.
    $geoRules = (array) ($site->geo_rules ?? []);
    $allowedFor = function ($visitor) use ($geoRules) {
        return array_key_exists($visitor, $geoRules)
            ? (array) $geoRules[$visitor]
            : [$visitor];
    };

    // Filter records by selected country tab (uses geo_rules; falls back to country_iso match).
    // Records WITHOUT country_iso (e.g., prices/socials currently) are treated as "global" and always shown.
    $filterByGeo = function ($collection) use ($country, $allowedFor) {
        if ($country === 'all') return $collection;
        $allowed = $allowedFor($country);
        return $collection->filter(function ($item) use ($allowed) {
            $iso = $item->country_iso ?? null;
            return $iso === null || in_array($iso, $allowed, true);
        })->values();
    };

    $shownPhones    = $filterByGeo($site->phones);
    $shownPrices    = $filterByGeo($site->prices);
    $shownAddresses = $filterByGeo($site->addresses);
    $shownSocials   = $filterByGeo($site->socials);

    $url = function($newParams) use ($site) {
        return route('sites.show', $site) . '?' . http_build_query(array_merge(request()->query(), $newParams));
    };

    // Social platform → icon SVG + brand color
    $socialIcon = [
        'instagram' => ['c' => '#c2185b', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg>'],
        'facebook'  => ['c' => '#1877f2', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.6-1.5h1.5V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.2H8v3h2.5V21h3z"/></svg>'],
        'telegram'  => ['c' => '#229ed9', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M21 4 2.5 11.5c-.7.3-.7 1.3 0 1.5l4.5 1.4 1.7 5.4c.2.6 1 .8 1.4.3l2.5-2.7 4.7 3.4c.5.4 1.3.1 1.5-.5L22 5c.2-.7-.5-1.3-1-1zM9.7 14.7l-.4 4 1.7-2.4 4.6-5.5-5.9 3.9z"/></svg>'],
        'linkedin'  => ['c' => '#0a66c2', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M5 4.5A1.7 1.7 0 1 1 5 8a1.7 1.7 0 0 1 0-3.5zM3.5 9.5h3v11h-3v-11zM9 9.5h2.9v1.6c.4-.8 1.5-1.8 3.2-1.8 3.4 0 4 2.2 4 5.1v6.1h-3v-5.4c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.5H9v-11z"/></svg>'],
        'x'         => ['c' => 'var(--text)', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M17.5 3h3l-6.6 7.6L21.5 21h-6l-4.4-5.8L6 21H3l7-8.1L2.5 3h6.1l4 5.4L17.5 3z"/></svg>'],
        'twitter'   => ['c' => 'var(--text)', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M17.5 3h3l-6.6 7.6L21.5 21h-6l-4.4-5.8L6 21H3l7-8.1L2.5 3h6.1l4 5.4L17.5 3z"/></svg>'],
        'whatsapp'  => ['c' => '#25d366', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3.5 20.5 4.8 16A8 8 0 1 1 8 19.4l-4.5 1.1z"/></svg>'],
        'viber'     => ['c' => '#7360f2', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 4h11a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3h-2l-3 3v-3H7a2 2 0 0 1-2-2V4z"/></svg>'],
        'youtube'   => ['c' => '#ff0000', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8C22 15.2 22 12 22 12s0-3.2-.4-4.8zM10 15V9l5 3-5 3z"/></svg>'],
    ];
@endphp

<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <div class="page-head__crumb">
                <a href="{{ route('sites.index') }}">Sites</a> / <span style="color:var(--text);">{{ $site->name }}</span>
            </div>
            <h1 class="page-head__title">
                <x-favicon :name="$site->name" :size="28"/>
                {{ $site->name }}
            </h1>
            <p class="page-head__subtitle" style="font-family:var(--font-mono);">{{ $site->url }}</p>
        </div>
        <div class="page-head__actions">
            @if($site->url)
                <a href="{{ $site->url }}" target="_blank" class="btn btn--secondary btn--md">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 4h6v6"/><path d="M20 4 10 14"/>
                        <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/>
                    </svg>
                    Open
                </a>
            @endif
            <button class="btn btn--secondary btn--md">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 0 1 15.5-6.3L21 8"/><path d="M21 4v4h-4"/>
                    <path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"/><path d="M3 20v-4h4"/>
                </svg>
                Resync
            </button>
            <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-site-edit')">Push update</button>
        </div>
    </div>

    {{-- ========= 5 MINI STATS ========= --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;">
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Status</div>
            <div><x-status-pill :status="$statusName"/></div>
        </div>
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Group</div>
            <div style="font-size:14px;font-weight:600;">
                @if($site->siteGroup)
                    <span class="group-chip">
                        <span class="group-chip__dot" style="background:{{ $site->siteGroup->color ?? '#71717a' }}"></span>
                        {{ $site->siteGroup->name }}
                    </span>
                @else
                    <span style="color:var(--text-3);">—</span>
                @endif
            </div>
        </div>
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Geos</div>
            <div style="font-size:18px;font-weight:600;color:var(--text);">{{ count($usedIso) }}</div>
        </div>
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Phones</div>
            <div style="font-size:18px;font-weight:600;">{{ $site->phones->count() }}</div>
        </div>
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Last sync</div>
            <div style="font-size:13px;font-weight:600;color:var(--text-2);">{{ $syncWhen }}</div>
        </div>
    </div>

    {{-- ========= MAIN TAB CARD ========= --}}
    <div class="card card--flush">

        {{-- ========= TABS — OVERVIEW/DATA/ACTIVITY/SETTINGS ========= --}}
        <div class="tabs">
            <a href="{{ $url(['tab' => 'overview']) }}" class="tabs__item {{ $tab === 'overview' ? 'is-active' : '' }}">Overview</a>
            <a href="{{ $url(['tab' => 'data']) }}"     class="tabs__item {{ $tab === 'data'     ? 'is-active' : '' }}">Data</a>
            <a href="{{ $url(['tab' => 'activity']) }}" class="tabs__item {{ $tab === 'activity' ? 'is-active' : '' }}">Activity</a>
            <a href="{{ $url(['tab' => 'settings']) }}" class="tabs__item {{ $tab === 'settings' ? 'is-active' : '' }}">Settings</a>
        </div>

        {{-- ========= OVERVIEW ========= --}}
        @if($tab === 'overview')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--border-2);">
                <div style="background:var(--panel);padding:20px;">
                    <h4 style="margin:0 0 12px;font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Site info</h4>
                    <div class="kv"><span class="kv__k">Domain</span><span class="kv__v mono">{{ $site->url }}</span></div>
                    <div class="kv"><span class="kv__k">Group</span><span class="kv__v">{{ $site->siteGroup?->name ?? '—' }}</span></div>
                    <div class="kv"><span class="kv__k">Status</span><span class="kv__v">{{ $statusName }}</span></div>
                    <div class="kv"><span class="kv__k">Added</span><span class="kv__v">{{ $site->created_at->format('d M Y') }}</span></div>
                </div>
                <div style="background:var(--panel);padding:20px;">
                    <h4 style="margin:0 0 12px;font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Sync health</h4>
                    <div class="kv"><span class="kv__k">Last sync</span><span class="kv__v">{{ $syncWhen }}</span></div>
                    <div class="kv"><span class="kv__k">Sync status</span><span class="kv__v">
                        @if($syncLog?->status === 'success')
                            <span class="pill pill--success"><span class="dot dot--success"></span>OK</span>
                        @elseif($syncLog?->status === 'error')
                            <span class="pill pill--danger"><span class="dot dot--danger"></span>Error</span>
                        @else
                            <span class="pill pill--neutral">No data</span>
                        @endif
                    </span></div>
                    <div class="kv"><span class="kv__k">Webhook</span><span class="kv__v">
                        @if($site->plugin_webhook_url)
                            <span class="pill pill--success"><span class="dot dot--success"></span>Active</span>
                        @else
                            <span class="pill pill--neutral">Not configured</span>
                        @endif
                    </span></div>
                </div>
            </div>

            {{-- ===== GEO COVERAGE — large country pills with totals ===== --}}
            @if(count($usedIso) > 0)
                <div style="border-top:1px solid var(--border-2);padding:20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
                        <h4 style="margin:0;font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Geo coverage</h4>
                        <span style="font-size:12px;color:var(--text-3);">{{ count($usedIso) }} {{ count($usedIso) === 1 ? 'country' : 'countries' }}</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
                        @foreach($usedIso as $iso)
                            @php
                                $cName  = $countriesByIso[$iso]->name ?? $iso;
                                $totalG = $site->phones->where('country_iso', $iso)->count()
                                        + $site->prices->where('country_iso', $iso)->count()
                                        + $site->addresses->where('country_iso', $iso)->count()
                                        + $site->socials->where('country_iso', $iso)->count();
                            @endphp
                            @php $showCName = $cName && strcasecmp($cName, $iso) !== 0; @endphp
                            <a href="{{ $url(['country' => $iso, 'tab' => 'data']) }}"
                               style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--panel);border:1px solid var(--border);border-radius:var(--radius);text-decoration:none;color:inherit;transition:border-color .12s, box-shadow .12s;"
                               onmouseover="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px var(--accent-2)';"
                               onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='none';">
                                <span style="width:34px;height:34px;border-radius:8px;background:var(--accent-2);color:var(--accent-text);font-family:var(--font-mono);font-weight:700;font-size:13px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;">{{ $iso }}</span>
                                <div style="min-width:0;flex:1;">
                                    <div style="font-size:13px;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $showCName ? $cName : 'Country '.$iso }}</div>
                                    <div style="font-size:11px;color:var(--text-3);font-family:var(--font-mono);">{{ $totalG }} {{ $totalG === 1 ? 'item' : 'items' }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div style="border-top:1px solid var(--border-2);padding:20px;display:flex;flex-direction:column;gap:16px;">
                    <h4 style="margin:0;font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Data by geo</h4>

                    @foreach($usedIso as $iso)
                        @php
                            $cName     = $countriesByIso[$iso]->name ?? $iso;
                            $geoPhones = $site->phones->where('country_iso', $iso);
                            $geoPrices = $site->prices->where('country_iso', $iso);
                            $geoAddrs  = $site->addresses->where('country_iso', $iso);
                            $geoSocial = $site->socials->where('country_iso', $iso);
                        @endphp
                        <div style="border:1px solid var(--border);border-radius:var(--radius-lg);overflow:hidden;background:var(--panel);">
                            @php $showCName2 = $cName && strcasecmp($cName, $iso) !== 0; @endphp
                            {{-- Geo card header --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 16px;background:var(--panel-2);border-bottom:1px solid var(--border-2);">
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span style="font-family:var(--font-mono);font-weight:700;font-size:12px;background:var(--accent-2);color:var(--accent-text);padding:3px 8px;border-radius:6px;">{{ $iso }}</span>
                                    @if($showCName2)<span style="font-size:13px;font-weight:600;color:var(--text);">{{ $cName }}</span>@endif
                                </div>
                                <div style="display:flex;gap:6px;align-items:center;">
                                    <span class="pill pill--neutral">{{ $geoPhones->count() }} phones</span>
                                    <span class="pill pill--neutral">{{ $geoPrices->count() }} prices</span>
                                    <span class="pill pill--neutral">{{ $geoAddrs->count() }} addr</span>
                                    <span class="pill pill--neutral">{{ $geoSocial->count() }} socials</span>
                                    <a href="{{ $url(['country' => $iso, 'tab' => 'data']) }}" class="btn btn--ghost btn--sm">Manage →</a>
                                </div>
                            </div>

                            {{-- Geo card body — 4 columns: phones / prices / addresses / socials --}}
                            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--border-2);">

                                {{-- Phones col --}}
                                <div style="background:var(--panel);padding:14px 16px;min-height:80px;">
                                    <div style="font-size:10.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:8px;">Phones</div>
                                    @forelse($geoPhones as $p)
                                        <div style="font-family:var(--font-mono);font-size:12px;color:var(--text-2);padding:3px 0;display:flex;align-items:center;gap:6px;">
                                            <span>+{{ $p->dial_code }} {{ $p->number }}</span>
                                            @if($p->is_primary)<span class="pill pill--accent" style="font-size:9px;padding:1px 5px;">P</span>@endif
                                        </div>
                                    @empty
                                        <div style="font-size:11px;color:var(--text-3);">—</div>
                                    @endforelse
                                </div>

                                {{-- Prices col --}}
                                <div style="background:var(--panel);padding:14px 16px;min-height:80px;">
                                    <div style="font-size:10.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:8px;">Prices</div>
                                    @forelse($geoPrices as $p)
                                        <div style="font-size:12px;color:var(--text-2);padding:3px 0;">
                                            @if($p->label)<span>{{ $p->label }} — </span>@endif
                                            <span style="font-family:var(--font-mono);">{{ $p->amount }} {{ $p->currency }}</span>
                                        </div>
                                    @empty
                                        <div style="font-size:11px;color:var(--text-3);">—</div>
                                    @endforelse
                                </div>

                                {{-- Addresses col --}}
                                <div style="background:var(--panel);padding:14px 16px;min-height:80px;">
                                    <div style="font-size:10.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:8px;">Addresses</div>
                                    @forelse($geoAddrs as $a)
                                        <div style="font-size:12px;color:var(--text-2);padding:3px 0;">
                                            {{ trim(($a->city ?? '').' '.($a->street ?? '').' '.($a->building ?? '')) ?: '—' }}
                                        </div>
                                    @empty
                                        <div style="font-size:11px;color:var(--text-3);">—</div>
                                    @endforelse
                                </div>

                                {{-- Socials col --}}
                                <div style="background:var(--panel);padding:14px 16px;min-height:80px;">
                                    <div style="font-size:10.5px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:8px;">Socials</div>
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                        @forelse($geoSocial as $s)
                                            @php
                                                $key = strtolower($s->platform ?? '');
                                                $ic  = $socialIcon[$key] ?? ['c' => 'var(--text-3)', 'svg' => '<svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/></svg>'];
                                            @endphp
                                            <span style="display:inline-flex;align-items:center;gap:4px;padding:3px 8px;background:var(--panel-2);border:1px solid var(--border);border-radius:99px;font-size:11px;color:var(--text-2);">
                                                <span style="color:{{ $ic['c'] }};display:inline-flex;">{!! $ic['svg'] !!}</span>
                                                {{ $s->handle ?: $s->platform }}
                                            </span>
                                        @empty
                                            <div style="font-size:11px;color:var(--text-3);">—</div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="border-top:1px solid var(--border-2);padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">
                    No geo data yet. <a href="{{ $url(['tab' => 'data']) }}" style="color:var(--accent);">Add data →</a>
                </div>
            @endif
        @endif

        {{-- ========= DATA ========= --}}
        @if($tab === 'data')

            {{-- Geo selector bar (only inside Data tab) --}}
            <div style="display:flex;align-items:center;gap:2px;padding:10px 16px;border-bottom:1px solid var(--border-2);background:var(--panel-2);overflow-x:auto;">
                <a href="{{ $url(['country' => 'all']) }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:var(--radius);font-size:12px;text-decoration:none;white-space:nowrap;
                          background:{{ $country === 'all' ? 'var(--panel)' : 'transparent' }};
                          border:1px solid {{ $country === 'all' ? 'var(--border)' : 'transparent' }};
                          color:{{ $country === 'all' ? 'var(--text)' : 'var(--text-3)' }};
                          font-weight:{{ $country === 'all' ? '600' : '500' }};">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a13 13 0 0 1 0 18M12 3a13 13 0 0 0 0 18"/></svg>
                    All geos
                </a>
                @foreach($usedIso as $iso)
                    @php $cName = $countriesByIso[$iso]->name ?? $iso; @endphp
                    <a href="{{ $url(['country' => $iso]) }}" title="{{ $cName }}"
                       style="display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:var(--radius);font-size:12px;text-decoration:none;white-space:nowrap;font-family:var(--font-mono);
                              background:{{ $country === $iso ? 'var(--panel)' : 'transparent' }};
                              border:1px solid {{ $country === $iso ? 'var(--border)' : 'transparent' }};
                              color:{{ $country === $iso ? 'var(--text)' : 'var(--text-3)' }};
                              font-weight:{{ $country === $iso ? '700' : '600' }};">
                        {{ $iso }}
                    </a>
                @endforeach
                <div style="flex:1"></div>

                @if($country !== 'all')
                    <form method="POST" action="{{ route('sites.geos.remove', [$site, $country]) }}" style="margin:0;" onsubmit="return confirm('Remove geo {{ $country }}? Tagged data records remain but the tab disappears.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn--ghost btn--sm" style="white-space:nowrap;color:var(--danger);">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                            Remove {{ $country }}
                        </button>
                    </form>
                @endif

                <button class="btn btn--ghost btn--sm" type="button" style="white-space:nowrap;" onclick="openDrawer('drawer-geo-add')">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add geo
                </button>
            </div>

            <div style="padding:20px;display:flex;flex-direction:column;gap:24px;">

                {{-- Header --}}
                @php
                    $headerName = $countriesByIso[$country]->name ?? null;
                    $showName   = $headerName && strcasecmp($headerName, $country) !== 0;
                @endphp
                <h3 style="margin:0;font-size:15px;font-weight:600;color:var(--text);display:inline-flex;align-items:center;gap:8px;">
                    @if($country === 'all')
                        All geos
                    @else
                        <span style="font-family:var(--font-mono);background:var(--accent-2);color:var(--accent-text);padding:2px 8px;border-radius:6px;font-size:13px;">{{ $country }}</span>
                        @if($showName)<span>{{ $headerName }}</span>@endif
                    @endif
                </h3>

                {{-- ===== PHONES ===== --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <span style="font-size:11px;color:var(--text-3);font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Phones</span>
                    @forelse($shownPhones as $p)
                        <div style="display:flex;gap:8px;align-items:center;">
                            <div class="input input--mono" style="flex:1;cursor:pointer;" onclick="openDrawer('drawer-phone-{{ $p->id }}')">
                                <input type="text" value="+{{ $p->dial_code }} {{ $p->number }}" readonly style="cursor:pointer;">
                            </div>
                            @if($p->is_primary)<span class="pill pill--accent">Primary</span>@endif
                            <button class="icon-btn" type="button" title="Edit" onclick="openDrawer('drawer-phone-{{ $p->id }}')">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4l11-11-4-4L4 16v4z"/><path d="m13.5 6.5 4 4"/></svg>
                            </button>
                            <form method="POST" action="{{ route('sites.visibility.toggle', [$site, 'phones', $p->id]) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="icon-btn" title="{{ ($p->is_visible ?? true) ? 'Hide on site' : 'Show on site' }}" style="color:{{ ($p->is_visible ?? true) ? 'var(--text-3)' : 'var(--warning)' }};">
                                    @if($p->is_visible ?? true)
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('phones.destroy', [$site, $p]) }}" style="margin:0;" onsubmit="return confirm('Delete this phone?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn" title="Delete" style="color:var(--danger);">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div style="color:var(--text-3);font-size:12px;">No phones for this geo.</div>
                    @endforelse
                    <button type="button" class="btn btn--ghost btn--sm" style="border:1px dashed var(--border);color:var(--text-3);align-self:flex-start;" onclick="openDrawer('drawer-phone-create')">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Add phone
                    </button>
                </div>

                {{-- ===== PRICES ===== --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <span style="font-size:11px;color:var(--text-3);font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Prices</span>
                    @forelse($shownPrices as $p)
                        <div style="display:flex;gap:8px;align-items:center;">
                            <div class="input" style="flex:1;cursor:pointer;" onclick="openDrawer('drawer-price-{{ $p->id }}')">
                                <input type="text" value="{{ $p->label ?? '' }}" placeholder="Label" readonly style="cursor:pointer;">
                            </div>
                            <div class="input input--mono" style="width:140px;cursor:pointer;" onclick="openDrawer('drawer-price-{{ $p->id }}')">
                                <input type="text" value="{{ $p->amount ?? '' }} {{ $p->currency ?? '' }}" readonly style="cursor:pointer;">
                            </div>
                            <button class="icon-btn" type="button" title="Edit" onclick="openDrawer('drawer-price-{{ $p->id }}')">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4l11-11-4-4L4 16v4z"/><path d="m13.5 6.5 4 4"/></svg>
                            </button>
                            <form method="POST" action="{{ route('sites.visibility.toggle', [$site, 'prices', $p->id]) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="icon-btn" title="{{ ($p->is_visible ?? true) ? 'Hide' : 'Show' }}" style="color:{{ ($p->is_visible ?? true) ? 'var(--text-3)' : 'var(--warning)' }};">
                                    @if($p->is_visible ?? true)
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('prices.destroy', [$site, $p]) }}" style="margin:0;" onsubmit="return confirm('Delete this price?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn" title="Delete" style="color:var(--danger);">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div style="color:var(--text-3);font-size:12px;">No prices for this geo.</div>
                    @endforelse
                    <button type="button" class="btn btn--ghost btn--sm" style="border:1px dashed var(--border);color:var(--text-3);align-self:flex-start;" onclick="openDrawer('drawer-price-create')">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Add price
                    </button>
                </div>

                {{-- ===== ADDRESSES ===== --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <span style="font-size:11px;color:var(--text-3);font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Addresses</span>
                    @forelse($shownAddresses as $a)
                        <div style="display:flex;gap:8px;align-items:center;">
                            <div class="input" style="flex:1;cursor:pointer;" onclick="openDrawer('drawer-addr-{{ $a->id }}')">
                                <input type="text" value="{{ trim(($a->city ?? '').' '.($a->street ?? '').' '.($a->building ?? '')) }}" placeholder="Address" readonly style="cursor:pointer;">
                            </div>
                            <button class="icon-btn" type="button" title="Edit" onclick="openDrawer('drawer-addr-{{ $a->id }}')">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4l11-11-4-4L4 16v4z"/><path d="m13.5 6.5 4 4"/></svg>
                            </button>
                            <form method="POST" action="{{ route('sites.visibility.toggle', [$site, 'addresses', $a->id]) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="icon-btn" title="{{ ($a->is_visible ?? true) ? 'Hide' : 'Show' }}" style="color:{{ ($a->is_visible ?? true) ? 'var(--text-3)' : 'var(--warning)' }};">
                                    @if($a->is_visible ?? true)
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('addresses.destroy', [$site, $a]) }}" style="margin:0;" onsubmit="return confirm('Delete this address?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn" title="Delete" style="color:var(--danger);">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div style="color:var(--text-3);font-size:12px;">No addresses for this geo.</div>
                    @endforelse
                    <button type="button" class="btn btn--ghost btn--sm" style="border:1px dashed var(--border);color:var(--text-3);align-self:flex-start;" onclick="openDrawer('drawer-addr-create')">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Add address
                    </button>
                </div>

                {{-- ===== SOCIAL MEDIA ===== --}}
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <span style="font-size:11px;color:var(--text-3);font-weight:500;text-transform:uppercase;letter-spacing:.05em;">Social media</span>
                    @forelse($shownSocials as $s)
                        @php
                            $key = strtolower($s->platform ?? '');
                            $ic  = $socialIcon[$key] ?? ['c' => 'var(--text-3)', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/></svg>'];
                        @endphp
                        <div style="display:flex;gap:8px;align-items:center;">
                            <div class="input" style="flex:1;cursor:pointer;" onclick="openDrawer('drawer-soc-{{ $s->id }}')">
                                <span style="width:22px;height:22px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;background:var(--panel-2);color:{{ $ic['c'] }};border:1px solid var(--border);flex-shrink:0;">
                                    {!! $ic['svg'] !!}
                                </span>
                                <input type="text" value="{{ $s->handle ?? $s->url ?? '' }}" readonly style="cursor:pointer;">
                            </div>
                            <button class="icon-btn" type="button" title="Edit" onclick="openDrawer('drawer-soc-{{ $s->id }}')">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4l11-11-4-4L4 16v4z"/><path d="m13.5 6.5 4 4"/></svg>
                            </button>
                            <form method="POST" action="{{ route('sites.visibility.toggle', [$site, 'socials', $s->id]) }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="icon-btn" title="{{ ($s->is_visible ?? true) ? 'Hide' : 'Show' }}" style="color:{{ ($s->is_visible ?? true) ? 'var(--text-3)' : 'var(--warning)' }};">
                                    @if($s->is_visible ?? true)
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    @else
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                    @endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('socials.destroy', [$site, $s]) }}" style="margin:0;" onsubmit="return confirm('Delete this social link?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="icon-btn" title="Delete" style="color:var(--danger);">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                </button>
                            </form>
                        </div>
                    @empty
                        <div style="color:var(--text-3);font-size:12px;">No social links for this geo.</div>
                    @endforelse
                    <button type="button" class="btn btn--ghost btn--sm" style="border:1px dashed var(--border);color:var(--text-3);align-self:flex-start;" onclick="openDrawer('drawer-soc-create')">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Add social link
                    </button>
                </div>

                {{-- Footer --}}
                <div style="display:flex;justify-content:flex-start;align-items:center;padding-top:14px;border-top:1px solid var(--border-2);">
                    <span style="font-size:12px;color:var(--text-3);">Last updated {{ $site->updated_at?->diffForHumans() ?? '—' }} · changes auto-push to site</span>
                    <div style="display:none;">
                        <button class="btn btn--ghost btn--md" type="button">Cancel</button>
                        <button class="btn btn--primary btn--md" type="button">Save & push</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ========= ACTIVITY ========= --}}
        @if($tab === 'activity')
            @php
                $siteSyncs = \App\Models\SyncLog::where('site_id', $site->id)
                    ->orderByDesc('synced_at')->take(20)->get();
            @endphp
            @forelse($siteSyncs as $sync)
                @php
                    $kind = $sync->status === 'success' ? 'success' : ($sync->status === 'error' ? 'danger' : 'warning');
                @endphp
                <div class="activity-row">
                    <span class="activity-row__when">{{ $sync->synced_at?->diffForHumans() ?? '—' }}</span>
                    <div class="activity-row__body">
                        <span class="dot dot--{{ $kind }}"></span>
                        <span class="activity-row__who-system">system</span>
                        <span class="activity-row__action">
                            {{ $sync->status === 'success' ? 'synced successfully' : ($sync->status === 'error' ? 'sync failed' : 'sync pending') }}
                        </span>
                        @if($sync->duration_ms)
                            <span style="color:var(--text-3);font-size:12px;">· {{ $sync->duration_ms }}ms</span>
                        @endif
                        @if($sync->error_msg)
                            <span style="color:var(--text-3);font-size:12px;">· {{ \Illuminate\Support\Str::limit($sync->error_msg, 60) }}</span>
                        @endif
                    </div>
                    <span class="activity-row__kind">{{ $sync->status }}</span>
                </div>
            @empty
                <div style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">No activity yet for this site</div>
            @endforelse
        @endif

        {{-- ========= SETTINGS ========= --}}
        @if($tab === 'settings')

            {{-- ===== Geo rules ===== --}}
            <div style="padding:20px;border-bottom:1px solid var(--border-2);">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <h4 style="margin:0;font-size:13px;font-weight:600;color:var(--text);">Geo visibility rules</h4>
                    <span style="font-size:11px;color:var(--text-3);">{{ count($usedIso) }} {{ count($usedIso) === 1 ? 'geo' : 'geos' }} active</span>
                </div>
                <p style="font-size:12px;color:var(--text-3);margin:0 0 14px;">
                    For each visitor country, choose which data is shown on the site. Empty row = show only data tagged for that country (default).
                </p>

                @if(count($usedIso) === 0)
                    <div style="padding:16px;background:var(--panel-2);border-radius:var(--radius);font-size:12px;color:var(--text-3);">
                        No active geos. Open the Data tab and click «Add geo» first.
                    </div>
                @else
                    <form method="POST" action="{{ route('sites.geo-rules.save', $site) }}" id="form-geo-rules">
                        @csrf
                        <div style="overflow:auto;border:1px solid var(--border);border-radius:var(--radius);">
                            <table class="crm-table" style="font-size:12px;">
                                <thead>
                                    <tr>
                                        <th style="width:140px;">Visitor from</th>
                                        @foreach($usedIso as $col)
                                            <th style="text-align:center;">
                                                <span style="font-family:var(--font-mono);font-weight:700;background:var(--accent-2);color:var(--accent-text);padding:2px 6px;border-radius:4px;">{{ $col }}</span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($usedIso as $row)
                                        @php
                                            $current = (array) ($geoRules[$row] ?? [$row]);
                                        @endphp
                                        <tr style="cursor:default;">
                                            <td>
                                                <span style="display:inline-flex;align-items:center;gap:6px;">
                                                    <span style="font-family:var(--font-mono);font-weight:700;font-size:11px;background:var(--panel-2);padding:2px 6px;border-radius:4px;color:var(--text-2);">{{ $row }}</span>
                                                    <span style="font-size:11px;color:var(--text-3);">{{ $countriesByIso[$row]->name ?? '' }}</span>
                                                </span>
                                            </td>
                                            @foreach($usedIso as $col)
                                                @php $checked = in_array($col, $current, true); @endphp
                                                <td style="text-align:center;">
                                                    <label style="display:inline-flex;align-items:center;justify-content:center;cursor:pointer;width:24px;height:24px;border-radius:6px;{{ $row === $col ? 'background:var(--accent-2);' : '' }}">
                                                        <input type="checkbox" name="rules[{{ $row }}][]" value="{{ $col }}"
                                                               {{ $checked ? 'checked' : '' }}
                                                               style="accent-color:var(--accent);width:14px;height:14px;cursor:pointer;">
                                                    </label>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;">
                            <span style="font-size:11px;color:var(--text-3);">
                                Diagonal cells (highlighted) — visitor sees their own country data.
                                Example: «UA → UA, BY» means visitors from Ukraine see records tagged UA and BY.
                            </span>
                            <button type="submit" class="btn btn--primary btn--sm">Save rules</button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- ===== Sync settings ===== --}}
            <div style="padding:20px;display:flex;flex-direction:column;gap:0;">
                {{-- Auto-sync --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text);">Auto-sync</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Pull updates from this site automatically</div>
                    </div>
                    <button class="toggle is-on" type="button" onclick="this.classList.toggle('is-on')"></button>
                </div>
                {{-- Sync frequency --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;border-top:1px solid var(--border-2);">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text);">Sync frequency</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">How often to pull updates</div>
                    </div>
                    <div class="select-wrap">
                        <select>
                            <option>Every 5 min</option>
                            <option>Every 15 min</option>
                            <option>Hourly</option>
                            <option>Manual only</option>
                        </select>
                        <span class="select-wrap__chevron"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg></span>
                    </div>
                </div>
                {{-- Allow plugin to push --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;border-top:1px solid var(--border-2);">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text);">Allow plugin to push</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Let the WP plugin write back changes</div>
                    </div>
                    <button class="toggle" type="button" onclick="this.classList.toggle('is-on')"></button>
                </div>
                {{-- Notify on errors --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;border-top:1px solid var(--border-2);">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text);">Notify on errors</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Email the team if sync fails</div>
                    </div>
                    <button class="toggle is-on" type="button" onclick="this.classList.toggle('is-on')"></button>
                </div>
                {{-- Footer actions --}}
                <div style="border-top:1px solid var(--border-2);margin-top:6px;padding-top:14px;display:flex;justify-content:space-between;">
                    <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Delete site «{{ $site->name }}»?')" style="margin:0;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn--danger btn--md">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                            Remove site
                        </button>
                    </form>
                    <form method="POST" action="{{ route('sites.api-key.generate', $site) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn--secondary btn--md">Rotate API key</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Toggle styles inline (small component) --}}
<style>
    .toggle {
        width: 34px; height: 20px; border-radius: 99px; padding: 2px; border: 0; cursor: pointer;
        background: var(--border); transition: background .15s; display: inline-flex;
        flex-shrink: 0;
    }
    .toggle::after {
        content: ""; width: 16px; height: 16px; border-radius: 99px; background: #fff;
        transition: transform .15s; box-shadow: 0 1px 2px rgba(0,0,0,.2);
    }
    .toggle.is-on { background: var(--accent); }
    .toggle.is-on::after { transform: translateX(14px); }
</style>

{{-- ========= EDIT DRAWER ========= --}}
<div class="drawer-overlay" id="drawer-site-edit-overlay" onclick="closeDrawer('drawer-site-edit')"></div>
<div class="drawer" id="drawer-site-edit">
    <div class="drawer__header">
        <span class="drawer__title">{{ $site->name }}</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-site-edit')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.update', $site) }}" class="form-stack" id="form-site-edit">
            @csrf @method('PUT')
            @include('admin.sites._form', ['site' => $site, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-site-edit')">Cancel</button>
        <button type="submit" form="form-site-edit" class="btn btn--primary btn--md">Save</button>
    </div>
</div>

{{-- ===================== DATA CRUD DRAWERS (only on Data tab) ===================== --}}
@if($tab === 'data')

    {{-- ========= PHONE: create ========= --}}
    <div class="drawer-overlay" id="drawer-phone-create-overlay" onclick="closeDrawer('drawer-phone-create')"></div>
    <div class="drawer" id="drawer-phone-create">
        <div class="drawer__header">
            <span class="drawer__title">Add phone</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-phone-create')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('phones.store', $site) }}" id="form-phone-create">
                @csrf
                @include('admin.sites._form-phone', ['phone' => null, 'countries' => $countries])
            </form>
        </div>
        <div class="drawer__footer">
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-phone-create')">Cancel</button>
            <button type="submit" form="form-phone-create" class="btn btn--primary btn--md">Add phone</button>
        </div>
    </div>

    {{-- ========= PHONE: edit ========= --}}
    @foreach($site->phones as $p)
        <div class="drawer-overlay" id="drawer-phone-{{ $p->id }}-overlay" onclick="closeDrawer('drawer-phone-{{ $p->id }}')"></div>
        <div class="drawer" id="drawer-phone-{{ $p->id }}">
            <div class="drawer__header">
                <span class="drawer__title">Edit phone</span>
                <button class="icon-btn" onclick="closeDrawer('drawer-phone-{{ $p->id }}')">✕</button>
            </div>
            <div class="drawer__body">
                <form method="POST" action="{{ route('phones.update', [$site, $p]) }}" id="form-phone-{{ $p->id }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-phone', ['phone' => $p, 'countries' => $countries])
                </form>
            </div>
            <div class="drawer__footer">
                <form method="POST" action="{{ route('phones.destroy', [$site, $p]) }}" class="drawer__footer-left" onsubmit="return confirm('Delete this phone?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Delete</button>
                </form>
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-phone-{{ $p->id }}')">Cancel</button>
                <button type="submit" form="form-phone-{{ $p->id }}" class="btn btn--primary btn--md">Save</button>
            </div>
        </div>
    @endforeach

    {{-- ========= PRICE: create ========= --}}
    <div class="drawer-overlay" id="drawer-price-create-overlay" onclick="closeDrawer('drawer-price-create')"></div>
    <div class="drawer" id="drawer-price-create">
        <div class="drawer__header">
            <span class="drawer__title">Add price</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-price-create')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('prices.store', $site) }}" id="form-price-create">
                @csrf
                @include('admin.sites._form-price', ['price' => null])
            </form>
        </div>
        <div class="drawer__footer">
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-price-create')">Cancel</button>
            <button type="submit" form="form-price-create" class="btn btn--primary btn--md">Add price</button>
        </div>
    </div>

    {{-- ========= PRICE: edit ========= --}}
    @foreach($site->prices as $p)
        <div class="drawer-overlay" id="drawer-price-{{ $p->id }}-overlay" onclick="closeDrawer('drawer-price-{{ $p->id }}')"></div>
        <div class="drawer" id="drawer-price-{{ $p->id }}">
            <div class="drawer__header">
                <span class="drawer__title">Edit price</span>
                <button class="icon-btn" onclick="closeDrawer('drawer-price-{{ $p->id }}')">✕</button>
            </div>
            <div class="drawer__body">
                <form method="POST" action="{{ route('prices.update', [$site, $p]) }}" id="form-price-{{ $p->id }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-price', ['price' => $p])
                </form>
            </div>
            <div class="drawer__footer">
                <form method="POST" action="{{ route('prices.destroy', [$site, $p]) }}" class="drawer__footer-left" onsubmit="return confirm('Delete this price?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Delete</button>
                </form>
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-price-{{ $p->id }}')">Cancel</button>
                <button type="submit" form="form-price-{{ $p->id }}" class="btn btn--primary btn--md">Save</button>
            </div>
        </div>
    @endforeach

    {{-- ========= ADDRESS: create ========= --}}
    <div class="drawer-overlay" id="drawer-addr-create-overlay" onclick="closeDrawer('drawer-addr-create')"></div>
    <div class="drawer" id="drawer-addr-create">
        <div class="drawer__header">
            <span class="drawer__title">Add address</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-addr-create')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('addresses.store', $site) }}" id="form-addr-create">
                @csrf
                @include('admin.sites._form-address', ['address' => null, 'countries' => $countries])
            </form>
        </div>
        <div class="drawer__footer">
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-addr-create')">Cancel</button>
            <button type="submit" form="form-addr-create" class="btn btn--primary btn--md">Add address</button>
        </div>
    </div>

    {{-- ========= ADDRESS: edit ========= --}}
    @foreach($site->addresses as $a)
        <div class="drawer-overlay" id="drawer-addr-{{ $a->id }}-overlay" onclick="closeDrawer('drawer-addr-{{ $a->id }}')"></div>
        <div class="drawer" id="drawer-addr-{{ $a->id }}">
            <div class="drawer__header">
                <span class="drawer__title">Edit address</span>
                <button class="icon-btn" onclick="closeDrawer('drawer-addr-{{ $a->id }}')">✕</button>
            </div>
            <div class="drawer__body">
                <form method="POST" action="{{ route('addresses.update', [$site, $a]) }}" id="form-addr-{{ $a->id }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-address', ['address' => $a, 'countries' => $countries])
                </form>
            </div>
            <div class="drawer__footer">
                <form method="POST" action="{{ route('addresses.destroy', [$site, $a]) }}" class="drawer__footer-left" onsubmit="return confirm('Delete this address?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Delete</button>
                </form>
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-addr-{{ $a->id }}')">Cancel</button>
                <button type="submit" form="form-addr-{{ $a->id }}" class="btn btn--primary btn--md">Save</button>
            </div>
        </div>
    @endforeach

    {{-- ========= SOCIAL: create ========= --}}
    <div class="drawer-overlay" id="drawer-soc-create-overlay" onclick="closeDrawer('drawer-soc-create')"></div>
    <div class="drawer" id="drawer-soc-create">
        <div class="drawer__header">
            <span class="drawer__title">Add social link</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-soc-create')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('socials.store', $site) }}" id="form-soc-create">
                @csrf
                @include('admin.sites._form-social', ['social' => null])
            </form>
        </div>
        <div class="drawer__footer">
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-soc-create')">Cancel</button>
            <button type="submit" form="form-soc-create" class="btn btn--primary btn--md">Add link</button>
        </div>
    </div>

    {{-- ========= ADD GEO ========= --}}
    @php
        $availableCountries = $countries->reject(fn($c) => in_array($c->iso, $usedIso, true))->values();
    @endphp
    <div class="drawer-overlay" id="drawer-geo-add-overlay" onclick="closeDrawer('drawer-geo-add')"></div>
    <div class="drawer" id="drawer-geo-add">
        <form method="POST" action="{{ route('sites.geos.add', $site) }}" id="form-geo-add">
            @csrf
            <div class="drawer__header">
                <span class="drawer__title">Add geo</span>
                <button class="icon-btn" type="button" onclick="closeDrawer('drawer-geo-add')">✕</button>
            </div>
            <div class="drawer__body">
                <p style="font-size:13px;color:var(--text-2);margin:0 0 14px;">
                    Pick a country to add to this site. The new geo will appear as a tab — you can then add phones, addresses and other data tagged to it.
                </p>
                <div class="field">
                    <label class="field__label" for="geo-pick">Country</label>
                    <select name="country_iso" id="geo-pick" class="field__input" required>
                        @forelse($availableCountries as $c)
                            <option value="{{ $c->iso }}">
                                {{ $c->iso }} {{ ($c->name && strcasecmp($c->name, $c->iso) !== 0) ? '— '.$c->name : '' }}
                            </option>
                        @empty
                            <option value="">All countries already added</option>
                        @endforelse
                    </select>
                </div>

                @if($availableCountries->isNotEmpty())
                    <div style="margin-top:18px;">
                        <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:8px;">Quick pick</div>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            @foreach($availableCountries->take(24) as $c)
                                <button type="button"
                                        onclick="document.getElementById('geo-pick').value='{{ $c->iso }}';"
                                        style="padding:5px 10px;background:var(--panel-2);border:1px solid var(--border);border-radius:99px;font-family:var(--font-mono);font-size:11px;font-weight:600;color:var(--text-2);cursor:pointer;">
                                    {{ $c->iso }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="drawer__footer">
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-geo-add')">Cancel</button>
                <button type="submit" class="btn btn--primary btn--md" {{ $availableCountries->isEmpty() ? 'disabled' : '' }}>Add geo</button>
            </div>
        </form>
    </div>

    {{-- ========= SOCIAL: edit ========= --}}
    @foreach($site->socials as $s)
        <div class="drawer-overlay" id="drawer-soc-{{ $s->id }}-overlay" onclick="closeDrawer('drawer-soc-{{ $s->id }}')"></div>
        <div class="drawer" id="drawer-soc-{{ $s->id }}">
            <div class="drawer__header">
                <span class="drawer__title">Edit social link</span>
                <button class="icon-btn" onclick="closeDrawer('drawer-soc-{{ $s->id }}')">✕</button>
            </div>
            <div class="drawer__body">
                <form method="POST" action="{{ route('socials.update', [$site, $s]) }}" id="form-soc-{{ $s->id }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-social', ['social' => $s])
                </form>
            </div>
            <div class="drawer__footer">
                <form method="POST" action="{{ route('socials.destroy', [$site, $s]) }}" class="drawer__footer-left" onsubmit="return confirm('Delete this link?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Delete</button>
                </form>
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-soc-{{ $s->id }}')">Cancel</button>
                <button type="submit" form="form-soc-{{ $s->id }}" class="btn btn--primary btn--md">Save</button>
            </div>
        </div>
    @endforeach

@endif

@endsection
