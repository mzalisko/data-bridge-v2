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
    // Priority: item geo_mode first, then site geo_rules for item's data-tab (country_iso).
    // 'all' = visible to all (unless blocked by geo_rules).
    // 'include' = only listed visitors; 'exclude' = all except listed.
    $geoVis = function ($geoMode, $geoCountries, $visitorIso, $itemCountryIso = null) use ($geoRules): bool {
        $mode   = $geoMode ?? 'all';
        $ctries = (array) ($geoCountries ?? []);
        $itemOk = match($mode) {
            'include' => in_array($visitorIso, $ctries),
            'exclude' => !in_array($visitorIso, $ctries),
            default   => true,
        };
        if (!$itemOk) return false;
        // Site-level geo_rules: which visitors can see the data tab this item belongs to.
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

    // ISO options available for rule editor chips (active geos of this site).
    $visRuleOptions = $usedIso;

    // Each tab shows only records tagged to THAT tab (country_iso === tab ISO).
    // "All geos" shows everything. Records with no country_iso appear everywhere.
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
        'signal'    => ['c' => '#3a76f0', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M8 12a4 4 0 0 1 8 0"/></svg>'],
        'discord'   => ['c' => '#5865f2', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M20.3 4.4A19.7 19.7 0 0 0 15.5 3c-.2.4-.5.9-.7 1.3a18.3 18.3 0 0 0-5.6 0A12.7 12.7 0 0 0 8.5 3a19.7 19.7 0 0 0-4.8 1.4C.5 9.3-.3 14 .1 18.7A20 20 0 0 0 6 21.3c.5-.7 1-1.4 1.4-2.2a13 13 0 0 1-2.1-1l.5-.4A14.2 14.2 0 0 0 12 19c2.1 0 4.2-.5 6.2-1.3l.5.4a13 13 0 0 1-2.1 1 12.5 12.5 0 0 0 1.4 2.2A19.9 19.9 0 0 0 23.9 18.7c.4-5.4-1-10-3.6-14.3zM8 15.5a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm8 0a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/></svg>'],
        'skype'     => ['c' => '#00aff0', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M22 12a10 10 0 1 0-10 10c.8 0 1.6-.1 2.4-.2a5.5 5.5 0 0 0 7.4-7.4c.1-.8.2-1.6.2-2.4zm-7.7 4.5c-1.8.9-5 .7-5.9-1.5-.2-.5 0-1 .4-1.2.5-.2 1 0 1.2.5.5 1.3 3 1.3 3.5.3.3-.5 0-1.2-1.6-1.6-2.7-.6-4.3-1.5-4.3-3.3 0-1.9 1.8-3 3.6-3 1.6 0 3.2.6 4 1.8.3.4.2 1-.2 1.3-.4.3-1 .2-1.3-.2-.5-.8-1.5-1.2-2.5-1.2-.9 0-1.9.4-1.9 1.1 0 1.5 4.8 1.1 5.9 3.3.7 1.5.2 3-1 3.7z"/></svg>'],
        'wechat'    => ['c' => '#07c160', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 11c0-3.3 3.1-6 7-6 3.9 0 7 2.7 7 6s-3.1 6-7 6c-.5 0-1-.1-1.5-.1L11 19l1-3A5.9 5.9 0 0 1 9 11z"/><path d="M2 8.5C2 5.5 4.7 3 8 3a6 6 0 0 1 3.5 1"/></svg>'],
        'line'      => ['c' => '#00b900', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12 2C6.5 2 2 5.9 2 10.6c0 3.5 2.3 6.5 5.7 8.2l-.7 2.6 3-1.8c.6.1 1.3.2 2 .2 5.5 0 10-3.9 10-8.6S17.5 2 12 2zm-2 12H8V8h2v6zm5 0h-2l-2-3v3h-1V8h2l2 3V8h1v6z"/></svg>'],
        'youtube'   => ['c' => '#ff0000', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8C22 15.2 22 12 22 12s0-3.2-.4-4.8zM10 15V9l5 3-5 3z"/></svg>'],
        'tiktok'    => ['c' => '#010101', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M19.6 8.5A5.7 5.7 0 0 1 16 7.4V16a6 6 0 1 1-5.1-5.9v3.2a2.8 2.8 0 1 0 2 2.7V3h3.2a5.7 5.7 0 0 0 3.5 5.5z"/></svg>'],
        'pinterest' => ['c' => '#e60023', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12 2a10 10 0 0 0-4 19.2c0-.8.1-2 .4-2.9l1.5-6.2s-.4-.8-.4-1.9c0-1.8 1-3.1 2.3-3.1 1.1 0 1.6.8 1.6 1.8 0 1.1-.7 2.8-1.1 4.3-.3 1.3.6 2.3 1.9 2.3 2.2 0 3.7-2.9 3.7-6.3 0-2.6-1.8-4.5-4.5-4.5-3.2 0-5 2.4-5 4.8 0 .9.4 1.9.8 2.4.1.1.1.3.1.4l-.3 1.3c-.1.3-.3.4-.6.2-1.6-.8-2.6-3.2-2.6-5.1 0-4.2 3-8 8.8-8 4.6 0 8.2 3.3 8.2 7.7 0 4.6-2.9 8.3-7 8.3-1.4 0-2.7-.7-3.1-1.5l-.8 3.1c-.3 1.1-1.1 2.6-1.7 3.5A10 10 0 1 0 12 2z"/></svg>'],
        'reddit'    => ['c' => '#ff4500', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><circle cx="12" cy="12" r="10"/><path fill="#fff" d="M18.5 12c0-.8-.7-1.5-1.5-1.5-.4 0-.7.1-1 .4-1-.7-2.3-1.1-3.8-1.2l.6-3 2.1.4c0 .6.5 1.1 1.1 1.1.6 0 1.1-.5 1.1-1.1s-.5-1.1-1.1-1.1c-.4 0-.8.3-1 .7l-2.4-.5c-.2 0-.3.1-.3.2l-.7 3.3c-1.5.1-2.8.5-3.8 1.2-.3-.2-.7-.4-1.1-.4-.8 0-1.5.7-1.5 1.5 0 .6.4 1.2.9 1.4 0 .2-.1.4-.1.6 0 2.1 2.4 3.7 5.5 3.7s5.5-1.6 5.5-3.7c0-.2 0-.4-.1-.6.5-.2.9-.7.9-1.4zM9 13c0-.6.4-1 1-1s1 .4 1 1-.4 1-1 1-1-.4-1-1zm5.5 2.7c-.7.7-1.8 1-3 1s-2.3-.3-3-1c-.2-.2-.2-.4 0-.5.2-.2.4-.2.5 0 .5.5 1.4.8 2.4.8s2-.3 2.4-.8c.2-.2.4-.2.5 0 .3.1.3.4.2.5zm-.5-1.7c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z"/></svg>'],
        'vk'        => ['c' => '#4680c2', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm5.5 13.5H16c-.3 0-.5-.2-1.4-1.1-.7-.8-1-1.2-1.3-1.2-.2 0-.2.1-.2.5v1.1c0 .4-.1.7-.9.7-1.2 0-2.6-.7-3.6-2.1C7.3 11.9 6.6 10 6.6 9.9c0-.2.1-.3.3-.3h1.5c.2 0 .4.2.5.4.8 2 2 3.6 2.5 3.6.1 0 .2-.1.2-.5v-1.8c0-.9-.5-.9-.5-1.2 0-.1.1-.3.3-.3h2.4c.2 0 .3.1.3.4v2.4c0 .2.1.3.3.3.2 0 .4-.1.7-.5 1-1.3 1.7-3.2 1.7-3.2.1-.2.2-.4.5-.4h1.5c.4 0 .5.2.4.5-.3 1.3-1.8 3.3-1.8 3.3-.1.2-.2.3 0 .5.2.2.7.6 1.1 1 .4.4.7.8.8 1 .1.4-.1.5-.3.5z"/></svg>'],
        'twitch'    => ['c' => '#9146ff', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M4.3 2L3 5.6V20h5v2h3l2-2h3l4-4V2H4.3zM19 13.5l-3 3h-4l-2 2v-2H6V4h13v9.5zm-3-6.5v5h-2V7h2zm-5 0v5H9V7h2z"/></svg>'],
        'threads'   => ['c' => '#000000', 'svg' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M12.2 2c-5.5 0-8.2 3-8.2 7 0 2.4 1 4 3 5-1.5.8-2.5 2.2-2.5 4 0 3.2 2.7 4 5.5 4 3 0 5.5-1.2 5.5-4 0-1.6-.8-2.8-2-3.5 1.5-1 2-2.8 2-4.5 0-4-2.5-8-3.3-8zm0 2c1.2 2 2.3 4.8 2.3 6 0 1.5-.4 2.5-1 3.2-.7-.4-1.5-.7-2.5-.8 1 .2 1.8 1 1.8 2.1 0 1.8-1.5 2.5-3.8 2.5-2 0-3.5-.7-3.5-2.5 0-1.5 1-2.5 2.5-2.5.5 0 1 0 1.5.2-.3-.7-.7-1.5-1.2-2.2-2 .3-3.3 1.2-3.3 2.5v.5C5.5 13 5 11.8 5 11c0-3.3 2-5 7.2-5V4z"/></svg>'],
    ];
@endphp

<div class="page-stack">

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

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
            @if($site->push_url && $site->push_key)
            <form id="form-head-sync" method="POST" action="{{ route('sites.sync', $site) }}" style="display:none;">@csrf</form>
            @endif
            <button id="fav-btn" onclick="toggleFavorite()" class="btn btn--secondary btn--md" title="{{ $isFavorite ? 'Прибрати з улюблених' : 'Додати до улюблених' }}"
                    style="color:{{ $isFavorite ? '#f6ad55' : 'var(--text-3)' }};">
                <svg id="fav-icon" viewBox="0 0 24 24" width="14" height="14" fill="{{ $isFavorite ? '#f6ad55' : 'none' }}" stroke="{{ $isFavorite ? '#f6ad55' : 'currentColor' }}" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </button>
            @if($site->url)
                <a href="{{ $site->url }}" target="_blank" class="btn btn--secondary btn--md">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 4h6v6"/><path d="M20 4 10 14"/>
                        <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/>
                    </svg>
                    Відкрити
                </a>
            @endif
            @if($site->push_url && $site->push_key)
            <button type="submit" form="form-head-sync" class="btn btn--secondary btn--md">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                Синхронізувати
            </button>
            @endif
            <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-site-edit')">Оновити дані</button>
        </div>
    </div>

    {{-- ========= PRESENCE BANNER ========= --}}
    <div id="presence-banner" style="background:rgba(225,29,72,.08);border:1px solid var(--danger);border-radius:var(--radius-item);padding:10px 14px;align-items:center;gap:10px;font-size:13px;color:var(--danger);margin-bottom:4px;flex-wrap:nowrap;white-space:nowrap;display:{{ count($presenceOthers) > 0 ? 'flex' : 'none' }};">
        <svg style="flex-shrink:0;" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span id="presence-text" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            @if(count($presenceOthers) > 0)
                {{ collect($presenceOthers)->pluck('name')->join(', ') }} зараз тут — редагування може конфліктувати
            @endif
        </span>
    </div>

    {{-- ========= SITE INFO BAR ========= --}}
    @php $totalRecords = $site->phones->count() + $site->prices->count() + $site->addresses->count() + $site->socials->count(); @endphp
    <div class="card site-info-bar">
        <div class="site-info-bar__item">
            <span class="site-info-bar__label">Статус</span>
            <div><x-status-pill :status="$statusName"/></div>
        </div>
        <div class="site-info-bar__sep"></div>
        <div class="site-info-bar__item">
            <span class="site-info-bar__label">Група</span>
            @if($site->siteGroup)
                <span class="group-chip" style="font-size:13px;">
                    <span class="group-chip__dot" style="background:{{ $site->siteGroup->color ?? '#71717a' }}"></span>
                    {{ $site->siteGroup->name }}
                </span>
            @else
                <span class="site-info-bar__val" style="color:var(--text-3);">—</span>
            @endif
        </div>
        <div class="site-info-bar__sep"></div>
        <div class="site-info-bar__item">
            <span class="site-info-bar__label">Записів</span>
            <span class="site-info-bar__val">{{ $totalRecords }}</span>
        </div>
        <div class="site-info-bar__sep"></div>
        <div class="site-info-bar__item">
            <span class="site-info-bar__label">Додано</span>
            <span class="site-info-bar__val">{{ $site->created_at->format('d M Y') }}</span>
        </div>
        <div class="site-info-bar__sep"></div>
        <div class="site-info-bar__item">
            <span class="site-info-bar__label">Остання синхр.</span>
            <span class="site-info-bar__val" style="color:var(--text-2);">{{ $syncWhen }}</span>
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
                ['Телефони',  $site->phones,    fn($p) => $p->number],
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
                    <button onclick="showVisitorPanel('_raw')" id="vis-tab-_raw"
                            class="btn btn--sm btn--ghost"
                            style="font-weight:700;gap:5px;">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="5" rx="1"/><rect x="3" y="10" width="18" height="5" rx="1"/><rect x="3" y="17" width="18" height="4" rx="1"/></svg>
                        Всі дані
                    </button>
                    <button onclick="showVisitorPanel('_all')" id="vis-tab-_all"
                            class="btn btn--sm btn--ghost"
                            style="font-weight:700;gap:5px;">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                        Весь світ
                    </button>
                    <span style="color:var(--border);font-size:16px;margin:0 2px;">|</span>
                    @foreach($allVisitorIsos as $visIso)
                        <button onclick="showVisitorPanel('{{ $visIso }}')" id="vis-tab-{{ $visIso }}"
                                class="btn btn--sm btn--ghost"
                                style="font-family:var(--font-mono);font-weight:700;">{{ $visIso }}</button>
                    @endforeach
                </div>

                {{-- "Весь світ" panel: items visible to everyone (geo_mode=all) --}}
                @php
                    // Весь світ = all або exclude (відвідувач без конкретної країни не потрапляє до жодного виключення)
                    $worldVis = fn($item) => ($item->is_visible ?? true) && in_array($item->geo_mode ?? 'all', ['all', 'exclude']);
                    $wPhones  = $site->phones->filter($worldVis);
                    $wPrices  = $site->prices->filter($worldVis);
                    $wAddrs   = $site->addresses->filter($worldVis);
                    $wSocials = $site->socials->filter($worldVis);
                    $wTotal   = $wPhones->count() + $wPrices->count() + $wAddrs->count() + $wSocials->count();
                    $totalAll = $site->phones->count() + $site->prices->count() + $site->addresses->count() + $site->socials->count();
                @endphp
                <div id="vis-panel-_all" style="display:none;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--border-2);">
                        {{-- LEFT: що бачать усі відвідувачі --}}
                        <div style="background:var(--panel);padding:20px;">
                            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:6px;">
                                <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                                Що бачать усі відвідувачі
                            </div>
                            @if($wTotal === 0)
                                <div style="text-align:center;padding:20px;color:var(--text-3);font-size:12px;">Усі записи мають гео-обмеження</div>
                            @else
                            @if($wPhones->count())
                                <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:6px;">Телефони</div>
                                @foreach($wPhones as $p)
                                    <div style="background:var(--panel-2);border-radius:var(--radius-item);padding:8px 10px;margin-bottom:5px;">
                                        <div style="font-family:var(--font-mono);font-size:13px;font-weight:600;color:var(--text);">{{ $p->number }}</div>
                                        @if($p->label)<div style="font-size:11px;color:var(--text-3);margin-top:1px;">{{ $p->label }}</div>@endif
                                    </div>
                                @endforeach
                            @endif
                            @if($wPrices->count())
                                <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin:14px 0 6px;">Ціни</div>
                                @foreach($wPrices as $p)
                                    <div style="display:flex;justify-content:space-between;align-items:center;background:var(--panel-2);border-radius:var(--radius-item);padding:7px 10px;margin-bottom:5px;">
                                        <span style="font-size:12px;color:var(--text-2);">{{ $p->label }}</span>
                                        <span style="font-family:var(--font-mono);font-weight:700;font-size:13px;color:#34d399;">{{ $p->amount }} {{ $p->currency }}</span>
                                    </div>
                                @endforeach
                            @endif
                            @if($wAddrs->count())
                                <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin:14px 0 6px;">Адреси</div>
                                @foreach($wAddrs as $a)
                                    <div style="font-size:12px;color:var(--text-2);background:var(--panel-2);border-radius:var(--radius-item);padding:7px 10px;margin-bottom:5px;">
                                        {{ trim(($a->city ?? '').' '.($a->street ?? '')) ?: '—' }}
                                    </div>
                                @endforeach
                            @endif
                            @php
                                $wSocNets  = $wSocials->filter(fn($s) => !in_array(strtolower($s->platform ?? ''), ['telegram','whatsapp','viber']));
                                $wMsgngers = $wSocials->filter(fn($s) =>  in_array(strtolower($s->platform ?? ''), ['telegram','whatsapp','viber']));
                            @endphp
                            @if($wSocNets->count())
                                <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin:14px 0 6px;">Соц.мережі</div>
                                <div style="display:flex;flex-direction:column;gap:5px;">
                                @foreach($wSocNets as $s)
                                    @php $sk = strtolower($s->platform ?? ''); $sic = $socialIcon[$sk] ?? ['c'=>'var(--text-3)','svg'=>'']; @endphp
                                    <div style="display:flex;align-items:center;gap:8px;background:var(--panel-2);border-radius:var(--radius-item);padding:7px 10px;">
                                        <span style="color:{{ $sic['c'] }};display:inline-flex;flex-shrink:0;">{!! $sic['svg'] !!}</span>
                                        <span style="font-size:12px;color:var(--text-2);">{{ ucfirst($s->platform) }}: {{ $s->handle }}</span>
                                    </div>
                                @endforeach
                                </div>
                            @endif
                            @if($wMsgngers->count())
                                <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin:14px 0 6px;">Месенджери</div>
                                <div style="display:flex;flex-direction:column;gap:5px;">
                                @foreach($wMsgngers as $s)
                                    @php $sk = strtolower($s->platform ?? ''); $sic = $socialIcon[$sk] ?? ['c'=>'var(--text-3)','svg'=>'']; @endphp
                                    <div style="display:flex;align-items:center;gap:8px;background:var(--panel-2);border-radius:var(--radius-item);padding:7px 10px;">
                                        <span style="color:{{ $sic['c'] }};display:inline-flex;flex-shrink:0;">{!! $sic['svg'] !!}</span>
                                        <span style="font-size:12px;color:var(--text-2);">{{ ucfirst($s->platform) }}: {{ $s->handle }}</span>
                                    </div>
                                @endforeach
                                </div>
                            @endif
                            @endif
                        </div>
                        {{-- RIGHT: всі поля з гео-режимом --}}
                        @php
                            $wTdS = 'padding:4px 8px;font-size:11px;border-bottom:1px solid var(--border-2);';
                            $wVKey = \App\Models\CustomPlatform::messengerSlugs();
                            $wAllPhs  = $site->phones->sortBy('sort_order');
                            $wAllPrs  = $site->prices->sortBy('sort_order');
                            $wAllAds  = $site->addresses->sortBy('sort_order');
                            $wAllMsgr = $site->socials->filter(fn($s)=>in_array(strtolower($s->platform??''),$wVKey))->sortBy('sort_order');
                            $wAllSocN = $site->socials->filter(fn($s)=>!in_array(strtolower($s->platform??''),$wVKey))->sortBy('sort_order');
                            $wIsVis = fn($item) => ($item->is_visible ?? true) && in_array($item->geo_mode ?? 'all', ['all', 'exclude']);
                        @endphp
                        <div style="background:var(--panel);overflow-y:auto;">
                            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;padding:10px 12px 8px;display:flex;justify-content:space-between;border-bottom:1px solid var(--border-2);">
                                <span>Всі поля</span>
                                <span style="font-family:var(--font-mono);color:{{ $wTotal===$totalAll?'#34d399':($wTotal>0?'var(--warning)':'#f87171') }};">{{ $wTotal }}/{{ $totalAll }}</span>
                            </div>
                            @if($wAllPhs->count())
                            <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Телефони</div>
                            <table style="width:100%;border-collapse:collapse;">
                                <tbody>
                                @foreach($wAllPhs as $p)
                                @php $pV = $wIsVis($p); @endphp
                                <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                    <td style="{{ $wTdS }}font-family:var(--font-mono);font-size:11px;font-weight:600;color:var(--text);">{{ $p->number }}</td>
                                    <td style="{{ $wTdS }}color:var(--text-3);font-size:10px;">{{ $p->label ?: '' }}</td>
                                    <td style="{{ $wTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @endif
                            @if($wAllPrs->count())
                            <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Ціни</div>
                            <table style="width:100%;border-collapse:collapse;">
                                <tbody>
                                @foreach($wAllPrs as $p)
                                @php $pV = $wIsVis($p); @endphp
                                <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                    <td style="{{ $wTdS }}font-family:var(--font-mono);font-weight:700;color:#34d399;">{{ number_format($p->amount,2) }} {{ $p->currency }}</td>
                                    <td style="{{ $wTdS }}color:var(--text-3);font-size:10px;">{{ $p->label ?: '' }}</td>
                                    <td style="{{ $wTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @endif
                            @if($wAllAds->count())
                            <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Адреси</div>
                            <table style="width:100%;border-collapse:collapse;">
                                <tbody>
                                @foreach($wAllAds as $a)
                                @php $pV = $wIsVis($a); @endphp
                                <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                    <td style="{{ $wTdS }}color:var(--text-2);">{{ trim(($a->city??'').' '.($a->street??'')) ?: '—' }}</td>
                                    <td style="{{ $wTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @endif
                            @if($wAllMsgr->count())
                            <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Месенджери</div>
                            <table style="width:100%;border-collapse:collapse;">
                                <tbody>
                                @foreach($wAllMsgr as $s)
                                @php $pV=$wIsVis($s);$sk=strtolower($s->platform??'');$sic=$socialIcon[$sk]??['c'=>'var(--text-3)','svg'=>'']; @endphp
                                <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                    <td style="{{ $wTdS }}"><span style="display:inline-flex;align-items:center;gap:4px;"><span style="color:{{ $sic['c'] }};display:inline-flex;">{!! $sic['svg'] !!}</span><span style="font-size:10px;color:var(--text-3);">{{ ucfirst($s->platform) }}</span></span></td>
                                    <td style="{{ $wTdS }}color:var(--text-2);font-size:10px;">{{ $s->handle ?: '—' }}</td>
                                    <td style="{{ $wTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @endif
                            @if($wAllSocN->count())
                            <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Соцмережі</div>
                            <table style="width:100%;border-collapse:collapse;">
                                <tbody>
                                @foreach($wAllSocN as $s)
                                @php $pV=$wIsVis($s);$sk=strtolower($s->platform??'');$sic=$socialIcon[$sk]??['c'=>'var(--text-3)','svg'=>'']; @endphp
                                <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                    <td style="{{ $wTdS }}"><span style="display:inline-flex;align-items:center;gap:4px;"><span style="color:{{ $sic['c'] }};display:inline-flex;">{!! $sic['svg'] !!}</span><span style="font-size:10px;color:var(--text-3);">{{ ucfirst($s->platform) }}</span></span></td>
                                    <td style="{{ $wTdS }}color:var(--text-2);font-size:10px;">{{ $s->handle ?: '—' }}</td>
                                    <td style="{{ $wTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- "Всі дані" panel: every record, no geo filter, full site snapshot --}}
                <div id="vis-panel-_raw" style="display:none;overflow-x:auto;">
                    @php
                        $rMsgrK = \App\Models\CustomPlatform::messengerSlugs();
                        $rPhs   = $site->phones->sortBy('sort_order');
                        $rPrs   = $site->prices->sortBy('sort_order');
                        $rAds   = $site->addresses->sortBy('sort_order');
                        $rSocN  = $site->socials->filter(fn($s)=>!in_array(strtolower($s->platform??''),$rMsgrK))->sortBy('sort_order');
                        $rMsgr  = $site->socials->filter(fn($s)=> in_array(strtolower($s->platform??''),$rMsgrK))->sortBy('sort_order');
                        $rTot   = $rPhs->count()+$rPrs->count()+$rAds->count()+$site->socials->count();
                        $thR = 'padding:5px 8px;text-align:left;font-size:10px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--border-2);white-space:nowrap;overflow:hidden;';
                        $tdR = 'padding:5px 8px;font-size:12px;border-bottom:1px solid var(--border-2);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;';
                    @endphp
                    <div style="background:var(--panel);">
                        <div style="font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;padding:10px 14px 8px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border-2);">
                            <span>Повна картина — всі записи</span>
                            <span style="font-family:var(--font-mono);color:var(--text-2);">{{ $rTot }}</span>
                        </div>
                        @if($rTot === 0)<div style="text-align:center;padding:28px;color:var(--text-3);font-size:12px;">Даних ще немає</div>@endif

                        @if($rPhs->count())
                        <div style="padding:8px 14px 4px;font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Телефони</div>
                        <table style="width:auto;border-collapse:collapse;table-layout:fixed;">
                            <colgroup><col style="width:70px"><col style="width:190px"><col style="width:120px"><col style="width:100px"><col style="width:40px"></colgroup>
                            <thead><tr>
                                <th style="{{ $thR }}">Статус</th>
                                <th style="{{ $thR }}">Номер</th>
                                <th style="{{ $thR }}">Мітка</th>
                                <th style="{{ $thR }}">Гео</th>
                                <th style="{{ $thR }}">Видно</th>
                            </tr></thead>
                            <tbody>
                            @foreach($rPhs as $p)
                            @php
                                $pGeo = $p->geo_mode ?? 'all';
                                $pGeoTxt = $pGeo==='all' ? 'Всім' : (['include'=>'Тільки','exclude'=>'Крім'][$pGeo]??$pGeo);
                                if($pGeo!=='all'&&$p->geo_countries) $pGeoTxt.=' '.implode(',', (array)$p->geo_countries);
                            @endphp
                            <tr style="{{ !($p->is_visible??true)?'opacity:.45;':'' }}">
                                <td style="{{ $tdR }}">
                                    @if($p->is_blocked)<span style="font-size:10px;padding:1px 5px;border-radius:3px;background:rgba(245,101,101,.12);color:var(--danger);font-weight:600;">Блок</span>
                                    @elseif($p->standby_for_id)<span style="font-size:10px;padding:1px 5px;border-radius:3px;background:rgba(99,179,237,.12);color:#63b3ed;font-weight:600;">Резерв</span>
                                    @else<span style="font-size:10px;padding:1px 5px;border-radius:3px;background:rgba(72,187,120,.1);color:#48bb78;font-weight:600;">Осн.</span>@endif
                                </td>
                                <td style="{{ $tdR }}font-family:var(--font-mono);font-weight:600;color:var(--text);{{ $p->is_blocked?'text-decoration:line-through;':'' }}">{{ $p->number }}</td>
                                <td style="{{ $tdR }}color:var(--text-3);">{{ $p->label ?: '—' }}</td>
                                <td style="{{ $tdR }}color:var(--text-3);font-size:11px;">{{ $pGeoTxt }}</td>
                                <td style="{{ $tdR }}text-align:center;">@if($p->is_visible??true)<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="var(--text-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif

                        @if($rPrs->count())
                        <div style="padding:10px 14px 4px;font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Ціни</div>
                        <table style="width:auto;border-collapse:collapse;table-layout:fixed;">
                            <colgroup><col style="width:82px"><col style="width:62px"><col style="width:180px"><col style="width:100px"></colgroup>
                            <thead><tr>
                                <th style="{{ $thR }}">Сума</th>
                                <th style="{{ $thR }}">Валюта</th>
                                <th style="{{ $thR }}">Мітка</th>
                                <th style="{{ $thR }}">Гео</th>
                            </tr></thead>
                            <tbody>
                            @foreach($rPrs as $p)
                            @php $pgTxt = ($p->geo_mode??'all')==='all'?'Всім':(['include'=>'Тільки','exclude'=>'Крім'][$p->geo_mode]??''); @endphp
                            <tr>
                                <td style="{{ $tdR }}font-family:var(--font-mono);font-weight:700;color:#34d399;">{{ number_format($p->amount,2) }}</td>
                                <td style="{{ $tdR }}font-family:var(--font-mono);color:var(--text-3);">{{ $p->currency }}</td>
                                <td style="{{ $tdR }}color:var(--text-2);">{{ $p->label ?: '—' }}</td>
                                <td style="{{ $tdR }}color:var(--text-3);font-size:11px;">{{ $pgTxt }}</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif

                        @if($rAds->count())
                        <div style="padding:10px 14px 4px;font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Адреси</div>
                        <table style="width:auto;border-collapse:collapse;table-layout:fixed;">
                            <colgroup><col style="width:110px"><col style="width:46px"><col style="width:180px"><col style="width:100px"><col style="width:40px"></colgroup>
                            <thead><tr>
                                <th style="{{ $thR }}">Місто</th>
                                <th style="{{ $thR }}">ISO</th>
                                <th style="{{ $thR }}">Вулиця</th>
                                <th style="{{ $thR }}">Мітка</th>
                                <th style="{{ $thR }}">Видно</th>
                            </tr></thead>
                            <tbody>
                            @foreach($rAds as $a)
                            <tr style="{{ !($a->is_visible??true)?'opacity:.45;':'' }}">
                                <td style="{{ $tdR }}font-weight:600;color:var(--text);">{{ $a->city ?: '—' }}</td>
                                <td style="{{ $tdR }}font-family:var(--font-mono);color:var(--text-3);">{{ $a->country_iso ?: '—' }}</td>
                                <td style="{{ $tdR }}color:var(--text-3);">{{ $a->street ?: '—' }}</td>
                                <td style="{{ $tdR }}color:var(--text-3);">{{ $a->label ?: '—' }}</td>
                                <td style="{{ $tdR }}text-align:center;">@if($a->is_visible??true)<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="var(--text-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif

                        @if($rMsgr->count())
                        <div style="padding:10px 14px 4px;font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Месенджери</div>
                        <table style="width:auto;border-collapse:collapse;table-layout:fixed;">
                            <colgroup><col style="width:120px"><col style="width:200px"><col style="width:100px"><col style="width:40px"></colgroup>
                            <thead><tr>
                                <th style="{{ $thR }}">Платф.</th>
                                <th style="{{ $thR }}">Handle</th>
                                <th style="{{ $thR }}">Гео</th>
                                <th style="{{ $thR }}">Видно</th>
                            </tr></thead>
                            <tbody>
                            @foreach($rMsgr as $s)
                            @php $sk=strtolower($s->platform??'');$sic=$socialIcon[$sk]??['c'=>'var(--text-3)','svg'=>''];$sgTxt=($s->geo_mode??'all')==='all'?'Всім':(['include'=>'Тільки','exclude'=>'Крім'][$s->geo_mode]??'');if(($s->geo_mode??'all')!=='all'&&$s->geo_countries)$sgTxt.=' '.implode(',', (array)$s->geo_countries); @endphp
                            <tr style="{{ !($s->is_visible??true)?'opacity:.45;':'' }}">
                                <td style="{{ $tdR }}"><span style="display:inline-flex;align-items:center;gap:5px;"><span style="color:{{ $sic['c'] }};display:inline-flex;flex-shrink:0;">{!! $sic['svg'] !!}</span><span style="font-size:11px;color:var(--text-3);">{{ ucfirst($s->platform) }}</span></span></td>
                                <td style="{{ $tdR }}color:var(--text-2);">{{ $s->handle ?: '—' }}</td>
                                <td style="{{ $tdR }}color:var(--text-3);font-size:11px;">{{ $sgTxt }}</td>
                                <td style="{{ $tdR }}text-align:center;">@if($s->is_visible??true)<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="var(--text-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif

                        @if($rSocN->count())
                        <div style="padding:10px 14px 4px;font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Соцмережі</div>
                        <table style="width:auto;border-collapse:collapse;table-layout:fixed;">
                            <colgroup><col style="width:120px"><col style="width:200px"><col style="width:100px"><col style="width:40px"></colgroup>
                            <thead><tr>
                                <th style="{{ $thR }}">Платф.</th>
                                <th style="{{ $thR }}">Handle</th>
                                <th style="{{ $thR }}">Гео</th>
                                <th style="{{ $thR }}">Видно</th>
                            </tr></thead>
                            <tbody>
                            @foreach($rSocN as $s)
                            @php $sk=strtolower($s->platform??'');$sic=$socialIcon[$sk]??['c'=>'var(--text-3)','svg'=>''];$sgTxt=($s->geo_mode??'all')==='all'?'Всім':(['include'=>'Тільки','exclude'=>'Крім'][$s->geo_mode]??'');if(($s->geo_mode??'all')!=='all'&&$s->geo_countries)$sgTxt.=' '.implode(',', (array)$s->geo_countries); @endphp
                            <tr style="{{ !($s->is_visible??true)?'opacity:.45;':'' }}">
                                <td style="{{ $tdR }}"><span style="display:inline-flex;align-items:center;gap:5px;"><span style="color:{{ $sic['c'] }};display:inline-flex;flex-shrink:0;">{!! $sic['svg'] !!}</span><span style="font-size:11px;color:var(--text-3);">{{ ucfirst($s->platform) }}</span></span></td>
                                <td style="{{ $tdR }}color:var(--text-2);">{{ $s->handle ?: '—' }}</td>
                                <td style="{{ $tdR }}color:var(--text-3);font-size:11px;">{{ $sgTxt }}</td>
                                <td style="{{ $tdR }}text-align:center;">@if($s->is_visible??true)<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="var(--text-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>

                {{-- Per-ISO panel: visitor preview (LEFT) + matrix (RIGHT) --}}
                @foreach($allVisitorIsos as $visIso)
                    @php
                        $vPhones   = $site->phones->filter(fn($p)  => ($p->is_visible ?? true) && $geoVis($p->geo_mode, $p->geo_countries, $visIso, $p->country_iso));
                        $vPrices   = $site->prices->filter(fn($p)  => ($p->is_visible ?? true) && $geoVis($p->geo_mode, $p->geo_countries, $visIso, $p->country_iso));
                        $vAddrs    = $site->addresses->filter(fn($a) => ($a->is_visible ?? true) && $geoVis($a->geo_mode, $a->geo_countries, $visIso, $a->country_iso));
                        $vSocials  = $site->socials->filter(fn($s)  => ($s->is_visible ?? true) && $geoVis($s->geo_mode, $s->geo_countries, $visIso, $s->country_iso));
                        $vSocNets  = $vSocials->filter(fn($s) => !in_array(strtolower($s->platform ?? ''), ['telegram','whatsapp','viber']));
                        $vMsgngers = $vSocials->filter(fn($s) =>  in_array(strtolower($s->platform ?? ''), ['telegram','whatsapp','viber']));
                        $totalVis  = $vPhones->count() + $vPrices->count() + $vAddrs->count() + $vSocials->count();
                        $totalAll  = $site->phones->count() + $site->prices->count() + $site->addresses->count() + $site->socials->count();
                    @endphp
                    <div id="vis-panel-{{ $visIso }}" style="display:none;">
                        <div class="vis-preview-bar" id="vis-bar-{{ $visIso }}">
                            <span>Перегляд: відвідувач <strong>{{ $visIso }}</strong>{{ isset($allIsoCountries[$visIso]) ? ' — '.$allIsoCountries[$visIso] : '' }}</span>
                            <button class="vis-preview-bar__exit" onclick="showVisitorPanel('_all')">✕ Вийти</button>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--border-2);">

                            {{-- LEFT: Що бачить відвідувач --}}
                            <div style="background:var(--panel);padding:20px;">
                                <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:6px;">
                                    <span style="width:6px;height:6px;border-radius:50%;background:#34d399;display:inline-block;"></span>
                                    Що бачить відвідувач — {{ $visIso }}
                                </div>

                                @if($totalVis === 0)
                                    <div style="text-align:center;padding:20px;color:var(--text-3);font-size:12px;">Нічого не показується для {{ $visIso }}</div>
                                @else

                                {{-- Телефони --}}
                                @if($vPhones->count())
                                    <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:6px;">Телефони</div>
                                    @foreach($vPhones as $p)
                                        <div onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-phone-{{ $p->id }}'"
                                             style="background:var(--panel-2);border-radius:var(--radius-item);padding:8px 10px;margin-bottom:5px;cursor:pointer;transition:background .1s;"
                                             onmouseover="this.style.background='var(--panel-hover,var(--border-2))'"
                                             onmouseout="this.style.background='var(--panel-2)'">
                                            <div style="font-family:var(--font-mono);font-size:13px;font-weight:600;color:var(--text);">{{ $p->number }}</div>
                                            @if($p->label)<div style="font-size:11px;color:var(--text-3);margin-top:1px;">{{ $p->label }}</div>@endif
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Ціни --}}
                                @if($vPrices->count())
                                    <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:6px;{{ $vPhones->count() ? 'margin-top:14px;' : '' }}">Ціни</div>
                                    @foreach($vPrices as $p)
                                        <div onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-price-{{ $p->id }}'"
                                             style="display:flex;justify-content:space-between;align-items:center;background:var(--panel-2);border-radius:var(--radius-item);padding:7px 10px;margin-bottom:5px;cursor:pointer;transition:background .1s;"
                                             onmouseover="this.style.background='var(--border-2)'"
                                             onmouseout="this.style.background='var(--panel-2)'">
                                            <span style="font-size:12px;color:var(--text-2);">{{ $p->label }}</span>
                                            <span style="font-family:var(--font-mono);font-weight:700;font-size:13px;color:#34d399;">{{ $p->amount }} {{ $p->currency }}</span>
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Адреси --}}
                                @if($vAddrs->count())
                                    <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:6px;{{ ($vPhones->count()||$vPrices->count()) ? 'margin-top:14px;' : '' }}">Адреси</div>
                                    @foreach($vAddrs as $a)
                                        <div onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-addr-{{ $a->id }}'"
                                             style="font-size:12px;color:var(--text-2);background:var(--panel-2);border-radius:var(--radius-item);padding:7px 10px;margin-bottom:5px;cursor:pointer;transition:background .1s;"
                                             onmouseover="this.style.background='var(--border-2)'"
                                             onmouseout="this.style.background='var(--panel-2)'">
                                            {{ trim(($a->city ?? '').' '.($a->street ?? '')) ?: '—' }}
                                        </div>
                                    @endforeach
                                @endif

                                {{-- Соц.мережі --}}
                                @if($vSocNets->count())
                                    <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:6px;{{ ($vPhones->count()||$vPrices->count()||$vAddrs->count()) ? 'margin-top:14px;' : '' }}">Соц.мережі</div>
                                    <div style="display:flex;flex-direction:column;gap:5px;">
                                        @foreach($vSocNets as $s)
                                            @php $sk = strtolower($s->platform ?? ''); $sic = $socialIcon[$sk] ?? ['c'=>'var(--text-3)','svg'=>'']; @endphp
                                            <div onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-social-{{ $s->id }}'"
                                                 style="display:flex;align-items:center;gap:8px;background:var(--panel-2);border-radius:var(--radius-item);padding:7px 10px;cursor:pointer;transition:background .1s;"
                                                 onmouseover="this.style.background='var(--border-2)'"
                                                 onmouseout="this.style.background='var(--panel-2)'">
                                                <span style="color:{{ $sic['c'] }};display:inline-flex;flex-shrink:0;">{!! $sic['svg'] !!}</span>
                                                <span style="font-size:12px;color:var(--text-2);">{{ ucfirst($s->platform) }}: {{ $s->handle }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                {{-- Месенджери --}}
                                @if($vMsgngers->count())
                                    <div style="font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-bottom:6px;{{ ($vPhones->count()||$vPrices->count()||$vAddrs->count()||$vSocNets->count()) ? 'margin-top:14px;' : '' }}">Месенджери</div>
                                    <div style="display:flex;flex-direction:column;gap:5px;">
                                        @foreach($vMsgngers as $s)
                                            @php $sk = strtolower($s->platform ?? ''); $sic = $socialIcon[$sk] ?? ['c'=>'var(--text-3)','svg'=>'']; @endphp
                                            <div onclick="location='{{ $url(['tab'=>'data']) }}#dt-edit-messenger-{{ $s->id }}'"
                                                 style="display:flex;align-items:center;gap:8px;background:var(--panel-2);border-radius:var(--radius-item);padding:7px 10px;cursor:pointer;transition:background .1s;"
                                                 onmouseover="this.style.background='var(--border-2)'"
                                                 onmouseout="this.style.background='var(--panel-2)'">
                                                <span style="color:{{ $sic['c'] }};display:inline-flex;flex-shrink:0;">{!! $sic['svg'] !!}</span>
                                                <span style="font-size:12px;color:var(--text-2);">{{ ucfirst($s->platform) }}: {{ $s->handle }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @endif {{-- /totalVis > 0 --}}
                            </div>

                            {{-- RIGHT: Всі поля з видимістю для цього відвідувача --}}
                            @php
                                $mThS = 'padding:4px 8px;text-align:left;font-size:10px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--border-2);white-space:nowrap;';
                                $mTdS = 'padding:4px 8px;font-size:11px;border-bottom:1px solid var(--border-2);';
                                $mVKey = \App\Models\CustomPlatform::messengerSlugs();
                                $mAllPhs = $site->phones->sortBy('sort_order');
                                $mAllPrs = $site->prices->sortBy('sort_order');
                                $mAllAds = $site->addresses->sortBy('sort_order');
                                $mAllMsgr = $site->socials->filter(fn($s)=>in_array(strtolower($s->platform??''),$mVKey))->sortBy('sort_order');
                                $mAllSocN = $site->socials->filter(fn($s)=>!in_array(strtolower($s->platform??''),$mVKey))->sortBy('sort_order');
                            @endphp
                            <div style="background:var(--panel);overflow-y:auto;">
                                <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;font-weight:600;padding:10px 12px 8px;display:flex;justify-content:space-between;border-bottom:1px solid var(--border-2);">
                                    <span>Всі поля</span>
                                    <span style="font-family:var(--font-mono);color:{{ $totalVis===$totalAll?'#34d399':($totalVis>0?'var(--warning)':'#f87171') }};">{{ $totalVis }}/{{ $totalAll }}</span>
                                </div>
                                @if($mAllPhs->count())
                                <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Телефони</div>
                                <table style="width:100%;border-collapse:collapse;">
                                    <tbody>
                                    @foreach($mAllPhs as $p)
                                    @php $pV = ($p->is_visible??true)&&$geoVis($p->geo_mode,$p->geo_countries,$visIso,$p->country_iso); @endphp
                                    <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                        <td style="{{ $mTdS }}font-family:var(--font-mono);font-size:11px;font-weight:600;color:var(--text);">{{ $p->number }}</td>
                                        <td style="{{ $mTdS }}color:var(--text-3);font-size:10px;">{{ $p->label ?: '' }}</td>
                                        <td style="{{ $mTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @endif
                                @if($mAllPrs->count())
                                <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Ціни</div>
                                <table style="width:100%;border-collapse:collapse;">
                                    <tbody>
                                    @foreach($mAllPrs as $p)
                                    @php $pV = ($p->is_visible??true)&&$geoVis($p->geo_mode,$p->geo_countries,$visIso,$p->country_iso); @endphp
                                    <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                        <td style="{{ $mTdS }}font-family:var(--font-mono);font-weight:700;color:#34d399;">{{ number_format($p->amount,2) }} {{ $p->currency }}</td>
                                        <td style="{{ $mTdS }}color:var(--text-3);font-size:10px;">{{ $p->label ?: '' }}</td>
                                        <td style="{{ $mTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @endif
                                @if($mAllAds->count())
                                <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Адреси</div>
                                <table style="width:100%;border-collapse:collapse;">
                                    <tbody>
                                    @foreach($mAllAds as $a)
                                    @php $pV = ($a->is_visible??true)&&$geoVis($a->geo_mode,$a->geo_countries,$visIso,$a->country_iso); @endphp
                                    <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                        <td style="{{ $mTdS }}color:var(--text-2);">{{ trim(($a->city??'').' '.($a->street??'')) ?: '—' }}</td>
                                        <td style="{{ $mTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @endif
                                @if($mAllMsgr->count())
                                <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Месенджери</div>
                                <table style="width:100%;border-collapse:collapse;">
                                    <tbody>
                                    @foreach($mAllMsgr as $s)
                                    @php $pV=($s->is_visible??true)&&$geoVis($s->geo_mode,$s->geo_countries,$visIso,$s->country_iso);$sk=strtolower($s->platform??'');$sic=$socialIcon[$sk]??['c'=>'var(--text-3)','svg'=>'']; @endphp
                                    <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                        <td style="{{ $mTdS }}"><span style="display:inline-flex;align-items:center;gap:4px;"><span style="color:{{ $sic['c'] }};display:inline-flex;">{!! $sic['svg'] !!}</span><span style="font-size:10px;color:var(--text-3);">{{ ucfirst($s->platform) }}</span></span></td>
                                        <td style="{{ $mTdS }}color:var(--text-2);font-size:10px;">{{ $s->handle ?: '—' }}</td>
                                        <td style="{{ $mTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @endif
                                @if($mAllSocN->count())
                                <div style="padding:6px 12px 3px;font-size:9px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:700;">Соцмережі</div>
                                <table style="width:100%;border-collapse:collapse;">
                                    <tbody>
                                    @foreach($mAllSocN as $s)
                                    @php $pV=($s->is_visible??true)&&$geoVis($s->geo_mode,$s->geo_countries,$visIso,$s->country_iso);$sk=strtolower($s->platform??'');$sic=$socialIcon[$sk]??['c'=>'var(--text-3)','svg'=>'']; @endphp
                                    <tr style="{{ !$pV?'opacity:.38;':'' }}">
                                        <td style="{{ $mTdS }}"><span style="display:inline-flex;align-items:center;gap:4px;"><span style="color:{{ $sic['c'] }};display:inline-flex;">{!! $sic['svg'] !!}</span><span style="font-size:10px;color:var(--text-3);">{{ ucfirst($s->platform) }}</span></span></td>
                                        <td style="{{ $mTdS }}color:var(--text-2);font-size:10px;">{{ $s->handle ?: '—' }}</td>
                                        <td style="{{ $mTdS }}text-align:center;width:20px;">@if($pV)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#34d399" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif</td>
                                    </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                <script>
                (function(){
                    var key = 'visPanel_{{ $site->id }}';
                    var iso = '';
                    try { iso = sessionStorage.getItem(key) || ''; } catch(e){}
                    if (!iso) iso = '_raw';
                    var panel = document.getElementById('vis-panel-' + iso);
                    if (!panel) iso = '_raw', panel = document.getElementById('vis-panel-_raw');
                    if (panel) panel.style.display = '';
                    var btn = document.getElementById('vis-tab-' + iso);
                    if (btn) btn.className = btn.className.replace('btn--ghost','btn--primary');
                })();
                </script>

                {{-- Conflicts — always visible so manager can verify setup --}}
                <div style="border-top:1px solid var(--border-2);padding:12px 20px;">
                    @if(count($conflicts) > 0)
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <span style="font-size:12px;font-weight:600;color:#f87171;text-transform:uppercase;letter-spacing:.05em;">Конфлікти ({{ count($conflicts) }})</span>
                    </div>
                    @endif
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
                    'phones'     => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
                    'prices'     => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                    'addresses'  => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
                    'socials'    => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
                    'messengers' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
                ];
                $messengerPlatforms = \App\Models\CustomPlatform::messengerOptions();
                $socialNetPlatforms = [
                    'instagram' => 'Instagram', 'facebook' => 'Facebook',  'youtube'   => 'YouTube',
                    'tiktok'    => 'TikTok',    'twitter'  => 'Twitter / X','linkedin'  => 'LinkedIn',
                    'pinterest' => 'Pinterest', 'threads'  => 'Threads',    'reddit'    => 'Reddit',
                    'vk'        => 'ВКонтакте', 'twitch'   => 'Twitch',
                ];
                $socialPlatforms    = $socialNetPlatforms + $messengerPlatforms;
                $messengerKeys      = array_keys($messengerPlatforms);
                $shownSocNetworks   = $shownSocials->filter(fn($s) => !in_array(strtolower($s->platform ?? ''), $messengerKeys))->values();
                $shownMessengers    = $shownSocials->filter(fn($s) => in_array(strtolower($s->platform ?? ''), $messengerKeys))->values();
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

            {{-- ═══ DATA SUB-TABS ══════════════════════════════════ --}}
            <div class="dt-subtabs">
                <button class="dt-subtab is-active" id="dst-contacts" onclick="dtSubTab('contacts')">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Контакти
                    <span class="dt-subtab__count" id="dst-contacts-count"></span>
                </button>
                <button class="dt-subtab" id="dst-details" onclick="dtSubTab('details')">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                    Деталі
                    <span class="dt-subtab__count" id="dst-details-count"></span>
                </button>
            </div>

            <div class="dt-grid">
            <div id="dt-group-contacts" class="dt-group" style="display:none;">
            {{-- ═══ PHONES ═══════════════════════════════════════════ --}}
            <div class="dt-card" id="data-phones">
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
                            <span id="dtchips-add-ph" class="dt-geo-chips" style="display:none;">
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

                <div class="dt-nav-list" data-type="phone">
                @php
                    // Roots = original primaries (no parent link, not in standby pool)
                    $ordPrimariesPh = $shownPhones->filter(fn($p) => !$p->standby_for_id && !$p->is_standby)->sortBy('sort_order');
                    $primaryIdsPh   = $ordPrimariesPh->pluck('id')->all();
                    // Children = ANY phone with standby_for_id (includes promoted active replacements)
                    $byParentPh     = $shownPhones->filter(fn($s) => $s->standby_for_id && in_array($s->standby_for_id, $primaryIdsPh))->groupBy('standby_for_id');
                    // Pool = is_standby=true with no parent link (general pool)
                    $poolPh         = $shownPhones->filter(fn($p) => !$p->standby_for_id && $p->is_standby)->sortBy('sort_order');
                    // Orphans = standby_for_id set but parent not known
                    $unlinkedPh     = $shownPhones->filter(fn($s) => $s->standby_for_id && !in_array($s->standby_for_id, $primaryIdsPh))->sortBy('sort_order');
                    // Badge count = waiting standbys only (is_standby=true, not blocked)
                    $sbCountsPh     = $byParentPh->map(fn($g) => $g->where('is_standby', true)->where('is_blocked', false)->count());
                    $phonesByIdPh   = $shownPhones->keyBy('id');
                    $allPhonesByIdPh= $site->phones->keyBy('id');
                    $flatPhones     = collect();
                    foreach ($ordPrimariesPh as $prPh) {
                        $flatPhones->push(['item' => $prPh, 'depth' => 0]);
                        foreach (($byParentPh->get($prPh->id, collect()))->sortBy('sort_order') as $sbPh) {
                            $flatPhones->push(['item' => $sbPh, 'depth' => 1]);
                        }
                    }
                    foreach ($poolPh as $sbPh) { $flatPhones->push(['item' => $sbPh, 'depth' => 0]); }
                    foreach ($unlinkedPh as $sbPh) { $flatPhones->push(['item' => $sbPh, 'depth' => 1]); }
                @endphp
                @if($flatPhones->isEmpty())
                    <div class="dt-empty">Телефонів немає</div>
                @else
                @foreach($flatPhones as $entry)
                @php
                    $ph = $entry['item']; $phDepth = $entry['depth']; $sbCount = $sbCountsPh->get($ph->id, 0);
                    $parentPhItem   = $phDepth && $ph->standby_for_id ? $allPhonesByIdPh->get($ph->standby_for_id) : null;
                    // Active replacement: child with is_standby=false (promoted by failover)
                    $isActiveReplPh = $phDepth && !$ph->is_standby && !$ph->is_blocked;
                @endphp
                <div class="dt-item {{ $phDepth ? 'dt-item--child' : 'dt-item--root' }}"
                     data-id="{{ $ph->id }}"
                     data-is-standby="{{ $ph->is_standby ? 1 : 0 }}"
                     data-parent-id="{{ $ph->standby_for_id ?? '' }}"
                     data-has-standbys="{{ $sbCount > 0 ? '1' : '0' }}"
                     data-sb-count="{{ $sbCount }}"
                     data-type="phone">
                    <div class="dt-item-row {{ !$phDepth ? 'dt-nav-primary' : '' }}" onclick="dtExpandItem('phone-{{ $ph->id }}')">
                        <span class="dt-nav-grip" title="{{ (!$phDepth && $sbCount > 0) ? 'Має резервних — не можна зробити резервним' : 'Потягни вправо = резерв, вліво = основний' }}"><svg viewBox="0 0 8 14" width="8" height="14" fill="currentColor" style="opacity:.4;"><circle cx="2" cy="2" r="1.2"/><circle cx="6" cy="2" r="1.2"/><circle cx="2" cy="7" r="1.2"/><circle cx="6" cy="7" r="1.2"/><circle cx="2" cy="12" r="1.2"/><circle cx="6" cy="12" r="1.2"/></svg></span>
                        <span class="dt-item-icon">{!! $dtIcons['phones'] !!}</span>
                        <div class="dt-item-main">
                            <div class="dt-item-name" style="font-family:var(--font-mono);">{{ $ph->number }}</div>
                            @if(!$phDepth)
                                @if($ph->label || $ph->is_blocked)
                                <div class="dt-item-sub">
                                    @if($ph->label){{ $ph->label }}@endif
                                    @if($ph->is_blocked)
                                        @if($ph->label)&thinsp;·&thinsp;@endif
                                        <span class="dt-badge dt-badge--blocked">✕ заблок.</span>
                                    @endif
                                </div>
                                @endif
                            @else
                                <div class="dt-item-sub">
                                    @if($ph->label){{ $ph->label }}&thinsp;·&thinsp;@endif
                                    @if($isActiveReplPh)
                                        <span class="dt-badge dt-badge--replacing">⟳ активний</span>
                                        @if($parentPhItem)<span class="dt-replacing-label">замість {{ $parentPhItem->number }}</span>@endif
                                    @elseif($ph->is_blocked)
                                        <span class="dt-badge dt-badge--blocked">✕ заблок.</span>
                                    @else
                                        <span class="dt-badge dt-badge--standby">⟳ резерв</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if(!$phDepth && $sbCount > 0)<span class="dt-nav-sb-badge">{{ $sbCount }}&thinsp;⟳</span>@endif
                        <div class="dt-vis">
                            @if(count($usedIso)===0||($ph->geo_mode??'all')==='all')<span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                            @elseif(($ph->geo_mode??'all')==='include')@forelse((array)($ph->geo_countries??[]) as $iso)<span class="dt-vis-badge dt-vis-badge--ok">{{ $iso }}</span>@empty<span class="dt-vis-badge dt-vis-badge--no">—</span>@endforelse
                            @else<span class="dt-vis-badge" style="font-size:9px;opacity:.6;">Крім</span>@forelse((array)($ph->geo_countries??[]) as $iso)<span class="dt-vis-badge dt-vis-badge--no">{{ $iso }}</span>@empty<span class="dt-vis-badge dt-vis-badge--all">Всі</span>@endforelse @endif
                        </div>
                        <div class="dt-item-actions" onclick="event.stopPropagation()">
                            <form method="POST" action="{{ route('sites.visibility.toggle',[$site,'phones',$ph->id]) }}" style="margin:0;">@csrf
                                <button type="submit" class="icon-btn" title="{{ ($ph->is_visible??true)?'Приховати':'Показати' }}" style="color:{{ ($ph->is_visible??true)?'var(--text-3)':'var(--warning)' }};">
                                    @if($ph->is_visible??true)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('phones.destroy',[$site,$ph]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">@csrf @method('DELETE')
                                <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg></button>
                            </form>
                            <button class="icon-btn" id="dt-expand-phone-{{ $ph->id }}" title="Редагувати" onclick="dtExpandItem('phone-{{ $ph->id }}')"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg></button>
                        </div>
                    </div>
                    <div class="dt-panel" id="dt-edit-phone-{{ $ph->id }}" style="display:none;">
                        <form method="POST" action="{{ route('phones.update',[$site,$ph]) }}">@csrf @method('PUT')
                            <input type="hidden" name="sort_order" value="{{ $ph->sort_order }}">
                            <div class="dt-row dt-row--2">
                                <div><label class="dt-label">Номер *</label><input type="text" name="number" class="dt-input" value="{{ $ph->number }}" required></div>
                                <div><label class="dt-label">Мітка</label><input type="text" name="label" class="dt-input" value="{{ $ph->label }}" placeholder="Головний…"></div>
                            </div>
                            <div class="dt-geo-row"><span class="dt-geo-label">Видно:</span>
                                @php $em=$ph->geo_mode??'all'; @endphp
                                @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv=>$ml)<label class="dt-geo-pill {{ $em===$mv?'is-on':'' }}" id="dtpill-ph{{ $ph->id }}-{{ $mv }}"><input type="radio" name="geo_mode" value="{{ $mv }}" {{ $em===$mv?'checked':'' }} style="display:none;" onchange="dtGeoMode('ph{{ $ph->id }}','{{ $mv }}')">{{ $ml }}</label>@endforeach
                                @if(count($usedIso))<span id="dtchips-ph{{ $ph->id }}" class="dt-geo-chips" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};">@foreach($usedIso as $iso)<label class="dt-geo-chip {{ in_array($iso,(array)($ph->geo_countries??[]))?'is-on':'' }}" id="dtchip-ph{{ $ph->id }}-{{ $iso }}"><input type="checkbox" name="geo_countries[]" value="{{ $iso }}" {{ in_array($iso,(array)($ph->geo_countries??[]))?'checked':'' }} style="display:none;" onchange="dtGeoChip('ph{{ $ph->id }}','{{ $iso }}',this)">{{ $iso }}</label>@endforeach</span>@endif
                            </div>
                            <div class="dt-panel__actions">
                                <button type="button" class="btn btn--ghost btn--sm" onclick="dtExpandItem('phone-{{ $ph->id }}')">Скасувати</button>
                                <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
                @endif
                </div>
            </div>

            {{-- ═══ MESSENGERS ════════════════════════════════════════ --}}
            <div class="dt-card" id="data-messengers">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">{!! $dtIcons['messengers'] !!}</span>
                    <span class="dt-card-head__title">Месенджери</span>
                    <span class="dt-card-head__count">{{ $site->socials->filter(fn($s)=>in_array(strtolower($s->platform??''),$messengerKeys))->count() }}</span>
                    <button class="dt-add-btn" id="dt-add-btn-messengers" onclick="dtToggleAdd('messengers')">
                        <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Додати
                    </button>
                </div>

                <div class="dt-panel" id="dt-add-messengers" style="display:none;">
                    <div class="dt-panel__title">Новий месенджер</div>
                    <form method="POST" action="{{ route('socials.store', $site) }}" data-ms-form>
                        @csrf
                        <input type="hidden" name="sort_order" value="{{ $site->socials->count() }}">
                        <div class="dt-row dt-row--2">
                            <div>
                                <label class="dt-label">Платформа *</label>
                                <select name="platform" class="dt-input show-ms-platform-sel" required
                                        onchange="onShowMsPlatformChange(this)">
                                    @foreach($messengerPlatforms as $val => $lbl)
                                        <option value="{{ $val }}">{{ $lbl }}</option>
                                    @endforeach
                                    <option value="__new__">➕ Інший месенджер...</option>
                                </select>
                                <input type="text" name="platform_custom" class="dt-input show-ms-custom-inp"
                                       placeholder="Назва месенджера" maxlength="50"
                                       style="display:none;margin-top:6px;">
                            </div>
                            <div>
                                <label class="dt-label">Нікнейм / номер</label>
                                <input type="text" name="handle" class="dt-input" placeholder="@username або номер">
                            </div>
                        </div>
                        <div class="dt-row" style="margin-bottom:8px;">
                            <label class="dt-label">Посилання</label>
                            <input type="url" name="url" class="dt-input" placeholder="https://t.me/…">
                        </div>
                        @if($site->phones->count())
                        <div class="dt-row" style="margin-bottom:8px;">
                            <label class="dt-label">Прив'язати до номеру</label>
                            <select name="phone_id" class="dt-input">
                                <option value="">— без прив'язки —</option>
                                @foreach($site->phones as $ph)
                                    <option value="{{ $ph->id }}">{{ $ph->number }}{{ $ph->label ? ' · '.$ph->label : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="dt-geo-row">
                            <span class="dt-geo-label">Видно:</span>
                            @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                <label class="dt-geo-pill {{ $mv==='all'?'is-on':'' }}" id="dtpill-add-ms-{{ $mv }}">
                                    <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $mv==='all'?'checked':'' }} style="display:none;"
                                           onchange="dtGeoMode('add-ms','{{ $mv }}')">{{ $ml }}
                                </label>
                            @endforeach
                            <span id="dtchips-add-ms" class="dt-geo-chips" style="display:none;">
                                @foreach($usedIso as $iso)
                                    <label class="dt-geo-chip" id="dtchip-add-ms-{{ $iso }}">
                                        <input type="checkbox" name="geo_countries[]" value="{{ $iso }}" style="display:none;"
                                               onchange="dtGeoChip('add-ms','{{ $iso }}',this)">{{ $iso }}
                                    </label>
                                @endforeach
                            </span>
                        </div>
                        <div class="dt-panel__actions">
                            <button type="button" class="btn btn--ghost btn--sm" onclick="dtToggleAdd('messengers')">Скасувати</button>
                            <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                        </div>
                    </form>
                </div>

                <div class="dt-nav-list" data-type="social">
                @php
                    $ordPrimariesMs = $shownMessengers->filter(fn($p) => !$p->standby_for_id && !$p->is_standby)->sortBy('sort_order');
                    $primaryIdsMs   = $ordPrimariesMs->pluck('id')->all();
                    $byParentMs     = $shownMessengers->filter(fn($s) => $s->standby_for_id && in_array($s->standby_for_id, $primaryIdsMs))->groupBy('standby_for_id');
                    $poolMs         = $shownMessengers->filter(fn($p) => !$p->standby_for_id && $p->is_standby)->sortBy('sort_order');
                    $unlinkedMs     = $shownMessengers->filter(fn($s) => $s->standby_for_id && !in_array($s->standby_for_id, $primaryIdsMs))->sortBy('sort_order');
                    $sbCountsMs     = $byParentMs->map(fn($g) => $g->where('is_standby', true)->where('is_blocked', false)->count());
                    $socialsByIdMs   = $shownMessengers->keyBy('id');
                    $allSocialsByIdMs= $site->socials->keyBy('id');
                    $flatMessengers = collect();
                    foreach ($ordPrimariesMs as $prMs) {
                        $flatMessengers->push(['item' => $prMs, 'depth' => 0]);
                        foreach (($byParentMs->get($prMs->id, collect()))->sortBy('sort_order') as $sbMs) {
                            $flatMessengers->push(['item' => $sbMs, 'depth' => 1]);
                        }
                    }
                    foreach ($poolMs as $sbMs) { $flatMessengers->push(['item' => $sbMs, 'depth' => 0]); }
                    foreach ($unlinkedMs as $sbMs) { $flatMessengers->push(['item' => $sbMs, 'depth' => 1]); }
                @endphp
                @if($flatMessengers->isEmpty())
                    <div class="dt-empty">Месенджерів немає</div>
                @else
                @foreach($flatMessengers as $entry)
                @php
                    $ms = $entry['item']; $msDepth = $entry['depth']; $msbCount = $sbCountsMs->get($ms->id, 0);
                    $msk=strtolower($ms->platform??''); $msic=$socialIcon[$msk]??['c'=>'var(--text-3)','svg'=>'<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/></svg>'];
                    $parentMsItem   = $msDepth && $ms->standby_for_id ? $allSocialsByIdMs->get($ms->standby_for_id) : null;
                    $isActiveReplMs = $msDepth && !$ms->is_standby && !$ms->is_blocked;
                @endphp
                <div class="dt-item {{ $msDepth ? 'dt-item--child' : 'dt-item--root' }}"
                     data-id="{{ $ms->id }}"
                     data-is-standby="{{ $ms->is_standby ? 1 : 0 }}"
                     data-parent-id="{{ $ms->standby_for_id ?? '' }}"
                     data-has-standbys="{{ $msbCount > 0 ? '1' : '0' }}"
                     data-sb-count="{{ $msbCount }}"
                     data-type="social">
                    <div class="dt-item-row {{ !$msDepth ? 'dt-nav-primary' : '' }}" onclick="dtExpandItem('social-{{ $ms->id }}')">
                        <span class="dt-nav-grip" title="{{ (!$msDepth && $msbCount > 0) ? 'Має резервних — не можна зробити резервним' : 'Потягни вправо = резерв, вліво = основний' }}"><svg viewBox="0 0 8 14" width="8" height="14" fill="currentColor" style="opacity:.4;"><circle cx="2" cy="2" r="1.2"/><circle cx="6" cy="2" r="1.2"/><circle cx="2" cy="7" r="1.2"/><circle cx="6" cy="7" r="1.2"/><circle cx="2" cy="12" r="1.2"/><circle cx="6" cy="12" r="1.2"/></svg></span>
                        <span class="dt-item-icon" style="color:{{ $msic['c'] }}">{!! $msic['svg'] !!}</span>
                        <div class="dt-item-main">
                            <div class="dt-item-name">{{ $ms->handle ?: ucfirst($ms->platform) }}</div>
                            @if(!$msDepth)
                                <div class="dt-item-sub">{{ ucfirst($ms->platform) }}
                                    @if($ms->phone_id && ($linkedPh=$site->phones->find($ms->phone_id)))<span class="dt-link-badge"><svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 10a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.59a16 16 0 0 0 6 6l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.72 16z"/></svg>{{ $linkedPh->number }}</span>@endif
                                    @if($ms->is_blocked)
                                        <span class="dt-badge dt-badge--blocked">✕ заблок.</span>
                                    @endif
                                </div>
                            @else
                                <div class="dt-item-sub">
                                    {{ ucfirst($ms->platform) }}&thinsp;·&thinsp;
                                    @if($isActiveReplMs)
                                        <span class="dt-badge dt-badge--replacing">⟳ активний</span>
                                        @if($parentMsItem)<span class="dt-replacing-label">замість {{ $parentMsItem->handle ?: ucfirst($parentMsItem->platform) }}</span>@endif
                                    @elseif($ms->is_blocked)
                                        <span class="dt-badge dt-badge--blocked">✕ заблок.</span>
                                    @else
                                        <span class="dt-badge dt-badge--standby">⟳ резерв</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if(!$msDepth && $msbCount > 0)<span class="dt-nav-sb-badge">{{ $msbCount }}&thinsp;⟳</span>@endif
                        <div class="dt-vis">
                            @if(count($usedIso)===0||($ms->geo_mode??'all')==='all')<span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                            @elseif(($ms->geo_mode??'all')==='include')@forelse((array)($ms->geo_countries??[]) as $iso)<span class="dt-vis-badge dt-vis-badge--ok">{{ $iso }}</span>@empty<span class="dt-vis-badge dt-vis-badge--no">—</span>@endforelse
                            @else<span class="dt-vis-badge" style="font-size:9px;opacity:.6;">Крім</span>@forelse((array)($ms->geo_countries??[]) as $iso)<span class="dt-vis-badge dt-vis-badge--no">{{ $iso }}</span>@empty<span class="dt-vis-badge dt-vis-badge--all">Всі</span>@endforelse @endif
                        </div>
                        <div class="dt-item-actions" onclick="event.stopPropagation()">
                            <form method="POST" action="{{ route('sites.visibility.toggle',[$site,'socials',$ms->id]) }}" style="margin:0;">@csrf
                                <button type="submit" class="icon-btn" title="{{ ($ms->is_visible??true)?'Приховати':'Показати' }}" style="color:{{ ($ms->is_visible??true)?'var(--text-3)':'var(--warning)' }};">
                                    @if($ms->is_visible??true)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif
                                </button>
                            </form>
                            <form method="POST" action="{{ route('socials.destroy',[$site,$ms]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">@csrf @method('DELETE')
                                <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg></button>
                            </form>
                            <button class="icon-btn" id="dt-expand-social-{{ $ms->id }}" title="Редагувати" onclick="dtExpandItem('social-{{ $ms->id }}')"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg></button>
                        </div>
                    </div>
                    <div class="dt-panel" id="dt-edit-social-{{ $ms->id }}" style="display:none;">
                        <form method="POST" action="{{ route('socials.update',[$site,$ms]) }}" data-ms-form>@csrf @method('PUT')
                            <input type="hidden" name="sort_order" value="{{ $ms->sort_order }}">
                            <div class="dt-row dt-row--2">
                                <div><label class="dt-label">Платформа *</label>
                                    <select name="platform" class="dt-input show-ms-platform-sel" required onchange="onShowMsPlatformChange(this)">
                                        @foreach($messengerPlatforms as $val=>$lbl)<option value="{{ $val }}" {{ $ms->platform===$val?'selected':'' }}>{{ $lbl }}</option>@endforeach
                                        @if(!array_key_exists($ms->platform, $messengerPlatforms))<option value="{{ $ms->platform }}" selected>{{ $ms->platform }}</option>@endif
                                        <option value="__new__">➕ Інший месенджер...</option>
                                    </select>
                                    <input type="text" name="platform_custom" class="dt-input show-ms-custom-inp" placeholder="Назва месенджера" maxlength="50" style="display:none;margin-top:6px;">
                                </div>
                                <div><label class="dt-label">Нікнейм / номер</label><input type="text" name="handle" class="dt-input" value="{{ $ms->handle }}"></div>
                            </div>
                            <div class="dt-row" style="margin-bottom:8px;"><label class="dt-label">Посилання</label><input type="url" name="url" class="dt-input" value="{{ $ms->url }}"></div>
                            @if($site->phones->count())<div class="dt-row" style="margin-bottom:8px;"><label class="dt-label">Прив'язати до номеру</label><select name="phone_id" class="dt-input"><option value="">— без прив'язки —</option>@foreach($site->phones as $ph)<option value="{{ $ph->id }}" {{ $ms->phone_id==$ph->id?'selected':'' }}>{{ $ph->number }}{{ $ph->label?' · '.$ph->label:'' }}</option>@endforeach</select></div>@endif
                            <div class="dt-geo-row"><span class="dt-geo-label">Видно:</span>
                                @php $em=$ms->geo_mode??'all'; @endphp
                                @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv=>$ml)<label class="dt-geo-pill {{ $em===$mv?'is-on':'' }}" id="dtpill-so{{ $ms->id }}-{{ $mv }}"><input type="radio" name="geo_mode" value="{{ $mv }}" {{ $em===$mv?'checked':'' }} style="display:none;" onchange="dtGeoMode('so{{ $ms->id }}','{{ $mv }}')">{{ $ml }}</label>@endforeach
                                @if(count($usedIso))<span id="dtchips-so{{ $ms->id }}" class="dt-geo-chips" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};">@foreach($usedIso as $iso)<label class="dt-geo-chip {{ in_array($iso,(array)($ms->geo_countries??[]))?'is-on':'' }}" id="dtchip-so{{ $ms->id }}-{{ $iso }}"><input type="checkbox" name="geo_countries[]" value="{{ $iso }}" {{ in_array($iso,(array)($ms->geo_countries??[]))?'checked':'' }} style="display:none;" onchange="dtGeoChip('so{{ $ms->id }}','{{ $iso }}',this)">{{ $iso }}</label>@endforeach</span>@endif
                            </div>
                            <div class="dt-panel__actions">
                                <button type="button" class="btn btn--ghost btn--sm" onclick="dtExpandItem('social-{{ $ms->id }}')">Скасувати</button>
                                <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endforeach
                @endif
                </div>
            </div>

            </div>{{-- /dt-group-contacts --}}

            <div id="dt-group-details" class="dt-group" style="display:none;">
            {{-- ═══ PRICES ═══════════════════════════════════════════ --}}
            <div class="dt-card" id="data-prices">
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
                                <label class="dt-label">Валюта</label>
                                <input type="text" name="currency" class="dt-input" placeholder="UAH" maxlength="3" style="text-transform:uppercase;">
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
                            <span id="dtchips-add-pr" class="dt-geo-chips" style="display:none;">
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
                                @elseif(($p->geo_mode??'all')==='include')
                                    @forelse((array)($p->geo_countries??[]) as $iso)
                                        <span class="dt-vis-badge dt-vis-badge--ok">{{ $iso }}</span>
                                    @empty
                                        <span class="dt-vis-badge dt-vis-badge--no">—</span>
                                    @endforelse
                                @else
                                    <span class="dt-vis-badge" style="font-size:9px;opacity:.6;letter-spacing:.02em;">Крім</span>
                                    @forelse((array)($p->geo_countries??[]) as $iso)
                                        <span class="dt-vis-badge dt-vis-badge--no">{{ $iso }}</span>
                                    @empty
                                        <span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                                    @endforelse
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
                                <button class="icon-btn" id="dt-expand-price-{{ $p->id }}" title="Редагувати" onclick="dtExpandItem('price-{{ $p->id }}')">
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
                                        <label class="dt-label">Валюта</label>
                                        <input type="text" name="currency" class="dt-input" value="{{ $p->currency }}" maxlength="3" style="text-transform:uppercase;">
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
                                    <span id="dtchips-pr{{ $p->id }}" class="dt-geo-chips" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};">
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

            {{-- ═══ SOCIAL NETWORKS ══════════════════════════════════ --}}
            <div class="dt-card" id="data-socials">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">{!! $dtIcons['socials'] !!}</span>
                    <span class="dt-card-head__title">Соціальні мережі</span>
                    <span class="dt-card-head__count">{{ $site->socials->filter(fn($s)=>!in_array(strtolower($s->platform??''),$messengerKeys))->count() }}</span>
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
                                    @foreach($socialNetPlatforms as $val => $lbl)
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
                        <div class="dt-geo-row">
                            <span class="dt-geo-label">Видно:</span>
                            @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                <label class="dt-geo-pill {{ $mv==='all'?'is-on':'' }}" id="dtpill-add-so-{{ $mv }}">
                                    <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $mv==='all'?'checked':'' }} style="display:none;"
                                           onchange="dtGeoMode('add-so','{{ $mv }}')">{{ $ml }}
                                </label>
                            @endforeach
                            <span id="dtchips-add-so" class="dt-geo-chips" style="display:none;">
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
                    @forelse($shownSocNetworks as $s)
                    @php $sk = strtolower($s->platform ?? ''); $sic = $socialIcon[$sk] ?? ['c'=>'var(--text-3)','svg'=>'<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/></svg>']; @endphp
                    <div class="dt-item">
                        <div class="dt-item-row" onclick="dtExpandItem('social-{{ $s->id }}')">
                            <span class="dt-item-icon" style="color:{{ $sic['c'] }}">{!! $sic['svg'] !!}</span>
                            <div class="dt-item-main">
                                <div class="dt-item-name">{{ $s->handle }}</div>
                                <div class="dt-item-sub">{{ ucfirst($s->platform) }}</div>
                            </div>
                            <div class="dt-vis">
                                @if(count($usedIso)===0||($s->geo_mode??'all')==='all')<span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                                @elseif(($s->geo_mode??'all')==='include')@forelse((array)($s->geo_countries??[]) as $iso)<span class="dt-vis-badge dt-vis-badge--ok">{{ $iso }}</span>@empty<span class="dt-vis-badge dt-vis-badge--no">—</span>@endforelse
                                @else<span class="dt-vis-badge" style="font-size:9px;opacity:.6;">Крім</span>@forelse((array)($s->geo_countries??[]) as $iso)<span class="dt-vis-badge dt-vis-badge--no">{{ $iso }}</span>@empty<span class="dt-vis-badge dt-vis-badge--all">Всі</span>@endforelse
                                @endif
                            </div>
                            <div class="dt-item-actions" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('sites.visibility.toggle',[$site,'socials',$s->id]) }}" style="margin:0;">@csrf
                                    <button type="submit" class="icon-btn" title="{{ ($s->is_visible??true)?'Приховати':'Показати' }}" style="color:{{ ($s->is_visible??true)?'var(--text-3)':'var(--warning)' }};">
                                        @if($s->is_visible??true)<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>@else<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>@endif
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('socials.destroy',[$site,$s]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">@csrf @method('DELETE')
                                    <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg></button>
                                </form>
                                <button class="icon-btn" id="dt-expand-social-{{ $s->id }}" title="Редагувати" onclick="dtExpandItem('social-{{ $s->id }}')"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg></button>
                            </div>
                        </div>
                        <div class="dt-panel" id="dt-edit-social-{{ $s->id }}" style="display:none;">
                            <form method="POST" action="{{ route('socials.update',[$site,$s]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="sort_order" value="{{ $s->sort_order }}">
                                <div class="dt-row dt-row--2">
                                    <div><label class="dt-label">Платформа *</label>
                                        <select name="platform" class="dt-input" required>
                                            @foreach($socialNetPlatforms as $val => $lbl)<option value="{{ $val }}" {{ $s->platform===$val?'selected':'' }}>{{ $lbl }}</option>@endforeach
                                        </select>
                                    </div>
                                    <div><label class="dt-label">Нікнейм *</label><input type="text" name="handle" class="dt-input" value="{{ $s->handle }}" required></div>
                                </div>
                                <div class="dt-row" style="margin-bottom:8px;"><label class="dt-label">URL *</label><input type="url" name="url" class="dt-input" value="{{ $s->url }}" required></div>
                                <div class="dt-geo-row">
                                    <span class="dt-geo-label">Видно:</span>
                                    @php $em = $s->geo_mode ?? 'all'; @endphp
                                    @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv => $ml)
                                        <label class="dt-geo-pill {{ $em===$mv?'is-on':'' }}" id="dtpill-so{{ $s->id }}-{{ $mv }}"><input type="radio" name="geo_mode" value="{{ $mv }}" {{ $em===$mv?'checked':'' }} style="display:none;" onchange="dtGeoMode('so{{ $s->id }}','{{ $mv }}')">{{ $ml }}</label>
                                    @endforeach
                                    @if(count($usedIso))<span id="dtchips-so{{ $s->id }}" class="dt-geo-chips" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};">@foreach($usedIso as $iso)<label class="dt-geo-chip {{ in_array($iso,(array)($s->geo_countries??[]))?'is-on':'' }}" id="dtchip-so{{ $s->id }}-{{ $iso }}"><input type="checkbox" name="geo_countries[]" value="{{ $iso }}" {{ in_array($iso,(array)($s->geo_countries??[]))?'checked':'' }} style="display:none;" onchange="dtGeoChip('so{{ $s->id }}','{{ $iso }}',this)">{{ $iso }}</label>@endforeach</span>@endif
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
            <div class="dt-card" id="data-addresses">
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
                            <span id="dtchips-add-ad" class="dt-geo-chips" style="display:none;">
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
                                <div class="dt-item-name">
                                    @if($a->label)<strong>{{ $a->label }}</strong> · @endif{{ $a->city }}
                                </div>
                                <div class="dt-item-sub">
                                    {{ $a->country_iso }}{{ $a->region ? ' · '.$a->region : '' }}{{ $a->street ? ' · '.$a->street.($a->building ? ' '.$a->building : '') : '' }}{{ $a->postal_code ? ' · '.$a->postal_code : '' }}
                                </div>
                            </div>
                            <div class="dt-vis">
                                @if(count($usedIso)===0 || ($a->geo_mode??'all')==='all')
                                    <span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                                @elseif(($a->geo_mode??'all')==='include')
                                    @forelse((array)($a->geo_countries??[]) as $iso)
                                        <span class="dt-vis-badge dt-vis-badge--ok">{{ $iso }}</span>
                                    @empty
                                        <span class="dt-vis-badge dt-vis-badge--no">—</span>
                                    @endforelse
                                @else
                                    <span class="dt-vis-badge" style="font-size:9px;opacity:.6;letter-spacing:.02em;">Крім</span>
                                    @forelse((array)($a->geo_countries??[]) as $iso)
                                        <span class="dt-vis-badge dt-vis-badge--no">{{ $iso }}</span>
                                    @empty
                                        <span class="dt-vis-badge dt-vis-badge--all">Всі</span>
                                    @endforelse
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
                                <button class="icon-btn" id="dt-expand-addr-{{ $a->id }}" title="Редагувати" onclick="dtExpandItem('addr-{{ $a->id }}')">
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
                                    <span id="dtchips-ad{{ $a->id }}" class="dt-geo-chips" style="display:{{ in_array($em,['include','exclude'])?'flex':'none' }};">
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

            {{-- ═══ LINKS (email / url) ══════════════════════════════ --}}
            @php
                $siteLinks = $site->customFields->whereIn('field_type', ['url', 'email']);
                $siteTexts = $site->customFields->whereNotIn('field_type', ['url', 'email']);
                $linkIcon  = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>';
                $textIcon  = '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
            @endphp
            <div class="dt-card" id="data-links">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">{!! $linkIcon !!}</span>
                    <span class="dt-card-head__title">Посилання</span>
                    <span class="dt-card-head__count">{{ $siteLinks->count() }}</span>
                    <button class="dt-add-btn" id="dt-add-btn-links" onclick="dtToggleAdd('links')">
                        <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Додати
                    </button>
                </div>
                <div class="dt-panel" id="dt-add-links" style="display:none;">
                    <div class="dt-panel__title">Нове посилання</div>
                    <form method="POST" action="{{ route('fields.store', $site) }}">
                        @csrf
                        <input type="hidden" name="field_type" value="url">
                        <input type="hidden" name="sort_order" value="0">
                        <div class="dt-row dt-row--2">
                            <div>
                                <label class="dt-label">Назва</label>
                                <input type="text" name="field_key" class="dt-input" placeholder="Сайт, Email підтримки…" required>
                            </div>
                            <div>
                                <label class="dt-label">Тип</label>
                                <select name="field_type" class="dt-input">
                                    <option value="url">URL</option>
                                    <option value="email">Email</option>
                                </select>
                            </div>
                        </div>
                        <div class="dt-row" style="margin-bottom:8px;">
                            <label class="dt-label">Значення (URL або email)</label>
                            <input type="text" name="field_value" class="dt-input" placeholder="https://… або user@domain.com" required>
                        </div>
                        <div class="dt-panel__actions">
                            <button type="button" class="btn btn--ghost btn--sm" onclick="dtToggleAdd('links')">Скасувати</button>
                            <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                        </div>
                    </form>
                </div>
                <div class="dt-items">
                    @forelse($siteLinks as $cf)
                    <div class="dt-item">
                        <div class="dt-item-row" onclick="dtExpandItem('link-{{ $cf->id }}')">
                            <span class="dt-item-icon">
                                @if($cf->field_type === 'email')
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                @else
                                    {!! $linkIcon !!}
                                @endif
                            </span>
                            <div class="dt-item-main">
                                <div class="dt-item-name">{{ $cf->field_key }}</div>
                                <div class="dt-item-sub" style="font-family:var(--font-mono);">{{ $cf->field_value }}</div>
                            </div>
                            <div class="dt-item-actions" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('fields.destroy',[$site,$cf]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                    </button>
                                </form>
                                <button class="icon-btn" id="dt-expand-link-{{ $cf->id }}" title="Редагувати" onclick="dtExpandItem('link-{{ $cf->id }}')">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="dt-panel" id="dt-edit-link-{{ $cf->id }}" style="display:none;">
                            <form method="POST" action="{{ route('fields.update',[$site,$cf]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="sort_order" value="{{ $cf->sort_order }}">
                                <div class="dt-row dt-row--2">
                                    <div>
                                        <label class="dt-label">Назва</label>
                                        <input type="text" name="field_key" class="dt-input" value="{{ $cf->field_key }}" required>
                                    </div>
                                    <div>
                                        <label class="dt-label">Тип</label>
                                        <select name="field_type" class="dt-input">
                                            <option value="url" {{ $cf->field_type==='url'?'selected':'' }}>URL</option>
                                            <option value="email" {{ $cf->field_type==='email'?'selected':'' }}>Email</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="dt-row" style="margin-bottom:8px;">
                                    <label class="dt-label">Значення</label>
                                    <input type="text" name="field_value" class="dt-input" value="{{ $cf->field_value }}" required>
                                </div>
                                <div class="dt-panel__actions">
                                    <button type="button" class="btn btn--ghost btn--sm" onclick="dtExpandItem('link-{{ $cf->id }}')">Скасувати</button>
                                    <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                        <div class="dt-empty">Посилань немає</div>
                    @endforelse
                </div>
            </div>

            {{-- ═══ TEXT FIELDS ════════════════════════════════════════ --}}
            <div class="dt-card" id="data-text">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">{!! $textIcon !!}</span>
                    <span class="dt-card-head__title">Текстові поля</span>
                    <span class="dt-card-head__count">{{ $siteTexts->count() }}</span>
                    <button class="dt-add-btn" id="dt-add-btn-text" onclick="dtToggleAdd('text')">
                        <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                        Додати
                    </button>
                </div>
                <div class="dt-panel" id="dt-add-text" style="display:none;">
                    <div class="dt-panel__title">Нове текстове поле</div>
                    <form method="POST" action="{{ route('fields.store', $site) }}">
                        @csrf
                        <input type="hidden" name="sort_order" value="0">
                        <div class="dt-row dt-row--2">
                            <div>
                                <label class="dt-label">Назва поля</label>
                                <input type="text" name="field_key" class="dt-input" placeholder="Короткий опис, Нотатка…" required>
                            </div>
                            <div>
                                <label class="dt-label">Тип</label>
                                <select name="field_type" class="dt-input">
                                    <option value="text">Текст</option>
                                    <option value="number">Число</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>
                        </div>
                        <div class="dt-row" style="margin-bottom:8px;">
                            <label class="dt-label">Значення</label>
                            <textarea name="field_value" class="dt-input" rows="3" style="resize:vertical;" placeholder="Будь-який текст…" required></textarea>
                        </div>
                        <div class="dt-panel__actions">
                            <button type="button" class="btn btn--ghost btn--sm" onclick="dtToggleAdd('text')">Скасувати</button>
                            <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                        </div>
                    </form>
                </div>
                <div class="dt-items">
                    @forelse($siteTexts as $cf)
                    <div class="dt-item">
                        <div class="dt-item-row" onclick="dtExpandItem('text-{{ $cf->id }}')">
                            <span class="dt-item-icon">{!! $textIcon !!}</span>
                            <div class="dt-item-main">
                                <div class="dt-item-name">{{ $cf->field_key }}</div>
                                <div class="dt-item-sub">{{ Str::limit($cf->field_value, 60) }}</div>
                            </div>
                            <div class="dt-item-actions" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('fields.destroy',[$site,$cf]) }}" style="margin:0;" onsubmit="return confirm('Видалити?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn" style="color:var(--danger);" title="Видалити">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                                    </button>
                                </form>
                                <button class="icon-btn" id="dt-expand-text-{{ $cf->id }}" title="Редагувати" onclick="dtExpandItem('text-{{ $cf->id }}')">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="transition:transform .15s;"><path d="M9 18l6-6-6-6"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="dt-panel" id="dt-edit-text-{{ $cf->id }}" style="display:none;">
                            <form method="POST" action="{{ route('fields.update',[$site,$cf]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="sort_order" value="{{ $cf->sort_order }}">
                                <div class="dt-row dt-row--2">
                                    <div>
                                        <label class="dt-label">Назва поля</label>
                                        <input type="text" name="field_key" class="dt-input" value="{{ $cf->field_key }}" required>
                                    </div>
                                    <div>
                                        <label class="dt-label">Тип</label>
                                        <select name="field_type" class="dt-input">
                                            <option value="text" {{ $cf->field_type==='text'?'selected':'' }}>Текст</option>
                                            <option value="number" {{ $cf->field_type==='number'?'selected':'' }}>Число</option>
                                            <option value="json" {{ $cf->field_type==='json'?'selected':'' }}>JSON</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="dt-row" style="margin-bottom:8px;">
                                    <label class="dt-label">Значення</label>
                                    <textarea name="field_value" class="dt-input" rows="3" style="resize:vertical;" required>{{ $cf->field_value }}</textarea>
                                </div>
                                <div class="dt-panel__actions">
                                    <button type="button" class="btn btn--ghost btn--sm" onclick="dtExpandItem('text-{{ $cf->id }}')">Скасувати</button>
                                    <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                        <div class="dt-empty">Текстових полів немає</div>
                    @endforelse
                </div>
            </div>

            </div>{{-- /dt-group-details --}}
            <script>
            (function(){
                var stored = '';
                try { stored = sessionStorage.getItem('dtSubTab') || ''; } catch(e){}
                var show = (stored === 'details') ? 'details' : 'contacts';
                var showEl = document.getElementById('dt-group-' + show);
                var hideEl = document.getElementById('dt-group-' + (show === 'details' ? 'contacts' : 'details'));
                if (showEl) showEl.style.display = '';
                if (hideEl) hideEl.style.display = 'none';
                var showBtn = document.getElementById('dst-' + show);
                var hideBtn = document.getElementById('dst-' + (show === 'details' ? 'contacts' : 'details'));
                if (showBtn) showBtn.classList.add('is-active');
                if (hideBtn) hideBtn.classList.remove('is-active');
            })();
            </script>
            </div>{{-- /dt-grid --}}

            {{-- ═══ FAILOVER LOG ════════════════════════════════════ --}}
            @if($failoverLogs->isNotEmpty())
            <div class="dt-card" id="data-failover" style="margin-top:12px;">
                <div class="dt-card-head">
                    <span class="dt-card-head__icon">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </span>
                    <span class="dt-card-head__title">Failover журнал</span>
                    <span class="dt-card-head__count">{{ $failoverLogs->total() }} записів</span>
                </div>
                <div class="dt-items">
                    @foreach($failoverLogs as $fl)
                    @php
                        $flPrimary = $fl->type === 'phone'
                            ? $site->phones->find($fl->primary_id)
                            : $site->socials->find($fl->primary_id);
                        $flStandby = $fl->type === 'phone'
                            ? $site->phones->find($fl->standby_id)
                            : $site->socials->find($fl->standby_id);
                        $flPrimaryLabel = $flPrimary
                            ? (($flPrimary->number ?? null) ? $flPrimary->number : ucfirst($flPrimary->platform ?? '').' '.$flPrimary->handle)
                            : '#'.$fl->primary_id;
                        $flStandbyLabel = $flStandby
                            ? (($flStandby->number ?? null) ? $flStandby->number : ucfirst($flStandby->platform ?? '').' '.$flStandby->handle)
                            : '#'.$fl->standby_id;
                    @endphp
                    <div class="dt-item" style="{{ $fl->rolled_back_at ? 'opacity:.55;' : '' }}">
                        <div class="dt-item-row" style="cursor:default;">
                            <span class="dt-item-icon" style="color:{{ $fl->rolled_back_at ? 'var(--text-3)' : 'var(--warning)' }};">
                                @if($fl->rolled_back_at)
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                                @endif
                            </span>
                            <div class="dt-item-main">
                                <div class="dt-item-name" style="font-size:12px;font-family:var(--font-mono);">
                                    {{ $flPrimaryLabel }} → {{ $flStandbyLabel }}
                                </div>
                                <div class="dt-item-sub">
                                    {{ $fl->trigger_reason }}
                                    &thinsp;·&thinsp;{{ $fl->triggered_by === 'api' ? 'API' : 'вручну' }}
                                    &thinsp;·&thinsp;{{ $fl->created_at->format('d.m H:i') }}
                                    @if($fl->rolled_back_at)&thinsp;·&thinsp;відкат {{ $fl->rolled_back_at->format('d.m H:i') }}@endif
                                </div>
                            </div>
                            @if(!$fl->rolled_back_at)
                            <div class="dt-item-actions" onclick="event.stopPropagation()">
                                <form method="POST" action="{{ route('sites.failover.rollback',[$site,$fl]) }}" style="margin:0;"
                                      onsubmit="return confirm('Відновити первинний запис і повернути резерв у пул?')">
                                    @csrf
                                    <button type="submit" class="btn btn--secondary btn--sm" style="font-size:11px;padding:3px 10px;">Відкат</button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @if($failoverLogs->hasPages())
                <div style="padding:10px 16px;border-top:1px solid var(--border-2);display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <span style="font-size:11px;color:var(--text-3);">{{ $failoverLogs->firstItem() }}–{{ $failoverLogs->lastItem() }} з {{ $failoverLogs->total() }}</span>
                    <div style="display:flex;gap:4px;">
                        @if($failoverLogs->onFirstPage())
                            <span class="btn btn--ghost btn--sm" style="opacity:.4;pointer-events:none;">←</span>
                        @else
                            <a href="{{ $failoverLogs->appends(request()->query())->previousPageUrl() }}" class="btn btn--ghost btn--sm">←</a>
                        @endif
                        @foreach($failoverLogs->getUrlRange(1, $failoverLogs->lastPage()) as $page => $pageUrl)
                            <a href="{{ $failoverLogs->appends(request()->query())->url($page) }}"
                               class="btn btn--sm {{ $page === $failoverLogs->currentPage() ? 'btn--primary' : 'btn--ghost' }}"
                               style="min-width:28px;justify-content:center;">{{ $page }}</a>
                        @endforeach
                        @if($failoverLogs->hasMorePages())
                            <a href="{{ $failoverLogs->appends(request()->query())->nextPageUrl() }}" class="btn btn--ghost btn--sm">→</a>
                        @else
                            <span class="btn btn--ghost btn--sm" style="opacity:.4;pointer-events:none;">→</span>
                        @endif
                    </div>
                </div>
                @endif
            </div>
            @endif

        @endif

        {{-- ========= ACTIVITY ========= --}}
        @if($tab === 'activity')
        @php
            $actIcons = [
                'phone'   => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.15 11a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.06 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>',
                'price'   => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
                'address' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
                'social'  => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
                'field'   => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>',
            ];
            $actLabels   = ['phone'=>'Телефон','price'=>'Ціна','address'=>'Адреса','social'=>'Соцмережа','field'=>'Поле'];
            $actionLabel = ['create'=>'додано','update'=>'оновлено','delete'=>'видалено'];
            $fieldLabels = ['number'=>'Номер','label'=>'Мітка','geo_mode'=>'Гео-правило','geo_countries'=>'Країни','is_primary'=>'Основний','is_visible'=>'Видимий','amount'=>'Сума','currency'=>'Валюта','city'=>'Місто','street'=>'Вулиця','region'=>'Регіон','country_iso'=>'Країна','platform'=>'Платформа','handle'=>'Handle','url'=>'URL','field_key'=>'Ключ','field_value'=>'Значення','dial_code'=>'Код'];
            $geoModes    = ['all'=>'Всім','include'=>'Тільки для','exclude'=>'Всім крім'];
            $skipFields  = ['id','site_id','group_id','created_at','updated_at','sort_order'];
            $tv          = fn($v) => is_array($v)
                ? implode(', ', array_map(fn($x) => $geoModes[$x] ?? $x, $v))
                : (is_bool($v) ? ($v ? 'Так' : 'Ні') : ($v === null ? '—' : ($geoModes[(string)$v] ?? (string)$v)));
        @endphp

        <div style="padding:0;">
            @forelse($activityLogs as $it)
            @php $hasDiff = !empty($it->snapshot['diff']) || !empty($it->snapshot['before']) || !empty($it->snapshot['after']); @endphp
            <div class="act-row act-row--{{ $it->action }} {{ $hasDiff ? '' : 'no-diff' }}" onclick="{{ $hasDiff ? 'actToggle(this)' : '' }}">
                <div class="act-row__icon act-row__icon--{{ $it->entity_type }}">
                    {!! $actIcons[$it->entity_type] ?? $actIcons['field'] !!}
                </div>
                <div class="act-row__body">
                    <span class="act-row__who">{{ $it->user?->name ?? 'Система' }}</span>
                    <span class="act-row__verb act-row__verb--{{ $it->action }}">{{ $actionLabel[$it->action] ?? $it->action }}</span>
                    <span class="act-row__summary">{{ $it->summary }}</span>
                </div>
                <div class="act-row__meta">
                    <span class="act-row__when" title="{{ $it->created_at->format('d.m.Y H:i') }}">{{ $it->created_at->diffForHumans() }}</span>
                    <span class="act-badge act-badge--{{ $it->entity_type }}">{{ $actLabels[$it->entity_type] ?? $it->entity_type }}</span>
                    @if($it->action === 'delete' && $it->snapshot)
                    <form method="POST" action="{{ route('sites.activity.restore', [$site, $it]) }}" style="margin:0;" onsubmit="event.stopPropagation();return confirm('Відновити запис?')">
                        @csrf
                        <button type="submit" class="btn btn--ghost btn--xs">Відновити</button>
                    </form>
                    @endif
                    @if($hasDiff)
                    <svg class="act-chevron" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="color:var(--text-3);transition:transform .15s;flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
                    @endif
                </div>
                @if($hasDiff)
                <div class="act-diff" style="grid-column:1/-1;" onclick="event.stopPropagation()">
                    @if(!empty($it->snapshot['diff']) && count($it->snapshot['diff']))
                        <div class="act-diff__grid">
                            <div class="act-diff__hdr">Параметр</div>
                            <div class="act-diff__hdr">Було</div>
                            <div class="act-diff__hdr">Стало</div>
                            @foreach($it->snapshot['diff'] as $field => $change)
                                <div class="act-diff__key">{{ $fieldLabels[$field] ?? $field }}</div>
                                <div class="act-diff__old">{{ $tv($change['before']) }}</div>
                                <div class="act-diff__new">{{ $tv($change['after']) }}</div>
                            @endforeach
                        </div>
                    @elseif($it->action === 'delete' && !empty($it->snapshot['before']))
                        <div class="act-diff__grid" style="grid-template-columns:140px 1fr;">
                            <div class="act-diff__hdr">Параметр</div>
                            <div class="act-diff__hdr">Значення</div>
                            @foreach($it->snapshot['before'] as $field => $value)
                                @if(!in_array($field, $skipFields) && $value !== null && $value !== '' && $value !== [])
                                <div class="act-diff__key">{{ $fieldLabels[$field] ?? $field }}</div>
                                <div class="act-diff__old" style="color:var(--text-2);">{{ $tv($value) }}</div>
                                @endif
                            @endforeach
                        </div>
                    @elseif($it->action === 'create' && !empty($it->snapshot['after']))
                        <div class="act-diff__grid" style="grid-template-columns:140px 1fr;">
                            <div class="act-diff__hdr">Параметр</div>
                            <div class="act-diff__hdr">Значення</div>
                            @foreach($it->snapshot['after'] as $field => $value)
                                @if(!in_array($field, $skipFields) && $value !== null && $value !== '' && $value !== [])
                                <div class="act-diff__key">{{ $fieldLabels[$field] ?? $field }}</div>
                                <div class="act-diff__new">{{ $tv($value) }}</div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif
            </div>
            @empty
            <div style="padding:48px 20px;text-align:center;color:var(--text-3);font-size:13px;">Активності ще немає</div>
            @endforelse

            @if($activityLogs->hasPages())
            <div style="padding:14px 20px;border-top:1px solid var(--border-2);">
                {{ $activityLogs->links() }}
            </div>
            @endif
        </div>
        @endif

        {{-- ========= SETTINGS ========= --}}
        @if($tab === 'settings')

            {{-- ===== Plugin Push Settings ===== --}}
            {{-- Hidden forms — siblings, not nested, to prevent _method bleed --}}
            <form id="form-push-settings" method="POST" action="{{ route('sites.push-settings.update', $site) }}" style="display:none;">
                @csrf @method('PUT')
            </form>
            <form id="form-site-delete" method="POST" action="{{ route('sites.destroy', $site) }}" onsubmit="return confirm('Видалити сайт «{{ $site->name }}»?')" style="display:none;">
                @csrf @method('DELETE')
            </form>
            @if($site->push_url && $site->push_key)
            <form id="form-site-sync" method="POST" action="{{ route('sites.sync', $site) }}" style="display:none;">
                @csrf
            </form>
            @endif

            <div style="padding:20px;display:flex;flex-direction:column;gap:0;">

                {{-- Section label --}}
                <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-3);padding-bottom:14px;border-bottom:1px solid var(--border-2);margin-bottom:4px;">
                    Підключення WordPress плагіна
                </div>

                {{-- Push URL --}}
                <div style="padding:14px 0;border-bottom:1px solid var(--border-2);">
                    <label style="font-size:12px;font-weight:600;color:var(--text-2);display:block;margin-bottom:6px;">
                        Webhook URL
                        <span style="font-weight:400;color:var(--text-3);margin-left:6px;">— скопіюйте з WP плагіна → Налаштування</span>
                    </label>
                    <div class="input input--mono" style="max-width:520px;">
                        <input type="url" name="push_url" form="form-push-settings"
                               placeholder="https://yoursite.com/wp-json/dbp/v1/sync"
                               value="{{ old('push_url', $site->push_url) }}" style="width:100%;">
                    </div>
                </div>

                {{-- Push Key --}}
                <div style="padding:14px 0;border-bottom:1px solid var(--border-2);">
                    <label style="font-size:12px;font-weight:600;color:var(--text-2);display:block;margin-bottom:6px;">
                        Sync Key
                        <span style="font-weight:400;color:var(--text-3);margin-left:6px;">— скопіюйте з WP плагіна → Налаштування</span>
                    </label>
                    <div class="input input--mono" style="max-width:520px;">
                        <input type="text" name="push_key" form="form-push-settings"
                               placeholder="64-символьний hex ключ"
                               value="{{ old('push_key', $site->push_key) }}" style="width:100%;">
                    </div>
                </div>

                {{-- Allow plugin edit toggle --}}
                <div style="padding:14px 0;border-bottom:1px solid var(--border-2);display:flex;align-items:center;justify-content:space-between;gap:16px;">
                    <div>
                        <div style="font-size:13px;font-weight:600;color:var(--text);">Редагування з плагіна</div>
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">Дозволити адміну WP редагувати дані локально або надсилати зміни назад</div>
                    </div>
                    <label style="position:relative;display:inline-flex;align-items:center;cursor:pointer;flex-shrink:0;">
                        <input type="checkbox" name="allow_plugin_edit" value="1" form="form-push-settings"
                               {{ $site->allow_plugin_edit ? 'checked' : '' }}
                               style="position:absolute;opacity:0;width:0;height:0;"
                               onchange="var tk=this.nextElementSibling;var th=tk.querySelector('span');if(this.checked){tk.style.background='var(--accent)';th.style.left='19px';}else{tk.style.background='var(--border)';th.style.left='3px';}">
                        <span class="toggle-track" style="width:38px;height:22px;background:{{ $site->allow_plugin_edit ? 'var(--accent)' : 'var(--border)' }};border-radius:11px;transition:.2s;display:block;position:relative;">
                            <span style="position:absolute;top:3px;left:{{ $site->allow_plugin_edit ? '19px' : '3px' }};width:16px;height:16px;background:#fff;border-radius:50%;transition:.2s;"></span>
                        </span>
                    </label>
                </div>

                @if($site->allow_plugin_edit && $site->plugin_edit_token)
                {{-- Callback URL info --}}
                <div style="padding:14px 0;border-bottom:1px solid var(--border-2);">
                    <label style="font-size:12px;font-weight:600;color:var(--text-2);display:block;margin-bottom:6px;">Callback URL <span style="font-weight:400;color:var(--text-3);">— надсилається до плагіна автоматично</span></label>
                    <div style="display:flex;align-items:center;gap:8px;max-width:580px;">
                        <div class="input input--mono" style="flex:1;">
                            <input type="text" readonly value="{{ config('app.url') }}/api/plugin-callback/{{ $site->plugin_edit_token }}" style="width:100%;color:var(--text-3);">
                        </div>
                        <button type="button" class="btn btn--secondary btn--sm"
                                onclick="navigator.clipboard.writeText('{{ config('app.url') }}/api/plugin-callback/{{ $site->plugin_edit_token }}')">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Status --}}
                <div style="padding:14px 0;border-bottom:1px solid var(--border-2);display:flex;align-items:center;gap:12px;">
                    @if($site->push_url && $site->push_key)
                        <span class="pill pill--success"><span class="dot dot--success"></span>Налаштовано</span>
                        <span style="font-size:12px;color:var(--text-3);">Push активний — дані оновлюються автоматично при кожній зміні</span>
                    @else
                        <span class="pill pill--neutral">Не налаштовано</span>
                        <span style="font-size:12px;color:var(--text-3);">Вставте Webhook URL і Sync Key з WP плагіна</span>
                    @endif
                </div>

                {{-- Footer --}}
                <div style="padding-top:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                    <div style="display:flex;gap:8px;">
                        <button type="submit" form="form-site-delete" class="btn btn--danger btn--md">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M6 7v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7"/></svg>
                            Видалити сайт
                        </button>
                        @if($failoverLogs->total() > 0)
                        <form method="POST" action="{{ route('sites.failover.history.clear', $site) }}"
                              onsubmit="return confirm('Видалити весь журнал failover ({{ $failoverLogs->total() }} записів)?')" style="margin:0;">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn--md" style="background:rgba(245,101,101,.08);color:var(--danger);border:1px solid rgba(245,101,101,.25);">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><line x1="18" y1="6" x2="23" y2="11"/><line x1="23" y1="6" x2="18" y2="11"/></svg>
                                Очистити журнал
                            </button>
                        </form>
                        @endif
                    </div>
                    <div style="display:flex;gap:8px;">
                        @if($site->push_url && $site->push_key)
                        <button type="submit" form="form-site-sync" class="btn btn--secondary btn--md">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg>
                            Синхронізувати
                        </button>
                        @endif
                        <button type="submit" form="form-push-settings" class="btn btn--primary btn--md">Зберегти</button>
                    </div>
                </div>

                {{-- [TEST] Failover signal simulator — видалити після інтеграції зовнішнього сервісу --}}
                <div class="dt-card" style="margin-top:24px;border:1px dashed var(--warning);">
                    <div class="dt-card-head" style="background:rgba(237,137,54,.06);">
                        <span class="dt-card-head__icon" style="color:var(--warning);">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </span>
                        <span class="dt-card-head__title">Симуляція сигналів failover</span>
                        <span style="font-size:10px;font-weight:600;color:var(--warning);background:rgba(237,137,54,.15);padding:2px 8px;border-radius:99px;letter-spacing:.04em;">TEMP · видалити після інтеграції</span>
                    </div>
                    <div style="padding:14px 16px;">
                        <p style="font-size:12px;color:var(--text-3);margin:0 0 12px;">
                            Імітуй сигнали що надходитимуть від зовнішнього сервісу моніторингу.<br>
                            <strong>BLOCK</strong> → активує наступний резерв у черзі (за sort_order).<br>
                            <strong>RESTORE</strong> → повертає оригінальний номер у пріоритет.
                        </p>
                        @php
                            $simAll      = $site->phones->keyBy('id');
                            $simFmt      = fn($p) => $p->number.($p->label ? ' · '.$p->label : '');
                            // True primaries: is_standby=false AND no standby_for_id (original primaries only)
                            $truePrimaries = $site->phones->filter(fn($p) => !$p->is_standby && !$p->standby_for_id)->sortBy('sort_order');
                            // All chain members (standbys + promoted) grouped by original primary id
                            $chainByPrimary = $site->phones->filter(fn($p) => $p->standby_for_id)->groupBy('standby_for_id');
                            // General pool: is_standby=true with no standby_for_id
                            $simPool = $site->phones->filter(fn($p) => $p->is_standby && !$p->standby_for_id)->sortBy('sort_order');
                        @endphp
                        @if($truePrimaries->isEmpty() && $simPool->isEmpty())
                        <p style="font-size:12px;color:var(--text-3);margin:0;">Немає телефонів для симуляції.</p>
                        @else
                        @foreach($truePrimaries as $sp)
                        @php
                            // Full chain: all records with standby_for_id=this primary, sorted by sort_order
                            $chain = $chainByPrimary->get($sp->id, collect())->sortBy('sort_order');
                            // Currently active replacement: promoted (is_standby=false), not blocked
                            $activeRepl = $chain->first(fn($r) => !$r->is_standby && !$r->is_blocked);
                            // Next standby in queue: still is_standby=true, not blocked
                            $nextWaiting = $chain->first(fn($r) => $r->is_standby && !$r->is_blocked);
                        @endphp
                        {{-- Primary row --}}
                        <div style="display:flex;align-items:center;gap:8px;padding:9px 0 7px;border-bottom:1px solid var(--border-2);">
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span style="font-family:var(--font-mono);font-size:12.5px;font-weight:{{ $sp->is_blocked ? '400' : '600' }};">{{ $simFmt($sp) }}</span>
                                    @if($chain->count() > 0)
                                        <span style="font-size:10px;background:rgba(102,119,255,.12);color:var(--accent);padding:1px 6px;border-radius:99px;white-space:nowrap;">{{ $chain->count() }} резерв{{ $chain->count() > 1 ? 'и' : '' }}</span>
                                    @endif
                                </div>
                            </div>
                            <span style="font-size:11px;white-space:nowrap;flex-shrink:0;{{ $sp->is_blocked ? 'color:var(--danger)' : 'color:var(--dot-ok)' }}">
                                {{ $sp->is_blocked ? '✕ заблок.' : '✓ активний' }}
                            </span>
                            @if(!$sp->is_blocked)
                            <form method="POST" action="{{ route('sites.failover.trigger',$site) }}" style="margin:0;flex-shrink:0;"
                                  onsubmit="return confirm('BLOCK: {{ addslashes($sp->number) }}')">
                                @csrf
                                <input type="hidden" name="type" value="phone">
                                <input type="hidden" name="primary_id" value="{{ $sp->id }}">
                                @if($nextWaiting)<input type="hidden" name="standby_id" value="{{ $nextWaiting->id }}">@endif
                                <input type="hidden" name="reason" value="[SIM] Block">
                                <button type="submit" class="btn btn--sm" style="background:rgba(245,101,101,.1);color:var(--danger);border:1px solid rgba(245,101,101,.25);">BLOCK</button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('sites.failover.restore',$site) }}" style="margin:0;flex-shrink:0;">
                                @csrf
                                <input type="hidden" name="type" value="phone">
                                <input type="hidden" name="primary_id" value="{{ $sp->id }}">
                                <button type="submit" class="btn btn--sm" style="background:rgba(72,187,120,.1);color:var(--dot-ok);border:1px solid rgba(72,187,120,.25);">RESTORE ALL</button>
                            </form>
                            @endif
                        </div>
                        {{-- Chain rows (all standbys + promoted, ordered by sort_order) --}}
                        @foreach($chain as $ssb)
                        @php
                            $isActive    = !$ssb->is_standby && !$ssb->is_blocked;   // currently replacing
                            $isBlocked   = $ssb->is_blocked;                           // was active, now blocked
                            $isWaiting   = $ssb->is_standby && !$ssb->is_blocked;     // still in queue
                        @endphp
                        <div style="display:flex;align-items:center;gap:8px;padding:5px 0 5px 18px;border-bottom:1px solid var(--border-2);{{ $isBlocked ? 'opacity:.55' : '' }}">
                            <span style="color:var(--border);font-size:13px;line-height:1;flex-shrink:0;">{{ $loop->last ? '└' : '├' }}</span>
                            <div style="flex:1;min-width:0;">
                                <span style="font-family:var(--font-mono);font-size:12px;">{{ $simFmt($ssb) }}</span>
                            </div>
                            <span style="font-size:10.5px;white-space:nowrap;flex-shrink:0;
                                @if($isBlocked) color:var(--danger);
                                @elseif($isActive) color:var(--dot-ok);font-weight:600;
                                @else color:var(--accent); @endif">
                                @if($isBlocked)✕ заблок.
                                @elseif($isActive)⟳ активний
                                @else⟳ #{{ $loop->iteration }}@endif
                            </span>
                            {{-- BLOCK on currently active replacement triggers cascade --}}
                            @if($isActive && $sp->is_blocked)
                            <form method="POST" action="{{ route('sites.failover.cascade',$site) }}" style="margin:0;flex-shrink:0;"
                                  onsubmit="return confirm('BLOCK активний резерв: {{ addslashes($ssb->number) }}?')">
                                @csrf
                                <input type="hidden" name="type" value="phone">
                                <input type="hidden" name="active_id" value="{{ $ssb->id }}">
                                <button type="submit" class="btn btn--sm" style="background:rgba(245,101,101,.1);color:var(--danger);border:1px solid rgba(245,101,101,.25);">BLOCK</button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                        @endforeach
                        {{-- General pool --}}
                        @if($simPool->isNotEmpty())
                        <div style="padding:8px 0 3px;font-size:10px;color:var(--text-3);font-weight:600;letter-spacing:.05em;text-transform:uppercase;">Загальний пул</div>
                        @foreach($simPool as $sp)
                        <div style="display:flex;align-items:center;gap:8px;padding:5px 0;border-bottom:1px solid var(--border-2);opacity:.65;">
                            <div style="flex:1;font-family:var(--font-mono);font-size:12px;">{{ $simFmt($sp) }}</div>
                            <span style="font-size:10.5px;color:var(--text-3);">не закріплений</span>
                        </div>
                        @endforeach
                        @endif
                        @endif
                    </div>
                </div>
                {{-- [/TEST] --}}

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
        // Quick pick: all ISOs used across the system, excluding already added ones
        $systemIsos = \App\Models\Site::whereNotNull('active_geos')
            ->pluck('active_geos')
            ->flatMap(fn($g) => array_keys(is_array($g) ? $g : (array)$g))
            ->merge(['UA','RO','RU','BY','PL','DE','MD'])
            ->unique()->sort()->values()->toArray();
        $availableQuick = array_values(array_filter($systemIsos, fn($iso) => !in_array($iso, $usedIso, true)));
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

