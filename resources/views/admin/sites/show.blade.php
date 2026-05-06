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

    // Hardcoded ISO map — used in Add Geo drawer + JS (always available, any tab)
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

    // active_geos is {"UA":"Ukraine","RO":"Romania"} or legacy ["UA","RO"] — normalize to assoc.
    $activeGeosRaw = (array) ($site->active_geos ?? []);
    $geoNames = array_is_list($activeGeosRaw)
        ? array_fill_keys($activeGeosRaw, '')
        : $activeGeosRaw;
    $usedIso  = array_keys($geoNames);
    sort($usedIso);

    $countriesByIso = $countries->keyBy('iso');

    // Geo rules: data-centric — for each geo tab's data, which visitors can see it.
    // Structure: { "UA": { "mode": "all|include|exclude", "countries": ["RU","BY"] } }
    $geoRules = (array) ($site->geo_rules ?? []);

    // Per-item visitor geo visibility helper.
    // Returns true if item with given geo_mode/geo_countries is visible to $visitorIso.
    $geoVis = function ($geoMode, $geoCountries, $visitorIso): bool {
        $mode   = $geoMode ?? 'all';
        $ctries = (array) ($geoCountries ?? []);
        return match($mode) {
            'include' => in_array($visitorIso, $ctries),
            'exclude' => !in_array($visitorIso, $ctries),
            default   => true,
        };
    };

    // ISO options available for rule editor chips (active geos of this site).
    $visRuleOptions = $usedIso;

    // Each tab shows only records tagged to THAT tab (country_iso === tab ISO).
    // "All geos" shows everything. Records with no country_iso appear everywhere.
    $filterByGeo = function ($collection) use ($country) {
        if ($country === 'all') return $collection;
        return $collection->filter(function ($item) use ($country) {
            $iso = $item->country_iso ?? null;
            return $iso === null || $iso === $country;
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
                <a href="{{ route('sites.index') }}">Сайти</a> / <span style="color:var(--text);">{{ $site->name }}</span>
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
                    Відкрити
                </a>
            @endif
            <button class="btn btn--secondary btn--md">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 0 1 15.5-6.3L21 8"/><path d="M21 4v4h-4"/>
                    <path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"/><path d="M3 20v-4h4"/>
                </svg>
                Синхронізувати
            </button>
            <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-site-edit')">Оновити дані</button>
        </div>
    </div>

    {{-- ========= 5 MINI STATS ========= --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;">
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Статус</div>
            <div><x-status-pill :status="$statusName"/></div>
        </div>
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Група</div>
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
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Гео</div>
            <div style="font-size:18px;font-weight:600;color:var(--text);">{{ count($usedIso) }}</div>
        </div>
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Телефони</div>
            <div style="font-size:18px;font-weight:600;">{{ $site->phones->count() }}</div>
        </div>
        <div class="card" style="padding:14px 16px;">
            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Остання синхр.</div>
            <div style="font-size:13px;font-weight:600;color:var(--text-2);">{{ $syncWhen }}</div>
        </div>
    </div>

    {{-- ========= MAIN TAB CARD ========= --}}
    <div class="card card--flush">

        {{-- ========= TABS — OVERVIEW/DATA/ACTIVITY/SETTINGS ========= --}}
        <div class="tabs">
            <a href="{{ $url(['tab' => 'overview']) }}" class="tabs__item {{ $tab === 'overview' ? 'is-active' : '' }}">Огляд</a>
            <a href="{{ $url(['tab' => 'data']) }}"     class="tabs__item {{ $tab === 'data'     ? 'is-active' : '' }}">Дані</a>
            <a href="{{ $url(['tab' => 'activity']) }}" class="tabs__item {{ $tab === 'activity' ? 'is-active' : '' }}">Активність</a>
            <a href="{{ $url(['tab' => 'settings']) }}" class="tabs__item {{ $tab === 'settings' ? 'is-active' : '' }}">Налаштування</a>
        </div>

        {{-- ========= OVERVIEW ========= --}}
        @if($tab === 'overview')
        @php
            $allVisitorIsos = collect($usedIso)
                ->merge($site->phones->flatMap(fn($p) => (array)($p->geo_countries ?? [])))
                ->merge($site->prices->flatMap(fn($p) => (array)($p->geo_countries ?? [])))
                ->merge($site->addresses->flatMap(fn($a) => (array)($a->geo_countries ?? [])))
                ->merge($site->socials->flatMap(fn($s) => (array)($s->geo_countries ?? [])))
                ->filter()->unique()->sort()->values()->toArray();
            $hasAnyData = $site->phones->count() + $site->prices->count() + $site->addresses->count() + $site->socials->count() > 0;
            $conflicts = [];
            foreach ([
                ['Телефони',  $site->phones,    fn($p) => ($p->dial_code ? '+'.$p->dial_code.' ' : '') . $p->number],
                ['Ціни',      $site->prices,    fn($p) => $p->label . ' — ' . number_format($p->amount, 2) . ' ' . $p->currency],
                ['Адреси',    $site->addresses, fn($a) => $a->city . ($a->street ? ', '.$a->street : '')],
                ['Соцмережі', $site->socials,   fn($s) => ucfirst($s->platform).': '.$s->handle],
            ] as [$typeName, $coll, $labelFn]) {
                foreach ($coll as $item) {
                    $mode   = $item->geo_mode ?? 'all';
                    $ctries = (array)($item->geo_countries ?? []);
                    if ($mode === 'include' && count($ctries) === 0) {
                        $conflicts[] = ['type' => $typeName, 'label' => $labelFn($item), 'issue' => 'Правило «Тільки» без країн — ніколи не показується'];
                    } elseif ($mode !== 'all' && count($allVisitorIsos) > 0) {
                        $visibleToAny = false;
                        foreach ($allVisitorIsos as $chkIso) {
                            if ($geoVis($mode, $ctries, $chkIso)) { $visibleToAny = true; break; }
                        }
                        if (!$visibleToAny) {
                            $conflicts[] = ['type' => $typeName, 'label' => $labelFn($item), 'issue' => 'Не показується жодному активному гео'];
                        }
                    }
                }
            }
        @endphp

            {{-- Info + Sync row --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--border-2);">
                <div style="background:var(--panel);padding:20px;">
                    <h4 style="margin:0 0 12px;font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Інфо про сайт</h4>
                    <div class="kv"><span class="kv__k">Домен</span><span class="kv__v mono">{{ $site->url }}</span></div>
                    <div class="kv"><span class="kv__k">Група</span><span class="kv__v">{{ $site->siteGroup?->name ?? '—' }}</span></div>
                    <div class="kv"><span class="kv__k">Статус</span><span class="kv__v">{{ $statusName }}</span></div>
                    <div class="kv"><span class="kv__k">Додано</span><span class="kv__v">{{ $site->created_at->format('d M Y') }}</span></div>
                </div>
                <div style="background:var(--panel);padding:20px;">
                    <h4 style="margin:0 0 12px;font-size:12px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Синхронізація</h4>
                    <div class="kv"><span class="kv__k">Остання синхр.</span><span class="kv__v">{{ $syncWhen }}</span></div>
                    <div class="kv"><span class="kv__k">Статус синхр.</span><span class="kv__v">
                        @if($syncLog?->status === 'success')
                            <span class="pill pill--success"><span class="dot dot--success"></span>OK</span>
                        @elseif($syncLog?->status === 'error')
                            <span class="pill pill--danger"><span class="dot dot--danger"></span>Помилка</span>
                        @else
                            <span class="pill pill--neutral">Немає даних</span>
                        @endif
                    </span></div>
                    <div class="kv"><span class="kv__k">Вебхук</span><span class="kv__v">
                        @if($site->plugin_webhook_url)
                            <span class="pill pill--success"><span class="dot dot--success"></span>Активний</span>
                        @else
                            <span class="pill pill--neutral">Не налаштовано</span>
                        @endif
                    </span></div>
                </div>
            </div>

            @if($hasAnyData && count($allVisitorIsos) > 0)
                {{-- Country selector tabs --}}
                <div style="border-top:1px solid var(--border-2);padding:10px 16px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;background:var(--panel-2);">
                    <span style="font-size:11px;color:var(--text-3);font-weight:600;margin-right:4px;">Перегляд для:</span>
                    @foreach($allVisitorIsos as $visIso)
                        <button onclick="showVisitorPanel('{{ $visIso }}')" id="vis-tab-{{ $visIso }}"
                                class="btn btn--sm {{ $loop->first ? 'btn--primary' : 'btn--ghost' }}"
                                style="font-family:var(--font-mono);font-weight:700;">{{ $visIso }}</button>
                    @endforeach
                </div>

                {{-- Per-ISO panel: visitor preview (LEFT) + matrix (RIGHT) --}}
                @foreach($allVisitorIsos as $visIso)
                    @php
                        $vPhones  = $site->phones->filter(fn($p)  => ($p->is_visible ?? true) && $geoVis($p->geo_mode, $p->geo_countries, $visIso));
                        $vPrices  = $site->prices->filter(fn($p)  => ($p->is_visible ?? true) && $geoVis($p->geo_mode, $p->geo_countries, $visIso));
                        $vAddrs   = $site->addresses->filter(fn($a) => ($a->is_visible ?? true) && $geoVis($a->geo_mode, $a->geo_countries, $visIso));
                        $vSocials = $site->socials->filter(fn($s)  => ($s->is_visible ?? true) && $geoVis($s->geo_mode, $s->geo_countries, $visIso));
                        $totalVis = $vPhones->count() + $vPrices->count() + $vAddrs->count() + $vSocials->count();
                        $totalAll = $site->phones->count() + $site->prices->count() + $site->addresses->count() + $site->socials->count();
                    @endphp
                    <div id="vis-panel-{{ $visIso }}" style="{{ $loop->first ? '' : 'display:none;' }}">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--border-2);">

                            {{-- LEFT: Що бачить відвідувач --}}
                            <div style="background:var(--panel);padding:20px;">
                                <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#34d399;display:inline-block;"></span>
                                    Що бачить відвідувач — {{ $visIso }}
                                </div>

                                @forelse($vPhones as $p)
                                    <div style="background:var(--panel-2);border-radius:var(--radius-item);padding:8px 10px;margin-bottom:6px;">
                                        <div style="font-family:var(--font-mono);font-size:13px;font-weight:600;">{{ ($p->dial_code ? '+'.$p->dial_code.' ' : '') . $p->number }}</div>
                                        @if($p->label)<div style="font-size:11px;color:var(--text-3);margin-top:1px;">{{ $p->label }}</div>@endif
                                    </div>
                                @empty
                                    <div style="font-size:11px;color:var(--text-3);margin-bottom:6px;">— телефони не показуються</div>
                                @endforelse

                                @if($vPrices->count())
                                    <div style="margin-top:10px;font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Ціни</div>
                                    @foreach($vPrices as $p)
                                        <div style="display:flex;justify-content:space-between;background:var(--panel-2);border-radius:var(--radius-item);padding:6px 10px;margin-bottom:4px;font-size:12px;">
                                            <span style="color:var(--text-2);">{{ $p->label }}</span>
                                            <span style="font-family:var(--font-mono);font-weight:700;color:#34d399;">{{ $p->amount }} {{ $p->currency }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                @if($vAddrs->count())
                                    <div style="margin-top:10px;font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px;">Адреси</div>
                                    @foreach($vAddrs as $a)
                                        <div style="font-size:12px;color:var(--text-2);background:var(--panel-2);border-radius:var(--radius-item);padding:6px 10px;margin-bottom:4px;">
                                            {{ trim(($a->city ?? '').' '.($a->street ?? '')) ?: '—' }}
                                        </div>
                                    @endforeach
                                @endif

                                @if($vSocials->count())
                                    <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:5px;">
                                        @foreach($vSocials as $s)
                                            <span style="padding:3px 9px;background:var(--panel-2);border:1px solid var(--border);border-radius:99px;font-size:11px;color:var(--text-2);">{{ ucfirst($s->platform) }}: {{ $s->handle }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                @if($totalVis === 0)
                                    <div style="text-align:center;padding:16px;color:var(--text-3);font-size:12px;">Нічого не показується для {{ $visIso }}</div>
                                @endif
                            </div>

                            {{-- RIGHT: Матриця видимості --}}
                            <div style="background:var(--panel);padding:20px;overflow-x:auto;">
                                <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:14px;display:flex;align-items:center;gap:8px;">
                                    Матриця
                                    <span style="font-family:var(--font-mono);font-weight:700;font-size:12px;color:{{ $totalVis === $totalAll ? '#34d399' : ($totalVis > 0 ? 'var(--warning)' : '#f87171') }};">{{ $totalVis }}/{{ $totalAll }}</span>
                                </div>
                                <table style="width:100%;border-collapse:collapse;font-size:11px;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left;padding:4px 6px;color:var(--text-3);font-weight:500;border-bottom:1px solid var(--border-2);font-size:10px;">Запис</th>
                                            @foreach($allVisitorIsos as $matIso)
                                                <th style="text-align:center;padding:4px 5px;font-family:var(--font-mono);font-size:10px;font-weight:700;border-bottom:1px solid var(--border-2);color:{{ $matIso === $visIso ? 'var(--accent)' : 'var(--text-3)' }};">{{ $matIso }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($site->phones as $mp)
                                        <tr style="border-bottom:1px solid var(--border-2);">
                                            <td style="padding:4px 6px;color:var(--text-2);font-family:var(--font-mono);font-size:10px;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;"
                                                title="{{ ($mp->dial_code ? '+'.$mp->dial_code.' ' : '') . $mp->number }}"
                                                onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-phone-{{ $mp->id }}'">тел. {{ $mp->number }}</td>
                                            @foreach($allVisitorIsos as $matIso)
                                                @php $mok = ($mp->is_visible ?? true) && $geoVis($mp->geo_mode, $mp->geo_countries, $matIso); @endphp
                                                <td style="text-align:center;padding:4px 5px;background:{{ $matIso === $visIso ? 'var(--accent-2)' : 'transparent' }};"><span style="color:{{ $mok ? '#34d399' : '#f87171' }};font-weight:700;">{{ $mok ? '✓' : '✗' }}</span></td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                        @foreach($site->prices as $mpr)
                                        <tr style="border-bottom:1px solid var(--border-2);">
                                            <td style="padding:4px 6px;color:var(--text-2);font-size:10px;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;"
                                                title="{{ $mpr->label }}"
                                                onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-price-{{ $mpr->id }}'">ціна {{ $mpr->label }}</td>
                                            @foreach($allVisitorIsos as $matIso)
                                                @php $mok = ($mpr->is_visible ?? true) && $geoVis($mpr->geo_mode, $mpr->geo_countries, $matIso); @endphp
                                                <td style="text-align:center;padding:4px 5px;background:{{ $matIso === $visIso ? 'var(--accent-2)' : 'transparent' }};"><span style="color:{{ $mok ? '#34d399' : '#f87171' }};font-weight:700;">{{ $mok ? '✓' : '✗' }}</span></td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                        @foreach($site->addresses as $ma)
                                        <tr style="border-bottom:1px solid var(--border-2);">
                                            <td style="padding:4px 6px;color:var(--text-2);font-size:10px;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;"
                                                title="{{ $ma->city }}"
                                                onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-addr-{{ $ma->id }}'">адр. {{ $ma->city }}</td>
                                            @foreach($allVisitorIsos as $matIso)
                                                @php $mok = ($ma->is_visible ?? true) && $geoVis($ma->geo_mode, $ma->geo_countries, $matIso); @endphp
                                                <td style="text-align:center;padding:4px 5px;background:{{ $matIso === $visIso ? 'var(--accent-2)' : 'transparent' }};"><span style="color:{{ $mok ? '#34d399' : '#f87171' }};font-weight:700;">{{ $mok ? '✓' : '✗' }}</span></td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                        @foreach($site->socials as $ms)
                                        <tr style="border-bottom:1px solid var(--border-2);">
                                            <td style="padding:4px 6px;color:var(--text-2);font-size:10px;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:pointer;"
                                                title="{{ ucfirst($ms->platform).': '.$ms->handle }}"
                                                onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-social-{{ $ms->id }}'">{{ $ms->platform }} {{ $ms->handle }}</td>
                                            @foreach($allVisitorIsos as $matIso)
                                                @php $mok = ($ms->is_visible ?? true) && $geoVis($ms->geo_mode, $ms->geo_countries, $matIso); @endphp
                                                <td style="text-align:center;padding:4px 5px;background:{{ $matIso === $visIso ? 'var(--accent-2)' : 'transparent' }};"><span style="color:{{ $mok ? '#34d399' : '#f87171' }};font-weight:700;">{{ $mok ? '✓' : '✗' }}</span></td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Conflicts — always visible so manager can verify setup --}}
                <div style="border-top:1px solid var(--border-2);padding:12px 20px;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:{{ count($conflicts) ? '10px' : '0' }};">
                        @if(count($conflicts) > 0)
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span style="font-size:12px;font-weight:600;color:#f87171;text-transform:uppercase;letter-spacing:.05em;">Конфлікти ({{ count($conflicts) }})</span>
                        @else
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            <span style="font-size:12px;font-weight:600;color:#34d399;text-transform:uppercase;letter-spacing:.05em;">Конфліктів не виявлено</span>
                        @endif
                    </div>
                    @if(count($conflicts) > 0)
                        <div style="display:flex;flex-direction:column;gap:5px;">
                            @foreach($conflicts as $cf)
                                <div style="display:flex;align-items:center;gap:10px;padding:7px 10px;background:#f871710d;border:1px solid #f8717133;border-radius:var(--radius);">
                                    <span style="font-size:10px;color:var(--text-3);font-weight:600;flex-shrink:0;min-width:64px;">{{ $cf['type'] }}</span>
                                    <span style="font-size:11px;color:var(--text-2);font-family:var(--font-mono);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $cf['label'] }}</span>
                                    <span style="font-size:11px;color:#f87171;flex-shrink:0;text-align:right;">{{ $cf['issue'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div style="border-top:1px solid var(--border-2);padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">
                    Ще немає даних. <a href="{{ $url(['tab' => 'data']) }}" style="color:var(--accent);">Додати →</a>
                </div>
            @endif
        @endif

        {{-- ========= DATA ========= --}}
        @if($tab === 'data')


            {{-- ===== DATA GRID ===== --}}
            @php
                $dtIcons = [
                    'phones'    => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
                    'prices'    => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                    'addresses' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
                    'socials'   => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
                ];
                $socialPlatforms = ['instagram'=>'Instagram','facebook'=>'Facebook','telegram'=>'Telegram','linkedin'=>'LinkedIn','x'=>'X / Twitter','whatsapp'=>'WhatsApp','viber'=>'Viber','youtube'=>'YouTube'];
            @endphp

            {{-- ── Geo mini-bar: active geos + add/remove ────────────── --}}
            <div style="display:flex;align-items:center;gap:6px;padding:8px 16px;border-bottom:1px solid var(--border-2);background:var(--panel-2);flex-wrap:wrap;">
                <span style="font-size:11px;color:var(--text-3);font-weight:600;flex-shrink:0;">Гео:</span>
                @forelse($usedIso as $iso)
                    <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;background:var(--panel);border:1px solid var(--border);border-radius:99px;font-family:var(--font-mono);font-size:11px;font-weight:700;color:var(--text-2);">
                        {{ $iso }}
                        <button type="button" onclick="openDrawer('drawer-geo-remove-{{ $iso }}')"
                                style="background:none;border:none;padding:0;margin-left:3px;cursor:pointer;color:var(--text-3);font-size:12px;line-height:1;" title="Видалити {{ $iso }}">✕</button>
                    </span>
                @empty
                    <span style="font-size:12px;color:var(--text-3);">Немає гео</span>
                @endforelse
                <button class="btn btn--ghost btn--sm" type="button" onclick="openDrawer('drawer-geo-add')" style="margin-left:auto;">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="margin-right:3px;vertical-align:-1px;"><path d="M12 5v14M5 12h14"/></svg>
                    Додати гео
                </button>
            </div>

            <div class="dt-grid">

            {{-- ═══ PHONES ═══════════════════════════════════════════ --}}
            <div class="dt-card">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">{!! $dtIcons['phones'] !!}</span>
                    <span class="dt-card-head__title">Телефони</span>
                    <span class="dt-card-head__count">{{ $site->phones->count() }}</span>
                    <button class="dt-add-btn" id="dt-add-btn-phones" onclick="dtToggleAdd('phones')">
                        <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Додати
                    </button>
                </div>

                {{-- Add panel --}}
                <div class="dt-panel" id="dt-add-phones" style="display:none;">
                    <div class="dt-panel__title">Новий телефон</div>
                    <form method="POST" action="{{ route('phones.store', $site) }}">
                        @csrf
                        <input type="hidden" name="sort_order" value="{{ $site->phones->count() }}">
                        <div class="dt-row dt-row--2">
                            <div>
                                <label class="dt-label">Номер *</label>
                                <input type="text" name="number" class="dt-input" placeholder="50 123 4567" required>
                            </div>
                            <div>
                                <label class="dt-label">Мітка</label>
                                <input type="text" name="label" class="dt-input" placeholder="Головний…">
                            </div>
                        </div>
                        <label class="dt-label">Гео-правило</label>
                        <div class="dt-geo-row">
                            <span class="dt-geo-label">Видно:</span>
                            @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                <label class="dt-geo-pill {{ $mv==='all'?'is-on':'' }}" id="dtpill-add-ph-{{ $mv }}">
                                    <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $mv==='all'?'checked':'' }} style="display:none;"
                                           onchange="dtGeoMode('add-ph','{{ $mv }}')">{{ $ml }}
                                </label>
                            @endforeach
                            <span id="dtchips-add-ph" style="display:none;display:flex;gap:3px;">
                                @foreach($usedIso as $iso)
                                    <label class="dt-geo-chip" id="dtchip-add-ph-{{ $iso }}">
                                        <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" style="display:none;"
                                               onchange="dtGeoChip('add-ph','{{ $iso }}',this)">{{ $iso }}
                                    </label>
                                @endforeach
                            </span>
                        </div>
                        <div class="dt-panel__actions">
                            <button type="button" class="btn btn--ghost btn--sm" onclick="dtToggleAdd('phones')">Скасувати</button>
                            <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                        </div>
                    </form>
                </div>

                <div class="dt-items">
                    @forelse($shownPhones as $p)
                    <div class="dt-item">
                        <div class="dt-item-row" onclick="dtExpandItem('phone-{{ $p->id }}')">
                            <span class="dt-item-icon">{!! $dtIcons['phones'] !!}</span>
                            <div class="dt-item-main">
                                <div class="dt-item-name" style="font-family:var(--font-mono);">
                                    {{ ($p->dial_code ? '+'.$p->dial_code.' ' : '') . $p->number }}
                                </div>
                                @if($p->label || $p->is_primary)
                                    <div class="dt-item-sub">{{ $p->label }}{{ $p->is_primary ? ($p->label ? ' · ' : '').'основний' : '' }}</div>
                                @endif
                            </div>
                            <div class="dt-vis">
                                @if(count($usedIso)===0 || ($p->geo_mode??'all')==='all')
                                    <span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                                @else
                                    @foreach($usedIso as $iso)
                                        @php $ok=$geoVis($p->geo_mode,$p->geo_countries,$iso); @endphp
                                        <span class="dt-vis-badge dt-vis-badge--{{ $ok?'ok':'no' }}">{{ $iso }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="dt-item-actions" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('sites.visibility.toggle',[$site,'phones',$p->id]) }}" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="icon-btn" title="{{ ($p->is_visible??true)?'Приховати':'Показати' }}" style="color:{{ ($p->is_visible??true)?'var(--text-3)':'var(--warning)' }};">
                                        @if($p->is_visible??true)
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('phones.destroy',[$site,$p]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                    </button>
                                </form>
                                <button class="icon-btn" id="dt-expand-phone-{{ $p->id }}" title="Редагувати">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>
                        {{-- Edit panel --}}
                        <div class="dt-panel" id="dt-edit-phone-{{ $p->id }}" style="display:none;">
                            <form method="POST" action="{{ route('phones.update',[$site,$p]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="sort_order" value="{{ $p->sort_order }}">
                                <div class="dt-row dt-row--2">
                                    <div>
                                        <label class="dt-label">Номер *</label>
                                        <input type="text" name="number" class="dt-input" value="{{ $p->number }}" required>
                                    </div>
                                    <div>
                                        <label class="dt-label">Мітка</label>
                                        <input type="text" name="label" class="dt-input" value="{{ $p->label }}" placeholder="Головний…">
                                    </div>
                                </div>
                                <div class="dt-geo-row">
                                    <span class="dt-geo-label">Видно:</span>
                                    @php $em = $p->geo_mode ?? 'all'; @endphp
                                    @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                        <label class="dt-geo-pill {{ $em===$mv?'is-on':'' }}" id="dtpill-ph{{ $p->id }}-{{ $mv }}">
                                            <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $em===$mv?'checked':'' }} style="display:none;"
                                                   onchange="dtGeoMode('ph{{ $p->id }}','{{ $mv }}')">{{ $ml }}
                                        </label>
                                    @endforeach
                                    @if(count($usedIso))
                                    <span id="dtchips-ph{{ $p->id }}" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};gap:3px;">
                                        @foreach($usedIso as $iso)
                                            <label class="dt-geo-chip {{ in_array($iso,(array)($p->geo_countries??[]))?'is-on':'' }}" id="dtchip-ph{{ $p->id }}-{{ $iso }}">
                                                <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" {{ in_array($iso,(array)($p->geo_countries??[]))?'checked':'' }} style="display:none;"
                                                       onchange="dtGeoChip('ph{{ $p->id }}','{{ $iso }}',this)">{{ $iso }}
                                            </label>
                                        @endforeach
                                    </span>
                                    @endif
                                </div>
                                <div style="display:flex;align-items:center;gap:6px;margin-top:8px;">
                                    <input type="hidden" name="is_primary" value="0">
                                    <input type="checkbox" name="is_primary" value="1" id="ph-prim-{{ $p->id }}" {{ $p->is_primary?'checked':'' }} style="accent-color:var(--accent);width:14px;height:14px;">
                                    <label for="ph-prim-{{ $p->id }}" class="dt-label" style="margin:0;cursor:pointer;">Основний</label>
                                </div>
                                <div class="dt-panel__actions">
                                    <button type="button" class="btn btn--ghost btn--sm" onclick="dtExpandItem('phone-{{ $p->id }}')">Скасувати</button>
                                    <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                        <div class="dt-empty">Телефонів немає</div>
                    @endforelse
                </div>
            </div>

            {{-- ═══ PRICES ═══════════════════════════════════════════ --}}
            <div class="dt-card">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">{!! $dtIcons['prices'] !!}</span>
                    <span class="dt-card-head__title">Ціни</span>
                    <span class="dt-card-head__count">{{ $site->prices->count() }}</span>
                    <button class="dt-add-btn" id="dt-add-btn-prices" onclick="dtToggleAdd('prices')">
                        <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Додати
                    </button>
                </div>

                <div class="dt-panel" id="dt-add-prices" style="display:none;">
                    <div class="dt-panel__title">Нова ціна</div>
                    <form method="POST" action="{{ route('prices.store', $site) }}">
                        @csrf
                        <input type="hidden" name="sort_order" value="{{ $site->prices->count() }}">
                        <div class="dt-row" style="margin-bottom:8px;">
                            <label class="dt-label">Мітка *</label>
                            <input type="text" name="label" class="dt-input" placeholder="Стандартний, Преміум…" required>
                        </div>
                        <div class="dt-row dt-row--3">
                            <div>
                                <label class="dt-label">Сума *</label>
                                <input type="number" name="amount" step="0.01" min="0" class="dt-input" placeholder="0" required>
                            </div>
                            <div>
                                <label class="dt-label">Валюта *</label>
                                <input type="text" name="currency" class="dt-input" placeholder="UAH" maxlength="3" style="text-transform:uppercase;" required>
                            </div>
                            <div>
                                <label class="dt-label">Період</label>
                                <input type="text" name="period" class="dt-input" placeholder="місяць…">
                            </div>
                        </div>
                        <div class="dt-geo-row">
                            <span class="dt-geo-label">Видно:</span>
                            @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                <label class="dt-geo-pill {{ $mv==='all'?'is-on':'' }}" id="dtpill-add-pr-{{ $mv }}">
                                    <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $mv==='all'?'checked':'' }} style="display:none;"
                                           onchange="dtGeoMode('add-pr','{{ $mv }}')">{{ $ml }}
                                </label>
                            @endforeach
                            <span id="dtchips-add-pr" style="display:none;gap:3px;">
                                @foreach($usedIso as $iso)
                                    <label class="dt-geo-chip" id="dtchip-add-pr-{{ $iso }}">
                                        <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" style="display:none;"
                                               onchange="dtGeoChip('add-pr','{{ $iso }}',this)">{{ $iso }}
                                    </label>
                                @endforeach
                            </span>
                        </div>
                        <div class="dt-panel__actions">
                            <button type="button" class="btn btn--ghost btn--sm" onclick="dtToggleAdd('prices')">Скасувати</button>
                            <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                        </div>
                    </form>
                </div>

                <div class="dt-items">
                    @forelse($shownPrices as $p)
                    <div class="dt-item">
                        <div class="dt-item-row" onclick="dtExpandItem('price-{{ $p->id }}')">
                            <span class="dt-item-icon">{!! $dtIcons['prices'] !!}</span>
                            <div class="dt-item-main">
                                <div class="dt-item-name">{{ $p->label }}</div>
                                <div class="dt-item-sub" style="font-family:var(--font-mono);">{{ number_format($p->amount,2) }} {{ $p->currency }}{{ $p->period ? ' / '.$p->period : '' }}</div>
                            </div>
                            <div class="dt-vis">
                                @if(count($usedIso)===0 || ($p->geo_mode??'all')==='all')
                                    <span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                                @else
                                    @foreach($usedIso as $iso)
                                        @php $ok=$geoVis($p->geo_mode,$p->geo_countries,$iso); @endphp
                                        <span class="dt-vis-badge dt-vis-badge--{{ $ok?'ok':'no' }}">{{ $iso }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="dt-item-actions" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('sites.visibility.toggle',[$site,'prices',$p->id]) }}" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="icon-btn" title="{{ ($p->is_visible??true)?'Приховати':'Показати' }}" style="color:{{ ($p->is_visible??true)?'var(--text-3)':'var(--warning)' }};">
                                        @if($p->is_visible??true)
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('prices.destroy',[$site,$p]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                    </button>
                                </form>
                                <button class="icon-btn" id="dt-expand-price-{{ $p->id }}" title="Редагувати">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="dt-panel" id="dt-edit-price-{{ $p->id }}" style="display:none;">
                            <form method="POST" action="{{ route('prices.update',[$site,$p]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="sort_order" value="{{ $p->sort_order }}">
                                <div class="dt-row" style="margin-bottom:8px;">
                                    <label class="dt-label">Мітка *</label>
                                    <input type="text" name="label" class="dt-input" value="{{ $p->label }}" required>
                                </div>
                                <div class="dt-row dt-row--3">
                                    <div>
                                        <label class="dt-label">Сума *</label>
                                        <input type="number" name="amount" step="0.01" min="0" class="dt-input" value="{{ $p->amount }}" required>
                                    </div>
                                    <div>
                                        <label class="dt-label">Валюта *</label>
                                        <input type="text" name="currency" class="dt-input" value="{{ $p->currency }}" maxlength="3" style="text-transform:uppercase;" required>
                                    </div>
                                    <div>
                                        <label class="dt-label">Період</label>
                                        <input type="text" name="period" class="dt-input" value="{{ $p->period }}">
                                    </div>
                                </div>
                                <div class="dt-geo-row">
                                    <span class="dt-geo-label">Видно:</span>
                                    @php $em = $p->geo_mode ?? 'all'; @endphp
                                    @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                        <label class="dt-geo-pill {{ $em===$mv?'is-on':'' }}" id="dtpill-pr{{ $p->id }}-{{ $mv }}">
                                            <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $em===$mv?'checked':'' }} style="display:none;"
                                                   onchange="dtGeoMode('pr{{ $p->id }}','{{ $mv }}')">{{ $ml }}
                                        </label>
                                    @endforeach
                                    @if(count($usedIso))
                                    <span id="dtchips-pr{{ $p->id }}" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};gap:3px;">
                                        @foreach($usedIso as $iso)
                                            <label class="dt-geo-chip {{ in_array($iso,(array)($p->geo_countries??[]))?'is-on':'' }}" id="dtchip-pr{{ $p->id }}-{{ $iso }}">
                                                <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" {{ in_array($iso,(array)($p->geo_countries??[]))?'checked':'' }} style="display:none;"
                                                       onchange="dtGeoChip('pr{{ $p->id }}','{{ $iso }}',this)">{{ $iso }}
                                            </label>
                                        @endforeach
                                    </span>
                                    @endif
                                </div>
                                <div class="dt-panel__actions">
                                    <button type="button" class="btn btn--ghost btn--sm" onclick="dtExpandItem('price-{{ $p->id }}')">Скасувати</button>
                                    <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                        <div class="dt-empty">Цін немає</div>
                    @endforelse
                </div>
            </div>

            {{-- ═══ SOCIALS ══════════════════════════════════════════ --}}
            <div class="dt-card">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">{!! $dtIcons['socials'] !!}</span>
                    <span class="dt-card-head__title">Соціальні мережі</span>
                    <span class="dt-card-head__count">{{ $site->socials->count() }}</span>
                    <button class="dt-add-btn" id="dt-add-btn-socials" onclick="dtToggleAdd('socials')">
                        <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Додати
                    </button>
                </div>

                <div class="dt-panel" id="dt-add-socials" style="display:none;">
                    <div class="dt-panel__title">Нова соціальна мережа</div>
                    <form method="POST" action="{{ route('socials.store', $site) }}">
                        @csrf
                        <input type="hidden" name="sort_order" value="{{ $site->socials->count() }}">
                        <div class="dt-row dt-row--2">
                            <div>
                                <label class="dt-label">Платформа *</label>
                                <select name="platform" class="dt-input" required>
                                    @foreach($socialPlatforms as $val => $lbl)
                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="dt-label">Нікнейм *</label>
                                <input type="text" name="handle" class="dt-input" placeholder="@username" required>
                            </div>
                        </div>
                        <div class="dt-row" style="margin-bottom:8px;">
                            <label class="dt-label">URL *</label>
                            <input type="url" name="url" class="dt-input" placeholder="https://…" required>
                        </div>
                        @if($site->phones->count())
                        <div class="dt-row" style="margin-bottom:8px;">
                            <label class="dt-label">Прив'язати до номеру</label>
                            <select name="phone_id" class="dt-input">
                                <option value="">— незалежно —</option>
                                @foreach($site->phones as $ph)
                                    <option value="{{ $ph->id }}">{{ ($ph->dial_code ? '+'.$ph->dial_code.' ' : '') . $ph->number }}{{ $ph->label ? ' · '.$ph->label : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="dt-geo-row">
                            <span class="dt-geo-label">Видно:</span>
                            @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                <label class="dt-geo-pill {{ $mv==='all'?'is-on':'' }}" id="dtpill-add-so-{{ $mv }}">
                                    <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $mv==='all'?'checked':'' }} style="display:none;"
                                           onchange="dtGeoMode('add-so','{{ $mv }}')">{{ $ml }}
                                </label>
                            @endforeach
                            <span id="dtchips-add-so" style="display:none;gap:3px;">
                                @foreach($usedIso as $iso)
                                    <label class="dt-geo-chip" id="dtchip-add-so-{{ $iso }}">
                                        <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" style="display:none;"
                                               onchange="dtGeoChip('add-so','{{ $iso }}',this)">{{ $iso }}
                                    </label>
                                @endforeach
                            </span>
                        </div>
                        <div class="dt-panel__actions">
                            <button type="button" class="btn btn--ghost btn--sm" onclick="dtToggleAdd('socials')">Скасувати</button>
                            <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                        </div>
                    </form>
                </div>

                <div class="dt-items">
                    @forelse($shownSocials as $s)
                    @php
                        $sk  = strtolower($s->platform ?? '');
                        $sic = $socialIcon[$sk] ?? ['c'=>'var(--text-3)','svg'=>'<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/></svg>'];
                    @endphp
                    <div class="dt-item">
                        <div class="dt-item-row" onclick="dtExpandItem('social-{{ $s->id }}')">
                            <span class="dt-item-icon" style="color:{{ $sic['c'] }}">{!! $sic['svg'] !!}</span>
                            <div class="dt-item-main">
                                <div class="dt-item-name">{{ $s->handle }}</div>
                                <div class="dt-item-sub">{{ ucfirst($s->platform) }}</div>
                            </div>
                            <div class="dt-vis">
                                @if(count($usedIso)===0 || ($s->geo_mode??'all')==='all')
                                    <span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                                @else
                                    @foreach($usedIso as $iso)
                                        @php $ok=$geoVis($s->geo_mode,$s->geo_countries,$iso); @endphp
                                        <span class="dt-vis-badge dt-vis-badge--{{ $ok?'ok':'no' }}">{{ $iso }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="dt-item-actions" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('sites.visibility.toggle',[$site,'socials',$s->id]) }}" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="icon-btn" title="{{ ($s->is_visible??true)?'Приховати':'Показати' }}" style="color:{{ ($s->is_visible??true)?'var(--text-3)':'var(--warning)' }};">
                                        @if($s->is_visible??true)
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('socials.destroy',[$site,$s]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                    </button>
                                </form>
                                <button class="icon-btn" id="dt-expand-social-{{ $s->id }}" title="Редагувати">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="dt-panel" id="dt-edit-social-{{ $s->id }}" style="display:none;">
                            <form method="POST" action="{{ route('socials.update',[$site,$s]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="sort_order" value="{{ $s->sort_order }}">
                                <div class="dt-row dt-row--2">
                                    <div>
                                        <label class="dt-label">Платформа *</label>
                                        <select name="platform" class="dt-input" required>
                                            @foreach($socialPlatforms as $val => $lbl)
                                                <option value="{{ $val }}" {{ $s->platform===$val?'selected':'' }}>{{ $lbl }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="dt-label">Нікнейм *</label>
                                        <input type="text" name="handle" class="dt-input" value="{{ $s->handle }}" required>
                                    </div>
                                </div>
                                <div class="dt-row" style="margin-bottom:8px;">
                                    <label class="dt-label">URL *</label>
                                    <input type="url" name="url" class="dt-input" value="{{ $s->url }}" required>
                                </div>
                                @if($site->phones->count())
                                <div class="dt-row" style="margin-bottom:8px;">
                                    <label class="dt-label">Прив'язати до номеру</label>
                                    <select name="phone_id" class="dt-input">
                                        <option value="">— незалежно —</option>
                                        @foreach($site->phones as $ph)
                                            <option value="{{ $ph->id }}" {{ $s->phone_id == $ph->id ? 'selected' : '' }}>{{ ($ph->dial_code ? '+'.$ph->dial_code.' ' : '') . $ph->number }}{{ $ph->label ? ' · '.$ph->label : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div class="dt-geo-row">
                                    <span class="dt-geo-label">Видно:</span>
                                    @php $em = $s->geo_mode ?? 'all'; @endphp
                                    @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                        <label class="dt-geo-pill {{ $em===$mv?'is-on':'' }}" id="dtpill-so{{ $s->id }}-{{ $mv }}">
                                            <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $em===$mv?'checked':'' }} style="display:none;"
                                                   onchange="dtGeoMode('so{{ $s->id }}','{{ $mv }}')">{{ $ml }}
                                        </label>
                                    @endforeach
                                    @if(count($usedIso))
                                    <span id="dtchips-so{{ $s->id }}" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};gap:3px;">
                                        @foreach($usedIso as $iso)
                                            <label class="dt-geo-chip {{ in_array($iso,(array)($s->geo_countries??[]))?'is-on':'' }}" id="dtchip-so{{ $s->id }}-{{ $iso }}">
                                                <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" {{ in_array($iso,(array)($s->geo_countries??[]))?'checked':'' }} style="display:none;"
                                                       onchange="dtGeoChip('so{{ $s->id }}','{{ $iso }}',this)">{{ $iso }}
                                            </label>
                                        @endforeach
                                    </span>
                                    @endif
                                </div>
                                <div class="dt-panel__actions">
                                    <button type="button" class="btn btn--ghost btn--sm" onclick="dtExpandItem('social-{{ $s->id }}')">Скасувати</button>
                                    <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                        <div class="dt-empty">Соціальних мереж немає</div>
                    @endforelse
                </div>
            </div>

            {{-- ═══ ADDRESSES ═════════════════════════════════════════ --}}
            <div class="dt-card">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">{!! $dtIcons['addresses'] !!}</span>
                    <span class="dt-card-head__title">Адреси</span>
                    <span class="dt-card-head__count">{{ $site->addresses->count() }}</span>
                    <button class="dt-add-btn" id="dt-add-btn-addresses" onclick="dtToggleAdd('addresses')">
                        <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Додати
                    </button>
                </div>

                <div class="dt-panel" id="dt-add-addresses" style="display:none;">
                    <div class="dt-panel__title">Нова адреса</div>
                    <form method="POST" action="{{ route('addresses.store', $site) }}">
                        @csrf
                        <input type="hidden" name="sort_order" value="{{ $site->addresses->count() }}">
                        <div class="dt-row dt-row--2">
                            <div>
                                <label class="dt-label">Країна</label>
                                <select name="country_iso" class="dt-input">
                                    <option value="">—</option>
                                    @foreach($countries as $c)
                                        <option value="{{ $c->iso }}">{{ $c->iso }}{{ ($c->name && strcasecmp($c->name,$c->iso)!==0) ? ' — '.$c->name : '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="dt-label">Місто *</label>
                                <input type="text" name="city" class="dt-input" required>
                            </div>
                        </div>
                        <div class="dt-row dt-row--auto">
                            <div>
                                <label class="dt-label">Вулиця</label>
                                <input type="text" name="street" class="dt-input">
                            </div>
                            <div style="width:80px;">
                                <label class="dt-label">Буд.</label>
                                <input type="text" name="building" class="dt-input">
                            </div>
                        </div>
                        <div class="dt-geo-row">
                            <span class="dt-geo-label">Видно:</span>
                            @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                <label class="dt-geo-pill {{ $mv==='all'?'is-on':'' }}" id="dtpill-add-ad-{{ $mv }}">
                                    <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $mv==='all'?'checked':'' }} style="display:none;"
                                           onchange="dtGeoMode('add-ad','{{ $mv }}')">{{ $ml }}
                                </label>
                            @endforeach
                            <span id="dtchips-add-ad" style="display:none;gap:3px;">
                                @foreach($usedIso as $iso)
                                    <label class="dt-geo-chip" id="dtchip-add-ad-{{ $iso }}">
                                        <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" style="display:none;"
                                               onchange="dtGeoChip('add-ad','{{ $iso }}',this)">{{ $iso }}
                                    </label>
                                @endforeach
                            </span>
                        </div>
                        <div class="dt-panel__actions">
                            <button type="button" class="btn btn--ghost btn--sm" onclick="dtToggleAdd('addresses')">Скасувати</button>
                            <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                        </div>
                    </form>
                </div>

                <div class="dt-items">
                    @forelse($shownAddresses as $a)
                    <div class="dt-item">
                        <div class="dt-item-row" onclick="dtExpandItem('addr-{{ $a->id }}')">
                            <span class="dt-item-icon">{!! $dtIcons['addresses'] !!}</span>
                            <div class="dt-item-main">
                                <div class="dt-item-name">{{ $a->city }}{{ $a->street ? ', '.$a->street.($a->building ? ' '.$a->building : '') : '' }}</div>
                                <div class="dt-item-sub">{{ $a->country_iso }}{{ $a->postal_code ? ' · '.$a->postal_code : '' }}</div>
                            </div>
                            <div class="dt-vis">
                                @if(count($usedIso)===0 || ($a->geo_mode??'all')==='all')
                                    <span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                                @else
                                    @foreach($usedIso as $iso)
                                        @php $ok=$geoVis($a->geo_mode,$a->geo_countries,$iso); @endphp
                                        <span class="dt-vis-badge dt-vis-badge--{{ $ok?'ok':'no' }}">{{ $iso }}</span>
                                    @endforeach
                                @endif
                            </div>
                            <div class="dt-item-actions" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('sites.visibility.toggle',[$site,'addresses',$a->id]) }}" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="icon-btn" title="{{ ($a->is_visible??true)?'Приховати':'Показати' }}" style="color:{{ ($a->is_visible??true)?'var(--text-3)':'var(--warning)' }};">
                                        @if($a->is_visible??true)
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('addresses.destroy',[$site,$a]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                    </button>
                                </form>
                                <button class="icon-btn" id="dt-expand-addr-{{ $a->id }}" title="Редагувати">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="dt-panel" id="dt-edit-addr-{{ $a->id }}" style="display:none;">
                            <form method="POST" action="{{ route('addresses.update',[$site,$a]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="sort_order" value="{{ $a->sort_order }}">
                                <div class="dt-row dt-row--2">
                                    <div>
                                        <label class="dt-label">Країна</label>
                                        <select name="country_iso" class="dt-input">
                                            <option value="">—</option>
                                            @foreach($countries as $c)
                                                <option value="{{ $c->iso }}" {{ $a->country_iso===$c->iso?'selected':'' }}>{{ $c->iso }}{{ ($c->name && strcasecmp($c->name,$c->iso)!==0) ? ' — '.$c->name : '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="dt-label">Місто *</label>
                                        <input type="text" name="city" class="dt-input" value="{{ $a->city }}" required>
                                    </div>
                                </div>
                                <div class="dt-row dt-row--auto">
                                    <div>
                                        <label class="dt-label">Вулиця</label>
                                        <input type="text" name="street" class="dt-input" value="{{ $a->street }}">
                                    </div>
                                    <div style="width:80px;">
                                        <label class="dt-label">Буд.</label>
                                        <input type="text" name="building" class="dt-input" value="{{ $a->building }}">
                                    </div>
                                </div>
                                <div class="dt-geo-row">
                                    <span class="dt-geo-label">Видно:</span>
                                    @php $em = $a->geo_mode ?? 'all'; @endphp
                                    @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                        <label class="dt-geo-pill {{ $em===$mv?'is-on':'' }}" id="dtpill-ad{{ $a->id }}-{{ $mv }}">
                                            <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $em===$mv?'checked':'' }} style="display:none;"
                                                   onchange="dtGeoMode('ad{{ $a->id }}','{{ $mv }}')">{{ $ml }}
                                        </label>
                                    @endforeach
                                    @if(count($usedIso))
                                    <span id="dtchips-ad{{ $a->id }}" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};gap:3px;">
                                        @foreach($usedIso as $iso)
                                            <label class="dt-geo-chip {{ in_array($iso,(array)($a->geo_countries??[]))?'is-on':'' }}" id="dtchip-ad{{ $a->id }}-{{ $iso }}">
                                                <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" {{ in_array($iso,(array)($a->geo_countries??[]))?'checked':'' }} style="display:none;"
                                                       onchange="dtGeoChip('ad{{ $a->id }}','{{ $iso }}',this)">{{ $iso }}
                                            </label>
                                        @endforeach
                                    </span>
                                    @endif
                                </div>
                                <div class="dt-panel__actions">
                                    <button type="button" class="btn btn--ghost btn--sm" onclick="dtExpandItem('addr-{{ $a->id }}')">Скасувати</button>
                                    <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                        <div class="dt-empty">Адрес немає</div>
                    @endforelse
                </div>
            </div>

            </div>{{-- /dt-grid --}}
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
                        <span class="activity-row__who-system">система</span>
                        <span class="activity-row__action">
                            {{ $sync->status === 'success' ? 'синхронізовано успішно' : ($sync->status === 'error' ? 'помилка синхронізації' : 'синхронізація...') }}
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
                <div style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">Активності ще немає</div>
            @endforelse
        @endif

        {{-- ========= SETTINGS ========= --}}
        @if($tab === 'settings')

            {{-- ===== Sync settings ===== --}}
            <div style="padding:20px;display:flex;flex-direction:column;gap:0;">
                {{-- Auto-sync --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text);">Авто-синхронізація</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Автоматично отримувати оновлення з сайту</div>
                    </div>
                    <button class="toggle is-on" type="button" onclick="this.classList.toggle('is-on')"></button>
                </div>
                {{-- Sync frequency --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;border-top:1px solid var(--border-2);">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text);">Частота синхронізації</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Як часто отримувати оновлення</div>
                    </div>
                    <div class="select-wrap">
                        <select>
                            <option>Кожні 5 хв</option>
                            <option>Кожні 15 хв</option>
                            <option>Щогодини</option>
                            <option>Лише вручну</option>
                        </select>
                        <span class="select-wrap__chevron"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg></span>
                    </div>
                </div>
                {{-- Allow plugin to push --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;border-top:1px solid var(--border-2);">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text);">Дозволити плагіну відправляти</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Дозволити WP плагіну записувати зміни</div>
                    </div>
                    <button class="toggle" type="button" onclick="this.classList.toggle('is-on')"></button>
                </div>
                {{-- Notify on errors --}}
                <div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:12px 0;border-top:1px solid var(--border-2);">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text);">Сповіщення про помилки</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Надіслати email якщо синхронізація не вдалась</div>
                    </div>
                    <button class="toggle is-on" type="button" onclick="this.classList.toggle('is-on')"></button>
                </div>
                {{-- Footer actions --}}
                <div style="border-top:1px solid var(--border-2);margin-top:6px;padding-top:14px;display:flex;justify-content:space-between;">
                    <form method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Видалити сайт «{{ $site->name }}»?')" style="margin:0;">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn--danger btn--md">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                            Видалити сайт
                        </button>
                    </form>
                    <form method="POST" action="{{ route('sites.api-key.generate', $site) }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn--secondary btn--md">Оновити API ключ</button>
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
        <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-site-edit')">Скасувати</button>
        <button type="submit" form="form-site-edit" class="btn btn--primary btn--md">Зберегти</button>
    </div>
</div>

{{-- ===================== DATA CRUD DRAWERS (only on Data tab) ===================== --}}
@if($tab === 'data')

    {{-- ========= PHONE: create ========= --}}
    <div class="drawer-overlay" id="drawer-phone-create-overlay" onclick="closeDrawer('drawer-phone-create')"></div>
    <div class="drawer" id="drawer-phone-create">
        <div class="drawer__header">
            <span class="drawer__title">Додати телефон</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-phone-create')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('phones.store', $site) }}" id="form-phone-create">
                @csrf
                <input type="hidden" name="country_iso" id="ph-iso-hidden">
                <input type="hidden" name="dial_code" id="ph-dialcode-hidden">
                @include('admin.sites._form-phone', ['phone' => null, 'visRuleOptions' => $visRuleOptions])
            </form>
        </div>
        <div class="drawer__footer">
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-phone-create')">Скасувати</button>
            <button type="submit" form="form-phone-create" class="btn btn--primary btn--md">Додати телефон</button>
        </div>
    </div>

    {{-- ========= PHONE: edit ========= --}}
    @foreach($site->phones as $p)
        <div class="drawer-overlay" id="drawer-phone-{{ $p->id }}-overlay" onclick="closeDrawer('drawer-phone-{{ $p->id }}')"></div>
        <div class="drawer" id="drawer-phone-{{ $p->id }}">
            <div class="drawer__header">
                <span class="drawer__title">Редагувати телефон</span>
                <button class="icon-btn" onclick="closeDrawer('drawer-phone-{{ $p->id }}')">✕</button>
            </div>
            <div class="drawer__body">
                <form method="POST" action="{{ route('phones.update', [$site, $p]) }}" id="form-phone-{{ $p->id }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="country_iso" value="{{ $p->country_iso }}">
                    <input type="hidden" name="dial_code" value="{{ $p->dial_code }}">
                    @include('admin.sites._form-phone', ['phone' => $p, 'visRuleOptions' => $visRuleOptions])
                </form>
            </div>
            <div class="drawer__footer">
                <form method="POST" action="{{ route('phones.destroy', [$site, $p]) }}" class="drawer__footer-left" onsubmit="return confirm('Видалити цей телефон?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Видалити</button>
                </form>
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-phone-{{ $p->id }}')">Скасувати</button>
                <button type="submit" form="form-phone-{{ $p->id }}" class="btn btn--primary btn--md">Зберегти</button>
            </div>
        </div>
    @endforeach

    {{-- ========= PRICE: create ========= --}}
    <div class="drawer-overlay" id="drawer-price-create-overlay" onclick="closeDrawer('drawer-price-create')"></div>
    <div class="drawer" id="drawer-price-create">
        <div class="drawer__header">
            <span class="drawer__title">Додати ціну</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-price-create')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('prices.store', $site) }}" id="form-price-create">
                @csrf
                @include('admin.sites._form-price', ['price' => null, 'visRuleOptions' => $visRuleOptions])
            </form>
        </div>
        <div class="drawer__footer">
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-price-create')">Скасувати</button>
            <button type="submit" form="form-price-create" class="btn btn--primary btn--md">Додати ціну</button>
        </div>
    </div>

    {{-- ========= PRICE: edit ========= --}}
    @foreach($site->prices as $p)
        <div class="drawer-overlay" id="drawer-price-{{ $p->id }}-overlay" onclick="closeDrawer('drawer-price-{{ $p->id }}')"></div>
        <div class="drawer" id="drawer-price-{{ $p->id }}">
            <div class="drawer__header">
                <span class="drawer__title">Редагувати ціну</span>
                <button class="icon-btn" onclick="closeDrawer('drawer-price-{{ $p->id }}')">✕</button>
            </div>
            <div class="drawer__body">
                <form method="POST" action="{{ route('prices.update', [$site, $p]) }}" id="form-price-{{ $p->id }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-price', ['price' => $p, 'visRuleOptions' => $visRuleOptions])
                </form>
            </div>
            <div class="drawer__footer">
                <form method="POST" action="{{ route('prices.destroy', [$site, $p]) }}" class="drawer__footer-left" onsubmit="return confirm('Видалити цю ціну?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Видалити</button>
                </form>
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-price-{{ $p->id }}')">Скасувати</button>
                <button type="submit" form="form-price-{{ $p->id }}" class="btn btn--primary btn--md">Зберегти</button>
            </div>
        </div>
    @endforeach

    {{-- ========= ADDRESS: create ========= --}}
    <div class="drawer-overlay" id="drawer-addr-create-overlay" onclick="closeDrawer('drawer-addr-create')"></div>
    <div class="drawer" id="drawer-addr-create">
        <div class="drawer__header">
            <span class="drawer__title">Додати адресу</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-addr-create')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('addresses.store', $site) }}" id="form-addr-create">
                @csrf
                @include('admin.sites._form-address', ['address' => null, 'countries' => $countries, 'defaultIso' => ($country !== 'all' ? $country : null), 'visRuleOptions' => $visRuleOptions])
            </form>
        </div>
        <div class="drawer__footer">
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-addr-create')">Скасувати</button>
            <button type="submit" form="form-addr-create" class="btn btn--primary btn--md">Додати адресу</button>
        </div>
    </div>

    {{-- ========= ADDRESS: edit ========= --}}
    @foreach($site->addresses as $a)
        <div class="drawer-overlay" id="drawer-addr-{{ $a->id }}-overlay" onclick="closeDrawer('drawer-addr-{{ $a->id }}')"></div>
        <div class="drawer" id="drawer-addr-{{ $a->id }}">
            <div class="drawer__header">
                <span class="drawer__title">Редагувати адресу</span>
                <button class="icon-btn" onclick="closeDrawer('drawer-addr-{{ $a->id }}')">✕</button>
            </div>
            <div class="drawer__body">
                <form method="POST" action="{{ route('addresses.update', [$site, $a]) }}" id="form-addr-{{ $a->id }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-address', ['address' => $a, 'countries' => $countries, 'visRuleOptions' => $visRuleOptions])
                </form>
            </div>
            <div class="drawer__footer">
                <form method="POST" action="{{ route('addresses.destroy', [$site, $a]) }}" class="drawer__footer-left" onsubmit="return confirm('Видалити цю адресу?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Видалити</button>
                </form>
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-addr-{{ $a->id }}')">Скасувати</button>
                <button type="submit" form="form-addr-{{ $a->id }}" class="btn btn--primary btn--md">Зберегти</button>
            </div>
        </div>
    @endforeach

    {{-- ========= SOCIAL: create ========= --}}
    <div class="drawer-overlay" id="drawer-soc-create-overlay" onclick="closeDrawer('drawer-soc-create')"></div>
    <div class="drawer" id="drawer-soc-create">
        <div class="drawer__header">
            <span class="drawer__title">Додати соцмережу</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-soc-create')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('socials.store', $site) }}" id="form-soc-create">
                @csrf
                @include('admin.sites._form-social', ['social' => null, 'visRuleOptions' => $visRuleOptions])
            </form>
        </div>
        <div class="drawer__footer">
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-soc-create')">Скасувати</button>
            <button type="submit" form="form-soc-create" class="btn btn--primary btn--md">Додати посилання</button>
        </div>
    </div>

    {{-- ========= ADD GEO ========= --}}
    @php
        $quickPickIsos  = ['UA', 'RU', 'BY', 'RO'];
        $availableQuick = array_values(array_filter($quickPickIsos, fn($iso) => !in_array($iso, $usedIso, true)));
    @endphp
    <div class="drawer-overlay" id="drawer-geo-add-overlay" onclick="closeDrawer('drawer-geo-add')"></div>
    <div class="drawer" id="drawer-geo-add">
        <form method="POST" action="{{ route('sites.geos.add', $site) }}" id="form-geo-add">
            @csrf
            <div class="drawer__header">
                <span class="drawer__title">Додати гео</span>
                <button class="icon-btn" type="button" onclick="closeDrawer('drawer-geo-add')">✕</button>
            </div>
            <div class="drawer__body">
                <p style="font-size:13px;color:var(--text-2);margin:0 0 16px;">
                    Оберіть країну — вона з'явиться як вкладка, де можна додавати телефони, адреси та інші дані.
                </p>
                <div style="display:grid;grid-template-columns:90px 1fr;gap:10px;align-items:end;">
                    <div class="field" style="margin:0;">
                        <label class="field__label" for="geo-pick">ISO код</label>
                        <input type="text" name="country_iso" id="geo-pick" class="field__input"
                               placeholder="UA" maxlength="2" required autocomplete="off"
                               oninput="this.value=this.value.toUpperCase();geoPickAutoName(this.value)"
                               style="font-family:var(--font-mono);font-weight:700;letter-spacing:.1em;text-align:center;">
                    </div>
                    <div class="field" style="margin:0;">
                        <label class="field__label" for="geo-name">Назва країни</label>
                        <input type="text" name="country_name" id="geo-name" class="field__input"
                               placeholder="Україна, Румунія…" maxlength="60" autocomplete="off">
                    </div>
                </div>

                @if(count($availableQuick) > 0)
                    <div style="margin-top:18px;">
                        <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:8px;">Швидкий вибір</div>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            @foreach($availableQuick as $iso)
                                @php $qName = $allIsoCountries[$iso] ?? $iso; @endphp
                                <button type="button"
                                        onclick="document.getElementById('geo-pick').value='{{ $iso }}';document.getElementById('geo-name').value='{{ $qName }}';"
                                        style="padding:5px 10px;background:var(--panel-2);border:1px solid var(--border);border-radius:99px;font-family:var(--font-mono);font-size:11px;font-weight:600;color:var(--text-2);cursor:pointer;">
                                    {{ $iso }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="drawer__footer">
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-geo-add')">Скасувати</button>
                <button type="submit" class="btn btn--primary btn--md">Додати гео</button>
            </div>
        </form>
    </div>

    {{-- ========= REMOVE GEO: confirmation drawers (one per active geo) ========= --}}
    @foreach($usedIso as $removeIso)
        <div class="drawer-overlay" id="drawer-geo-remove-{{ $removeIso }}-overlay" onclick="closeDrawer('drawer-geo-remove-{{ $removeIso }}')"></div>
        <div class="drawer" id="drawer-geo-remove-{{ $removeIso }}">
            <div class="drawer__header">
                <span class="drawer__title" style="color:var(--danger);">Видалити гео</span>
                <button class="icon-btn" type="button" onclick="closeDrawer('drawer-geo-remove-{{ $removeIso }}')">✕</button>
            </div>
            <div class="drawer__body">
                <p style="font-size:13px;color:var(--text-2);margin:0 0 16px;">
                    Ви збираєтесь видалити вкладку <strong style="font-family:var(--font-mono);">{{ $removeIso }}</strong>.
                    Всі записи даних, прив'язані до цього гео, залишаться в базі ��аних — зникне лише вкладка.
                </p>
                <div class="field">
                    <label class="field__label" for="geo-remove-confirm-{{ $removeIso }}">
                        Введіть <strong style="font-family:var(--font-mono);color:var(--danger);">{{ $removeIso }}</strong> для підтвердження
                    </label>
                    <input type="text" id="geo-remove-confirm-{{ $removeIso }}"
                           class="field__input" placeholder="{{ $removeIso }}"
                           autocomplete="off" maxlength="2"
                           oninput="this.value=this.value.toUpperCase();document.getElementById('btn-geo-remove-{{ $removeIso }}').disabled=this.value!=='{{ $removeIso }}';"
                           style="font-family:var(--font-mono);font-weight:700;font-size:18px;letter-spacing:.1em;text-align:center;">
                </div>
            </div>
            <div class="drawer__footer">
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-geo-remove-{{ $removeIso }}')">Скасувати</button>
                <form method="POST" action="{{ route('sites.geos.remove', [$site, $removeIso]) }}" style="margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" id="btn-geo-remove-{{ $removeIso }}" class="btn btn--danger btn--md" disabled>
                        Видалити {{ $removeIso }}
                    </button>
                </form>
            </div>
        </div>
    @endforeach

    {{-- ========= SOCIAL: edit ========= --}}
    @foreach($site->socials as $s)
        <div class="drawer-overlay" id="drawer-soc-{{ $s->id }}-overlay" onclick="closeDrawer('drawer-soc-{{ $s->id }}')"></div>
        <div class="drawer" id="drawer-soc-{{ $s->id }}">
            <div class="drawer__header">
                <span class="drawer__title">Редагувати соцмережу</span>
                <button class="icon-btn" onclick="closeDrawer('drawer-soc-{{ $s->id }}')">✕</button>
            </div>
            <div class="drawer__body">
                <form method="POST" action="{{ route('socials.update', [$site, $s]) }}" id="form-soc-{{ $s->id }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-social', ['social' => $s, 'visRuleOptions' => $visRuleOptions])
                </form>
            </div>
            <div class="drawer__footer">
                <form method="POST" action="{{ route('socials.destroy', [$site, $s]) }}" class="drawer__footer-left" onsubmit="return confirm('Видалити це посилання?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Видалити</button>
                </form>
                <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-soc-{{ $s->id }}')">Скасувати</button>
                <button type="submit" form="form-soc-{{ $s->id }}" class="btn btn--primary btn--md">Зберегти</button>
            </div>
        </div>
    @endforeach

@endif

@endsection

@push('scripts')
<script>
var isoNames = @json($allIsoCountries);
function geoPickAutoName(iso) {
    var nameInput = document.getElementById('geo-name');
    if (!nameInput || nameInput.value) return;
    if (isoNames[iso]) nameInput.value = isoNames[iso];
}

var dialCodeMap = {UA:380,RU:7,BY:375,RO:40,PL:48,DE:49,AT:43,BE:32,BG:359,
    CH:41,CZ:420,DK:45,EE:372,ES:34,FI:358,FR:33,GB:44,GE:995,GR:30,
    HR:385,HU:36,IE:353,IL:972,IT:39,KZ:7,LT:370,LU:352,LV:371,MD:373,
    ME:382,MK:389,MT:356,NL:31,NO:47,PT:351,RS:381,SE:46,SI:386,SK:421,
    TR:90,AE:971,SA:966,CN:86,IN:91,US:1,CA:1,AU:61,BR:55};

function openPhoneCreate(geoIso) {
    openDrawer('drawer-phone-create');
    var isoInput  = document.getElementById('ph-iso-hidden');
    var dialInput = document.getElementById('ph-dialcode-hidden');
    if (isoInput)  isoInput.value  = (geoIso && geoIso !== 'all') ? geoIso : '';
    if (dialInput) dialInput.value = (geoIso && dialCodeMap[geoIso]) ? dialCodeMap[geoIso] : '';
}

// Geo rules UI helpers
function geoRuleToggle(prefix, mode) {
    // Update label styles
    var group = document.getElementById(prefix + '-modes');
    if (!group) return;
    group.querySelectorAll('label').forEach(function(lbl) {
        var radio = lbl.querySelector('input[type=radio]');
        var isActive = radio && radio.value === mode;
        lbl.style.background    = isActive ? 'var(--accent)'   : 'var(--panel-2)';
        lbl.style.color         = isActive ? '#fff'            : 'var(--text-2)';
        lbl.style.borderColor   = isActive ? 'var(--accent)'   : 'var(--border)';
        lbl.style.fontWeight    = isActive ? '600'             : '400';
        if (radio) radio.checked = isActive;
    });
    // Show/hide chips
    var chips = document.getElementById(prefix + '-chips');
    if (chips) chips.style.display = (mode === 'include' || mode === 'exclude') ? '' : 'none';
}

function geoRuleChipToggle(prefix, iso, checkbox) {
    var lbl = document.getElementById(prefix + '-chip-' + iso);
    if (!lbl) return;
    lbl.style.background   = checkbox.checked ? 'var(--accent-2)' : 'var(--panel-2)';
    lbl.style.color        = checkbox.checked ? 'var(--accent-text)' : 'var(--text-2)';
    lbl.style.borderColor  = checkbox.checked ? 'var(--accent-2)' : 'var(--border)';
}

// Per-item rule editor (in drawer forms)
function ruleEditorToggle(prefix, mode) {
    var group = document.getElementById(prefix + '-modes');
    if (!group) return;
    group.querySelectorAll('label').forEach(function(lbl) {
        var radio = lbl.querySelector('input[type=radio]');
        var active = radio && radio.value === mode;
        lbl.style.background  = active ? 'var(--accent)'   : 'var(--panel-2)';
        lbl.style.color       = active ? '#fff'            : 'var(--text-2)';
        lbl.style.borderColor = active ? 'var(--accent)'   : 'var(--border)';
        lbl.style.fontWeight  = active ? '600'             : '400';
        if (radio) radio.checked = active;
    });
    var ctr = document.getElementById(prefix + '-countries');
    if (ctr) ctr.style.display = (mode === 'include' || mode === 'exclude') ? '' : 'none';
}

function ruleChipToggle(prefix, iso, checkbox) {
    var lbl = document.getElementById(prefix + '-chip-' + iso);
    if (!lbl) return;
    lbl.style.background  = checkbox.checked ? 'var(--accent-2)'   : 'var(--panel-2)';
    lbl.style.color       = checkbox.checked ? 'var(--accent-text)' : 'var(--text-2)';
    lbl.style.borderColor = checkbox.checked ? 'var(--accent-2)'   : 'var(--border)';
}

// ── Data tab inline CRM controls ──────────────────────────────
function dtToggleAdd(type) {
    var panel = document.getElementById('dt-add-' + type);
    var btn   = document.getElementById('dt-add-btn-' + type);
    if (!panel) return;
    var open = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : '';
    if (btn) btn.classList.toggle('is-open', !open);
}

function dtExpandItem(id) {
    var panel   = document.getElementById('dt-edit-' + id);
    var chevron = document.querySelector('#dt-expand-' + id + ' svg');
    if (!panel) return;
    var open = panel.style.display !== 'none';
    panel.style.display = open ? 'none' : '';
    if (chevron) chevron.style.transform = open ? '' : 'rotate(90deg)';
}

function dtGeoMode(prefix, mode) {
    ['all','include','exclude'].forEach(function(m) {
        var pill = document.getElementById('dtpill-' + prefix + '-' + m);
        if (!pill) return;
        var on = m === mode;
        pill.classList.toggle('is-on', on);
        var radio = pill.querySelector('input[type=radio]');
        if (radio) radio.checked = on;
    });
    var chips = document.getElementById('dtchips-' + prefix);
    if (chips) chips.style.display = (mode === 'include' || mode === 'exclude') ? 'flex' : 'none';
}

function dtGeoChip(prefix, iso, el) {
    var chip = document.getElementById('dtchip-' + prefix + '-' + iso);
    if (chip) chip.classList.toggle('is-on', el.checked);
}

// ── Auto-open dt-edit panel from URL hash (e.g. #dt-edit-phone-123) ─────
window.addEventListener('DOMContentLoaded', function() {
    var hash = window.location.hash;
    if (hash && hash.indexOf('dt-edit-') !== -1) {
        var id = hash.replace('#dt-edit-', '');
        var panel = document.getElementById('dt-edit-' + id);
        if (panel) {
            panel.style.display = '';
            var chevron = document.querySelector('#dt-expand-' + id + ' svg');
            if (chevron) chevron.style.transform = 'rotate(90deg)';
            setTimeout(function() { panel.scrollIntoView({behavior: 'smooth', block: 'center'}); }, 80);
        }
    }
});

// ── Visitor preview tab switcher (Overview tab) ────────────────
function showVisitorPanel(iso) {
    document.querySelectorAll('[id^="vis-panel-"]').forEach(function(el) { el.style.display = 'none'; });
    document.querySelectorAll('[id^="vis-tab-"]').forEach(function(btn) {
        btn.className = btn.className.replace('btn--primary', 'btn--ghost');
    });
    var panel = document.getElementById('vis-panel-' + iso);
    if (panel) panel.style.display = '';
    var tab = document.getElementById('vis-tab-' + iso);
    if (tab) tab.className = tab.className.replace('btn--ghost', 'btn--primary');
}
</script>
@endpush