// ── Data sub-tabs (Contacts / Details) ────────────────────────
function dtSubTab(group) {
    ['contacts','details'].forEach(function(g) {
        var el  = document.getElementById('dt-group-' + g);
        var btn = document.getElementById('dst-' + g);
        if (!el || !btn) return;
        var active = g === group;
        el.style.display  = active ? '' : 'none';
        btn.classList.toggle('is-active', active);
    });
    try { sessionStorage.setItem('dtSubTab', group); } catch(e){}
}

(function() {
    // Update badge counts
    var c = (document.getElementById('dt-group-contacts')?.querySelectorAll('.dt-item').length || 0);
    var d = (document.getElementById('dt-group-details')?.querySelectorAll('.dt-item').length || 0);
    var cc = document.getElementById('dst-contacts-count');
    var dc = document.getElementById('dst-details-count');
    if (cc && c > 0) cc.textContent = c;
    if (dc && d > 0) dc.textContent = d;
})();

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

    var item  = panel.closest ? (panel.closest('.dt-nav-child') || panel.closest('.dt-nav-group') || panel.closest('.dt-item')) : null;
    var items = panel.closest ? (panel.closest('.dt-nav-list') || panel.closest('.dt-items')) : null;
    if (item && items) {
        if (!open) {
            item.classList.add('is-editing');
            items.classList.add('has-edit');
        } else {
            item.classList.remove('is-editing');
            if (!items.querySelector('.is-editing')) {
                items.classList.remove('has-edit');
            }
        }
    }
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
    if (!hash || hash.indexOf('dt-edit-') === -1) return;
    var id = hash.replace('#dt-edit-', '');
    var panel = document.getElementById('dt-edit-' + id);
    if (!panel) return;
    // Open via dtExpandItem so dimming + chevron are handled correctly
    dtExpandItem(id);
    // Flash highlight so user sees where they landed
    var item = panel.closest ? (panel.closest('.dt-nav-child') || panel.closest('.dt-nav-group') || panel.closest('.dt-item')) : null;
    if (item) {
        item.classList.add('hash-opened');
        setTimeout(function() { item.classList.remove('hash-opened'); }, 2000);
    }
    setTimeout(function() {
        panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 80);
    // Clean hash without reloading
    history.replaceState(null, '', window.location.pathname + window.location.search);
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
    try { sessionStorage.setItem('visPanel_{{ $site->id }}', iso); } catch(e){}
}

// ── Activity diff toggle ───────────────────────────────────────────────────
function actToggle(row) {
    var diff = row.querySelector('.act-diff');
    var chev = row.querySelector('.act-chevron');
    if (!diff) return;
    var open = row.classList.toggle('is-open');
    if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
}

// ── Favorite toggle ──────────────────────────────────────────────────────
var _isFav = {{ $isFavorite ? 'true' : 'false' }};
function toggleFavorite() {
    var btn  = document.getElementById('fav-btn');
    var icon = document.getElementById('fav-icon');
    fetch('{{ route('sites.favorite', $site) }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
    }).then(function(r){ return r.json(); }).then(function(data){
        _isFav = data.favorite;
        icon.setAttribute('fill', _isFav ? '#f6ad55' : 'none');
        icon.setAttribute('stroke', _isFav ? '#f6ad55' : 'currentColor');
        btn.style.color = _isFav ? '#f6ad55' : 'var(--text-3)';
        btn.title = _isFav ? 'Прибрати з улюблених' : 'Додати до улюблених';
    });
}

// ── Site presence heartbeat ───────────────────────────────────────────────
(function() {
    var presenceUrl  = '{{ route("sites.presence", $site) }}';
    var csrfToken    = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    var banner       = document.getElementById('presence-banner');
    var bannerText   = document.getElementById('presence-text');

    function ping() {
        fetch(presenceUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var others = data.others || [];
            if (!banner) return;
            if (others.length === 0) {
                banner.style.display = 'none';
            } else {
                var names = others.map(function(o) { return o.name; }).join(', ');
                bannerText.textContent = names + ' зараз тут — редагування може конфліктувати';
                banner.style.display = 'flex';
            }
        })
        .catch(function() {});
    }

    ping();
    setInterval(ping, 60000);
})();


// ── Pointer-based gesture: swipe ←→ = standby toggle, drag ↕ = reorder ──
(function () {
    var CSRF        = '{{ csrf_token() }}';
    var URL_REORDER = {
        phone:  '{{ route('phones.reorder', $site) }}',
        social: '{{ route('socials.reorder', $site) }}',
    };
    var URL_STANDBY = '{{ route('sites.failover.standby', $site) }}';
    var URL_LINK    = '{{ route('sites.failover.link', $site) }}';
    var SWIPE_THRESHOLD = 60;

    function postJSON(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify(data),
        });
    }

    function refreshPrimaryBadge(list, primaryId, delta) {
        if (!primaryId) return;
        var primaryEl = list.querySelector('.dt-item[data-id="' + primaryId + '"]');
        if (!primaryEl) return;
        var count = Math.max(0, (parseInt(primaryEl.dataset.sbCount) || 0) + delta);
        primaryEl.dataset.sbCount = count;
        var badge = primaryEl.querySelector('.dt-nav-sb-badge');
        if (count > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'dt-nav-sb-badge';
                var vis = primaryEl.querySelector('.dt-item-row .dt-vis');
                if (vis) vis.before(badge); else primaryEl.querySelector('.dt-item-row').appendChild(badge);
            }
            badge.textContent = count + ' ⟳';
            primaryEl.dataset.hasStandbys = '1';
            var grip = primaryEl.querySelector('.dt-nav-grip');
            if (grip) grip.title = 'Має резервних — не можна зробити резервним';
        } else {
            if (badge) badge.remove();
            primaryEl.dataset.hasStandbys = '0';
            var grip = primaryEl.querySelector('.dt-nav-grip');
            if (grip) grip.title = 'Потягни вправо = резерв, вліво = основний';
        }
    }

    function applyStandbyToggle(item, isNowStandby) {
        // Close edit panel if open
        var panel = item.querySelector('.dt-panel');
        if (panel) panel.style.display = 'none';
        var chevronBtn = item.querySelector('[id^="dt-expand-"]');
        if (chevronBtn) { var cSvg = chevronBtn.querySelector('svg'); if (cSvg) cSvg.style.transform = ''; item.classList.remove('is-editing'); }
        var list2 = item.closest('.dt-nav-list');
        if (list2 && !list2.querySelector('.dt-item.is-editing')) list2.classList.remove('has-edit');

        // Update parent primary's badge before changing isStandby state
        if (isNowStandby) {
            // Becoming standby — find nearest primary above in DOM
            var nearestPrimary = null;
            var el2 = item.previousElementSibling;
            while (el2) {
                if (el2.classList.contains('dt-item') && el2.dataset.isStandby === '0') { nearestPrimary = el2; break; }
                el2 = el2.previousElementSibling;
            }
            if (nearestPrimary) {
                refreshPrimaryBadge(list2, parseInt(nearestPrimary.dataset.id), +1);
                item.dataset.parentId = nearestPrimary.dataset.id;
                // Persist standby_for_id immediately after swipe (drag saves it via updateStandbyParent, swipe must do it here)
                postJSON(URL_LINK, { type: item.dataset.type, id: parseInt(item.dataset.id), standby_for_id: parseInt(nearestPrimary.dataset.id) });
            }
        } else {
            // Leaving standby — toggleStandby already clears standby_for_id in DB
            var oldParentId2 = item.dataset.parentId ? parseInt(item.dataset.parentId) : null;
            if (oldParentId2) refreshPrimaryBadge(list2, oldParentId2, -1);
            item.dataset.parentId = '';
        }

        item.dataset.isStandby = isNowStandby ? '1' : '0';
        item.classList.toggle('dt-item--root', !isNowStandby);
        item.classList.toggle('dt-item--child', isNowStandby);

        var row = item.querySelector('.dt-item-row');
        if (row) row.classList.toggle('dt-nav-primary', !isNowStandby);

        // Standby badge
        var main = item.querySelector('.dt-item-main');
        if (main) {
            if (isNowStandby) {
                var sub = main.querySelector('.dt-item-sub');
                if (!sub) { sub = document.createElement('div'); sub.className = 'dt-item-sub'; main.appendChild(sub); }
                if (!sub.querySelector('.dt-badge--standby')) {
                    var b = document.createElement('span');
                    b.className = 'dt-badge dt-badge--standby'; b.textContent = '⟳ резерв';
                    sub.insertBefore(b, sub.firstChild);
                }
            } else {
                var sbadge = main.querySelector('.dt-badge--standby');
                if (sbadge) sbadge.remove();
            }
        }

        // Standby toggle button
        var actions = item.querySelector('.dt-item-actions');
        if (actions) {
            var standbyBtn = actions.querySelector('button[title="Зробити резервним"], button[title="Зняти з резерву"]');
            if (standbyBtn) {
                standbyBtn.title = isNowStandby ? 'Зняти з резерву' : 'Зробити резервним';
                standbyBtn.style.color = isNowStandby ? 'var(--accent)' : 'var(--text-3)';
            }
        }

    }

    function sendReorder(list, type) {
        var items = Array.from(list.querySelectorAll(':scope > .dt-item'))
            .filter(function (el) { return !el.classList.contains('dnd-placeholder'); })
            .map(function (el, i) { return { id: parseInt(el.dataset.id), sort_order: i }; });
        postJSON(URL_REORDER[type], { items: items });
    }

    function updateStandbyParent(item, type) {
        var newParentId = null;
        var el = item.previousElementSibling;
        while (el) {
            if (el.classList.contains('dt-item') && el.dataset.isStandby === '0') {
                newParentId = parseInt(el.dataset.id);
                break;
            }
            el = el.previousElementSibling;
        }
        var currentParentId = item.dataset.parentId ? parseInt(item.dataset.parentId) : null;
        if (newParentId === currentParentId) return;
        var list = item.closest('.dt-nav-list');
        if (currentParentId) refreshPrimaryBadge(list, currentParentId, -1);
        if (newParentId)     refreshPrimaryBadge(list, newParentId,     +1);
        item.dataset.parentId = newParentId || '';
        postJSON(URL_LINK, { type: type, id: parseInt(item.dataset.id), standby_for_id: newParentId });
    }

    document.querySelectorAll('.dt-nav-list').forEach(function (list) {
        var type = list.dataset.type;
        var s = { active: false, item: null, ph: null, mode: null, sx: 0, sy: 0, pid: null };

        function cleanup() {
            if (s.item) {
                s.item.style.transform = '';
                s.item.style.transition = '';
                s.item.classList.remove('is-dragging', 'swipe-right', 'swipe-left');
            }
            if (s.ph) { s.ph.remove(); s.ph = null; }
            if (s.pid !== null) { try { list.releasePointerCapture(s.pid); } catch (e) {} }
            s.active = false; s.item = null; s.mode = null; s.pid = null;
        }

        list.addEventListener('pointerdown', function (e) {
            if (!e.target.closest('.dt-nav-grip')) return;
            var item = e.target.closest('.dt-item[data-id]');
            if (!item || !list.contains(item)) return;
            e.preventDefault();
            list.setPointerCapture(e.pointerId);
            s.active = true; s.item = item; s.mode = null;
            s.sx = e.clientX; s.sy = e.clientY; s.pid = e.pointerId;
            item.style.transition = 'none';
        });

        list.addEventListener('pointermove', function (e) {
            if (!s.active || !s.item) return;
            var dx = e.clientX - s.sx, dy = e.clientY - s.sy;
            var adx = Math.abs(dx), ady = Math.abs(dy);

            if (!s.mode) {
                if (adx > 10 && adx > ady * 1.2)      s.mode = 'swipe';
                else if (ady > 10 && ady > adx * 1.2) { s.mode = 'reorder'; s.item.classList.add('is-dragging'); }
            }

            if (s.mode === 'swipe') {
                var clamped = Math.max(-130, Math.min(130, dx));
                s.item.style.transform = 'translateX(' + clamped + 'px)';
                s.item.classList.toggle('swipe-right', dx > 40);
                s.item.classList.toggle('swipe-left',  dx < -40);
            }

            if (s.mode === 'reorder') {
                if (s.ph) s.ph.remove();
                s.ph = document.createElement('div');
                s.ph.className = 'dnd-placeholder' + (s.item.classList.contains('dt-item--child') ? ' dnd-placeholder--child' : '');
                var items = Array.from(list.querySelectorAll(':scope > .dt-item')).filter(function (el) { return el !== s.item; });
                var before = null;
                for (var i = 0; i < items.length; i++) {
                    var r = items[i].getBoundingClientRect();
                    if (e.clientY < r.top + r.height / 2) { before = items[i]; break; }
                }
                if (before) list.insertBefore(s.ph, before); else list.appendChild(s.ph);
            }
        });

        list.addEventListener('pointerup', function (e) {
            if (!s.active || !s.item) return;
            var dx = e.clientX - s.sx;
            var item = s.item, mode = s.mode;
            var isStandby = item.dataset.isStandby === '1';

            if (mode === 'swipe') {
                var hasStandbys = item.dataset.hasStandbys === '1';
                var triggered = (dx > SWIPE_THRESHOLD && !isStandby && !hasStandbys) || (dx < -SWIPE_THRESHOLD && isStandby);
                if (triggered) {
                    item.style.opacity = '.4';
                    postJSON(URL_STANDBY, { type: type, id: parseInt(item.dataset.id) })
                        .then(function (r) {
                            if (!r.ok) { item.style.opacity = ''; item.style.transform = ''; return null; }
                            return r.json();
                        })
                        .then(function (data) {
                            if (!data) return;
                            item.style.opacity = '';
                            item.style.transform = '';
                            applyStandbyToggle(item, !!data.is_standby);
                        })
                        .catch(function () { location.reload(); });
                } else {
                    item.style.transition = 'transform .2s cubic-bezier(.4,0,.2,1)';
                    item.style.transform  = '';
                    setTimeout(function () { item.style.transition = ''; }, 220);
                }
                item.classList.remove('swipe-right', 'swipe-left');
            }

            if (mode === 'reorder' && s.ph) {
                list.insertBefore(item, s.ph);
                s.ph.remove(); s.ph = null;
                sendReorder(list, type);
                if (item.dataset.isStandby === '1') {
                    updateStandbyParent(item, type);
                }
            }

            cleanup();
        });

        list.addEventListener('pointercancel', cleanup);
    });
}());

// Custom messenger platform support
var _showMsCustomPlatforms = {!! json_encode(\App\Models\CustomPlatform::messengerOptions()) !!};

function onShowMsPlatformChange(sel) {
    var wrap = sel.parentElement;
    var inp  = wrap.querySelector('.show-ms-custom-inp');
    var isNew = sel.value === '__new__';
    if (inp) { inp.style.display = isNew ? '' : 'none'; inp.required = isNew; }
}

function _showMsCreatePlatform(form, callback) {
    var sel = form.querySelector('.show-ms-platform-sel');
    if (!sel || sel.value !== '__new__') { callback(); return; }
    var inp  = form.querySelector('.show-ms-custom-inp');
    var label = inp ? inp.value.trim() : '';
    if (!label) { inp && inp.focus(); return; }
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    fetch('{{ route("custom-platforms.store") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        body: JSON.stringify({label: label, category: 'messenger'})
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        document.querySelectorAll('.show-ms-platform-sel').forEach(function(s) {
            if (!s.querySelector('option[value="'+data.slug+'"]')) {
                var opt = document.createElement('option');
                opt.value = data.slug; opt.textContent = data.label;
                s.insertBefore(opt, s.querySelector('option[value="__new__"]'));
            }
        });
        sel.value = data.slug;
        inp.style.display = 'none'; inp.required = false;
        callback();
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[data-ms-form]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var sel = form.querySelector('.show-ms-platform-sel');
            if (sel && sel.value === '__new__') {
                e.preventDefault();
                _showMsCreatePlatform(form, function(){ form.submit(); });
            }
        });
    });
});
</script>
@endpush
