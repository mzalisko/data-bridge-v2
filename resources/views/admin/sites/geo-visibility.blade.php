@extends('layouts.app')

@section('title', 'Видимість — ' . $site->name)

@push('styles')
<style>
/* ── Geo Visibility page ────────────────────────────────── */
.gv-tabs { display:flex; gap:2px; border-bottom:1px solid var(--border); margin-bottom:0; }
.gv-tab {
    padding:10px 18px; font-size:13px; font-weight:500;
    color:var(--text-3); border:0; background:transparent;
    cursor:pointer; border-bottom:2px solid transparent;
    margin-bottom:-1px; text-decoration:none;
    transition: color .12s, border-color .12s;
    display:inline-flex; align-items:center; gap:7px;
}
.gv-tab:hover { color:var(--text); }
.gv-tab.is-active { color:var(--accent); border-bottom-color:var(--accent); font-weight:600; }

/* ── Filter bar ─────────────────────────────────────────── */
.gv-filter { display:flex; gap:6px; align-items:center; flex-wrap:wrap; }
.gv-filter-btn {
    padding:4px 12px; font-size:12px; font-family:var(--font-mono);
    font-weight:600; border:1px solid var(--border);
    border-radius:99px; background:var(--panel-2);
    color:var(--text-2); cursor:pointer; text-decoration:none;
    transition: background .12s, color .12s, border-color .12s;
}
.gv-filter-btn:hover { background:var(--accent-2); color:var(--accent-text); border-color:var(--accent-2); }
.gv-filter-btn.is-active { background:var(--accent); color:#fff; border-color:var(--accent); }

/* ── Data section ───────────────────────────────────────── */
.gv-section { background:var(--panel); border:1px solid var(--border); border-radius:var(--radius-lg); overflow:hidden; }
.gv-section-head {
    display:flex; align-items:center; gap:10px;
    padding:14px 18px; border-bottom:1px solid var(--border);
    background:var(--panel-2);
}
.gv-section-head__icon { color:var(--text-3); flex-shrink:0; display:flex; }
.gv-section-head__title { font-size:13px; font-weight:600; color:var(--text); flex:1; }
.gv-section-head__count {
    font-size:11px; font-weight:600; font-family:var(--font-mono);
    background:var(--border); color:var(--text-3);
    border-radius:99px; padding:1px 7px;
}

/* ── Item row ───────────────────────────────────────────── */
.gv-item { border-bottom:1px solid var(--border-2); }
.gv-item:last-child { border-bottom:0; }
.gv-item-row {
    display:flex; align-items:center; gap:10px;
    padding:10px 18px; min-height:46px;
}
.gv-item-main { flex:1; min-width:0; }
.gv-item-name { font-size:13px; font-weight:500; color:var(--text); }
.gv-item-sub  { font-size:11px; color:var(--text-3); margin-top:1px; font-family:var(--font-mono); }

/* ── Visibility badges ──────────────────────────────────── */
.vis-badges { display:flex; gap:4px; flex-wrap:wrap; }
.vis-badge {
    display:inline-flex; align-items:center; gap:3px;
    padding:2px 7px; border-radius:99px;
    font-size:10px; font-weight:700; font-family:var(--font-mono);
    border:1px solid transparent;
}
.vis-badge--ok  { background:var(--success-bg); color:var(--success); border-color:var(--success-bg); }
.vis-badge--no  { background:var(--danger-bg);  color:var(--danger);  border-color:var(--danger-bg);  }
.vis-badge--all { background:var(--panel-2);    color:var(--text-3);  border-color:var(--border); }

/* ── Expand toggle ──────────────────────────────────────── */
.gv-expand-btn {
    background:transparent; border:0; cursor:pointer; padding:5px; border-radius:6px;
    color:var(--text-3); display:inline-flex; align-items:center;
    transition: background .12s, color .12s, transform .15s;
}
.gv-expand-btn:hover { background:var(--panel-2); color:var(--text); }
.gv-expand-btn.is-open { color:var(--accent); transform: rotate(90deg); }

/* ── Inline form panel ──────────────────────────────────── */
.gv-item-form {
    padding:16px 18px 18px;
    border-top:1px solid var(--border-2);
    background:var(--panel-2);
}

/* ── Add form card ──────────────────────────────────────── */
.gv-add-form {
    padding:16px 18px 18px;
    border-top:1px dashed var(--border);
    background:var(--panel);
}
.gv-add-toggle {
    width:100%; display:flex; align-items:center; justify-content:center; gap:6px;
    padding:10px; border:1px dashed var(--border); border-radius:var(--radius);
    background:transparent; color:var(--text-3); font-size:12px; font-weight:500;
    cursor:pointer; transition: border-color .12s, color .12s, background .12s;
}
.gv-add-toggle:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-2); }

/* ── Overview widget ────────────────────────────────────── */
.gv-widget {
    background:var(--panel-2); border:1px solid var(--border);
    border-radius:var(--radius-lg); padding:20px 24px;
    max-width:480px;
}
.gv-widget__label {
    font-size:11px; font-weight:600; color:var(--text-3);
    text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px;
}
.gv-widget-section { margin-bottom:18px; }
.gv-widget-section:last-child { margin-bottom:0; }
.gv-widget-section__title {
    font-size:11px; font-weight:600; color:var(--text-3);
    text-transform:uppercase; letter-spacing:.04em;
    margin-bottom:8px; display:flex; align-items:center; gap:6px;
}
.gv-widget-row { display:flex; align-items:center; gap:8px; padding:4px 0; font-size:13px; color:var(--text); }
.gv-widget-row__icon { color:var(--text-3); flex-shrink:0; }
.gv-widget-empty { font-size:12px; color:var(--text-3); font-style:italic; }

/* ── Matrix table ───────────────────────────────────────── */
.gv-matrix { width:100%; border-collapse:collapse; }
.gv-matrix th, .gv-matrix td {
    padding:10px 14px; border-bottom:1px solid var(--border-2);
    text-align:left; font-size:13px;
}
.gv-matrix th {
    font-size:11px; font-weight:600; color:var(--text-3);
    text-transform:uppercase; letter-spacing:.05em;
    background:var(--panel-2); font-family:var(--font-mono);
    text-align:center;
}
.gv-matrix th:first-child { text-align:left; font-family:var(--font-sans); }
.gv-matrix td:not(:first-child) { text-align:center; }
.gv-matrix tr:hover td { background:var(--panel-2); }
.gv-matrix-group td {
    font-size:11px; font-weight:700; color:var(--text-3);
    text-transform:uppercase; letter-spacing:.05em;
    background:var(--bg); padding:8px 14px;
    border-bottom:1px solid var(--border);
}
.gv-matrix-group td svg { margin-right:5px; vertical-align:middle; }
.gv-vis-ok  { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:var(--success-bg); }
.gv-vis-ok  svg { color:var(--success); }
.gv-vis-no  { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:var(--danger-bg); }
.gv-vis-no  svg { color:var(--danger); }
</style>
@endpush

@section('content')
@php
    $gtab      = in_array(request('gtab'), ['data','overview','matrix']) ? request('gtab') : 'data';
    $filterIso = in_array(request('filter'), $usedIso) ? request('filter') : 'all';
    $visitorIso = in_array(request('vis'), $usedIso) ? request('vis') : ($usedIso[0] ?? null);

    $tabUrl = fn($t) => route('sites.geo-visibility', $site) . '?' . http_build_query(['gtab' => $t]);
    $filterUrl = fn($f) => route('sites.geo-visibility', $site) . '?' . http_build_query(['gtab' => 'data', 'filter' => $f]);
    $visUrl = fn($v) => route('sites.geo-visibility', $site) . '?' . http_build_query(['gtab' => 'overview', 'vis' => $v]);

    $socialIcon = [
        'instagram' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1" fill="currentColor"/></svg>',
        'facebook'  => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.6-1.5h1.5V4.4c-.3 0-1.2-.1-2.3-.1-2.3 0-3.8 1.4-3.8 3.9v2.2H8v3h2.5V21h3z"/></svg>',
        'telegram'  => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M21 4 2.5 11.5c-.7.3-.7 1.3 0 1.5l4.5 1.4 1.7 5.4c.2.6 1 .8 1.4.3l2.5-2.7 4.7 3.4c.5.4 1.3.1 1.5-.5L22 5c.2-.7-.5-1.3-1-1zM9.7 14.7l-.4 4 1.7-2.4 4.6-5.5-5.9 3.9z"/></svg>',
        'linkedin'  => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M5 4.5A1.7 1.7 0 1 1 5 8a1.7 1.7 0 0 1 0-3.5zM3.5 9.5h3v11h-3v-11zM9 9.5h2.9v1.6c.4-.8 1.5-1.8 3.2-1.8 3.4 0 4 2.2 4 5.1v6.1h-3v-5.4c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.5H9v-11z"/></svg>',
        'x'         => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M17.5 3h3l-6.6 7.6L21.5 21h-6l-4.4-5.8L6 21H3l7-8.1L2.5 3h6.1l4 5.4L17.5 3z"/></svg>',
        'whatsapp'  => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3.5 20.5 4.8 16A8 8 0 1 1 8 19.4l-4.5 1.1z"/></svg>',
        'viber'     => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M5 4h11a3 3 0 0 1 3 3v6a3 3 0 0 1-3 3h-2l-3 3v-3H7a2 2 0 0 1-2-2V4z"/></svg>',
        'youtube'   => '<svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><path d="M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8C22 15.2 22 12 22 12s0-3.2-.4-4.8zM10 15V9l5 3-5 3z"/></svg>',
    ];
    $socialColor = [
        'instagram'=>'#c2185b','facebook'=>'#1877f2','telegram'=>'#229ed9',
        'linkedin'=>'#0a66c2','x'=>'var(--text-2)','whatsapp'=>'#25d366',
        'viber'=>'#7360f2','youtube'=>'#ff0000',
    ];
    $socialLabel = [
        'instagram'=>'Instagram','facebook'=>'Facebook','telegram'=>'Telegram',
        'linkedin'=>'LinkedIn','x'=>'X / Twitter','whatsapp'=>'WhatsApp',
        'viber'=>'Viber','youtube'=>'YouTube',
    ];

    // Visibility helper
    $geoVis = function ($geoMode, $geoCountries, $iso): bool {
        $mode   = $geoMode ?? 'all';
        $ctries = (array)($geoCountries ?? []);
        return match($mode) {
            'include' => in_array($iso, $ctries),
            'exclude' => !in_array($iso, $ctries),
            default   => true,
        };
    };

    // Filter items for "Дані" tab
    $phones    = $site->phones;
    $prices    = $site->prices;
    $socials   = $site->socials;
    $addresses = $site->addresses;

    if ($filterIso !== 'all') {
        $filterFn = fn($col) => $col->filter(fn($i) => $geoVis($i->geo_mode, $i->geo_countries, $filterIso))->values();
        $phones    = $filterFn($phones);
        $prices    = $filterFn($prices);
        $socials   = $filterFn($socials);
        $addresses = $filterFn($addresses);
    }

    // Conflicts: items with different visibility per ISO (not "all" for everyone)
    $hasConflict = fn($item) => ($item->geo_mode ?? 'all') !== 'all';

    $visRuleOptions = $usedIso;
@endphp

<div class="page-stack">

{{-- ===== PAGE HEAD ===== --}}
<div class="page-head">
    <div>
        <div class="page-head__crumb">
            <a href="{{ route('sites.index') }}">Сайти</a> /
            <a href="{{ route('sites.show', $site) }}">{{ $site->name }}</a> /
            <span style="color:var(--text);">Видимість</span>
        </div>
        <h1 class="page-head__title">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z"/>
            </svg>
            Гео-видимість
        </h1>
        <p class="page-head__subtitle">{{ $site->name }} — правила та матриця</p>
    </div>
    <div class="page-head__actions">
        <a href="{{ route('sites.show', $site) }}?tab=settings" class="btn btn--secondary btn--md">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            Налаштування гео
        </a>
    </div>
</div>

{{-- ===== ACTIVE GEOS ===== --}}
@if(count($usedIso) === 0)
<div class="card" style="text-align:center;padding:32px;color:var(--text-3);">
    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;color:var(--muted);">
        <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><circle cx="12" cy="16" r=".5" fill="currentColor"/>
    </svg>
    <div style="font-size:14px;font-weight:600;margin-bottom:6px;">Немає активних гео</div>
    <div style="font-size:12px;">Спочатку додайте країни у вкладці <a href="{{ route('sites.show', $site) }}?tab=settings" style="color:var(--accent);">Налаштування</a></div>
</div>
@else

{{-- ===== TAB NAV ===== --}}
<div class="gv-tabs">
    <a href="{{ $tabUrl('data') }}" class="gv-tab {{ $gtab==='data' ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
        </svg>
        Дані
    </a>
    <a href="{{ $tabUrl('overview') }}" class="gv-tab {{ $gtab==='overview' ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z"/>
        </svg>
        Overview
    </a>
    <a href="{{ $tabUrl('matrix') }}" class="gv-tab {{ $gtab==='matrix' ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"/>
        </svg>
        Матриця
    </a>
</div>


{{-- ═══════════════════════════════════════════════════════════
     TAB: ДАНІ
═══════════════════════════════════════════════════════════ --}}
@if($gtab === 'data')

{{-- Filter bar --}}
<div class="gv-filter">
    <span style="font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Показати для:</span>
    <a href="{{ $filterUrl('all') }}" class="gv-filter-btn {{ $filterIso==='all' ? 'is-active' : '' }}">Всі</a>
    @foreach($usedIso as $iso)
        <a href="{{ $filterUrl($iso) }}" class="gv-filter-btn {{ $filterIso===$iso ? 'is-active' : '' }}">{{ $iso }}</a>
    @endforeach
    <a href="{{ route('sites.geo-visibility', $site) }}?gtab=data&filter=conflict" class="gv-filter-btn {{ request('filter')==='conflict' ? 'is-active' : '' }}" style="font-family:var(--font-sans);">
        <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.2" style="display:inline-block;">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><path d="M12 9v4"/><circle cx="12" cy="17" r=".5" fill="currentColor"/>
        </svg>
        Конфлікти
    </a>
</div>

<div class="page-stack">

{{-- ─── PHONES ─────────────────────────────────────────── --}}
<div class="gv-section">
    <div class="gv-section-head">
        <span class="gv-section-head__icon">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
        </span>
        <span class="gv-section-head__title">Телефони</span>
        <span class="gv-section-head__count">{{ $phones->count() }}</span>
        <button class="btn btn--ghost btn--sm" onclick="toggleAddForm('add-phone')">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Додати
        </button>
    </div>

    @forelse($phones as $phone)
        @php
            $pid = 'phone-' . $phone->id;
            $label = $phone->label ? $phone->label . ' — ' : '';
            $number = ($phone->dial_code ? '+' . $phone->dial_code . ' ' : '') . $phone->number;
        @endphp
        <div class="gv-item" id="item-{{ $pid }}">
            <div class="gv-item-row">
                <button class="gv-expand-btn" id="btn-{{ $pid }}" onclick="gvToggle('{{ $pid }}')">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
                <div class="gv-item-main">
                    <div class="gv-item-name">{{ $label }}{{ $number }}</div>
                    @if($phone->is_primary)
                        <div class="gv-item-sub">Основний</div>
                    @endif
                </div>
                <div class="vis-badges">
                    @if(count($usedIso) === 0 || ($phone->geo_mode ?? 'all') === 'all')
                        <span class="vis-badge vis-badge--all">
                            <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/></svg>
                            Всі
                        </span>
                    @else
                        @foreach($usedIso as $iso)
                            @php $ok = $geoVis($phone->geo_mode, $phone->geo_countries, $iso); @endphp
                            <span class="vis-badge vis-badge--{{ $ok ? 'ok' : 'no' }}">
                                @if($ok)
                                    <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                @else
                                    <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                @endif
                                {{ $iso }}
                            </span>
                        @endforeach
                    @endif
                </div>
                <div style="display:flex;gap:4px;">
                    <form method="POST" action="{{ route('phones.destroy', [$site, $phone]) }}" onsubmit="return confirm('Видалити?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn" title="Видалити">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            {{-- Edit form (collapsed) --}}
            <div class="gv-item-form" id="form-{{ $pid }}" style="display:none;">
                <form method="POST" action="{{ route('phones.update', [$site, $phone]) }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-phone', ['phone' => $phone])
                    <div style="display:flex;gap:8px;margin-top:14px;">
                        <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                        <button type="button" class="btn btn--ghost btn--sm" onclick="gvToggle('{{ $pid }}')">Скасувати</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div style="padding:20px 18px;color:var(--text-3);font-size:12px;">Немає телефонів</div>
    @endforelse

    {{-- Add form --}}
    <div id="add-phone" style="display:none;" class="gv-add-form">
        <div style="font-size:12px;font-weight:600;color:var(--text-2);margin-bottom:12px;">Новий телефон</div>
        <form method="POST" action="{{ route('phones.store', $site) }}">
            @csrf
            @include('admin.sites._form-phone', ['phone' => null])
            <div style="display:flex;gap:8px;margin-top:14px;">
                <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                <button type="button" class="btn btn--ghost btn--sm" onclick="toggleAddForm('add-phone')">Скасувати</button>
            </div>
        </form>
    </div>
    <button class="gv-add-toggle" onclick="toggleAddForm('add-phone')" id="add-phone-btn">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Додати телефон
    </button>
</div>

{{-- ─── PRICES ─────────────────────────────────────────── --}}
<div class="gv-section">
    <div class="gv-section-head">
        <span class="gv-section-head__icon">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </span>
        <span class="gv-section-head__title">Ціни</span>
        <span class="gv-section-head__count">{{ $prices->count() }}</span>
        <button class="btn btn--ghost btn--sm" onclick="toggleAddForm('add-price')">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Додати
        </button>
    </div>

    @forelse($prices as $price)
        @php $prid = 'price-' . $price->id; @endphp
        <div class="gv-item" id="item-{{ $prid }}">
            <div class="gv-item-row">
                <button class="gv-expand-btn" id="btn-{{ $prid }}" onclick="gvToggle('{{ $prid }}')">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
                <div class="gv-item-main">
                    <div class="gv-item-name">{{ $price->label }}</div>
                    <div class="gv-item-sub">{{ number_format($price->amount, 2) }} {{ $price->currency }}{{ $price->period ? ' / '.$price->period : '' }}</div>
                </div>
                <div class="vis-badges">
                    @if(count($usedIso) === 0 || ($price->geo_mode ?? 'all') === 'all')
                        <span class="vis-badge vis-badge--all">
                            <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/></svg>
                            Всі
                        </span>
                    @else
                        @foreach($usedIso as $iso)
                            @php $ok = $geoVis($price->geo_mode, $price->geo_countries, $iso); @endphp
                            <span class="vis-badge vis-badge--{{ $ok ? 'ok' : 'no' }}">
                                @if($ok)<svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                @else<svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                @endif
                                {{ $iso }}
                            </span>
                        @endforeach
                    @endif
                </div>
                <div style="display:flex;gap:4px;">
                    <form method="POST" action="{{ route('prices.destroy', [$site, $price]) }}" onsubmit="return confirm('Видалити?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn" title="Видалити">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <div class="gv-item-form" id="form-{{ $prid }}" style="display:none;">
                <form method="POST" action="{{ route('prices.update', [$site, $price]) }}">
                    @csrf @method('PUT')
                    @include('admin.sites._form-price', ['price' => $price])
                    <div style="display:flex;gap:8px;margin-top:14px;">
                        <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                        <button type="button" class="btn btn--ghost btn--sm" onclick="gvToggle('{{ $prid }}')">Скасувати</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div style="padding:20px 18px;color:var(--text-3);font-size:12px;">Немає цін</div>
    @endforelse

    <div id="add-price" style="display:none;" class="gv-add-form">
        <div style="font-size:12px;font-weight:600;color:var(--text-2);margin-bottom:12px;">Нова ціна</div>
        <form method="POST" action="{{ route('prices.store', $site) }}">
            @csrf
            @include('admin.sites._form-price', ['price' => null])
            <div style="display:flex;gap:8px;margin-top:14px;">
                <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                <button type="button" class="btn btn--ghost btn--sm" onclick="toggleAddForm('add-price')">Скасувати</button>
            </div>
        </form>
    </div>
    <button class="gv-add-toggle" onclick="toggleAddForm('add-price')" id="add-price-btn">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Додати ціну
    </button>
</div>

{{-- ─── SOCIALS ─────────────────────────────────────────── --}}
<div class="gv-section">
    <div class="gv-section-head">
        <span class="gv-section-head__icon">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
            </svg>
        </span>
        <span class="gv-section-head__title">Соціальні мережі</span>
        <span class="gv-section-head__count">{{ $socials->count() }}</span>
        <button class="btn btn--ghost btn--sm" onclick="toggleAddForm('add-social')">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Додати
        </button>
    </div>

    @forelse($socials as $social)
        @php $sid = 'social-' . $social->id; @endphp
        <div class="gv-item" id="item-{{ $sid }}">
            <div class="gv-item-row">
                <button class="gv-expand-btn" id="btn-{{ $sid }}" onclick="gvToggle('{{ $sid }}')">
                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
                <span style="color:{{ $socialColor[$social->platform] ?? 'var(--text-3)' }};display:flex;flex-shrink:0;">{!! $socialIcon[$social->platform] ?? '' !!}</span>
                <div class="gv-item-main">
                    <div class="gv-item-name">{{ $socialLabel[$social->platform] ?? $social->platform }}</div>
                    <div class="gv-item-sub">{{ $social->handle }}</div>
                </div>
                @if($social->phone)
                    <span class="pill pill--neutral" style="font-size:10px;gap:4px;">
                        <svg viewBox="0 0 24 24" width="9" height="9" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        {{ $social->phone->number }}
                    </span>
                @endif
                <div class="vis-badges">
                    @if(count($usedIso) === 0 || ($social->geo_mode ?? 'all') === 'all')
                        <span class="vis-badge vis-badge--all">
                            <svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="9"/></svg>
                            Всі
                        </span>
                    @else
                        @foreach($usedIso as $iso)
                            @php $ok = $geoVis($social->geo_mode, $social->geo_countries, $iso); @endphp
                            <span class="vis-badge vis-badge--{{ $ok ? 'ok' : 'no' }}">
                                @if($ok)<svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                @else<svg viewBox="0 0 24 24" width="8" height="8" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                @endif
                                {{ $iso }}
                            </span>
                        @endforeach
                    @endif
                </div>
                <div style="display:flex;gap:4px;">
                    <form method="POST" action="{{ route('socials.destroy', [$site, $social]) }}" onsubmit="return confirm('Видалити?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="icon-btn" title="Видалити">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            <div class="gv-item-form" id="form-{{ $sid }}" style="display:none;">
                <form method="POST" action="{{ route('socials.update', [$site, $social]) }}">
                    @csrf @method('PUT')
                    {{-- phone link selector --}}
                    @if($site->phones->count() > 0)
                    <div class="field" style="margin-bottom:12px;">
                        <label class="field__label">Прив'язаний номер (необов'язково)</label>
                        <select name="phone_id" class="field__input">
                            <option value="">— не прив'язано —</option>
                            @foreach($site->phones as $ph)
                                <option value="{{ $ph->id }}" {{ $social->phone_id == $ph->id ? 'selected' : '' }}>
                                    {{ ($ph->dial_code ? '+'.$ph->dial_code.' ' : '') . $ph->number }}{{ $ph->label ? ' ('.$ph->label.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    @include('admin.sites._form-social', ['social' => $social])
                    <div style="display:flex;gap:8px;margin-top:14px;">
                        <button type="submit" class="btn btn--primary btn--sm">Зберегти</button>
                        <button type="button" class="btn btn--ghost btn--sm" onclick="gvToggle('{{ $sid }}')">Скасувати</button>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div style="padding:20px 18px;color:var(--text-3);font-size:12px;">Немає соціальних мереж</div>
    @endforelse

    <div id="add-social" style="display:none;" class="gv-add-form">
        <div style="font-size:12px;font-weight:600;color:var(--text-2);margin-bottom:12px;">Нова соціальна мережа</div>
        <form method="POST" action="{{ route('socials.store', $site) }}">
            @csrf
            @if($site->phones->count() > 0)
            <div class="field" style="margin-bottom:12px;">
                <label class="field__label">Прив'язаний номер (необов'язково)</label>
                <select name="phone_id" class="field__input">
                    <option value="">— не прив'язано —</option>
                    @foreach($site->phones as $ph)
                        <option value="{{ $ph->id }}">{{ ($ph->dial_code ? '+'.$ph->dial_code.' ' : '') . $ph->number }}{{ $ph->label ? ' ('.$ph->label.')' : '' }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @include('admin.sites._form-social', ['social' => null])
            <div style="display:flex;gap:8px;margin-top:14px;">
                <button type="submit" class="btn btn--primary btn--sm">Додати</button>
                <button type="button" class="btn btn--ghost btn--sm" onclick="toggleAddForm('add-social')">Скасувати</button>
            </div>
        </form>
    </div>
    <button class="gv-add-toggle" onclick="toggleAddForm('add-social')" id="add-social-btn">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        Додати соціальну мережу
    </button>
</div>

</div>{{-- /page-stack --}}
@endif{{-- /data tab --}}


{{-- ═══════════════════════════════════════════════════════════
     TAB: OVERVIEW
═══════════════════════════════════════════════════════════ --}}
@if($gtab === 'overview')
<div>
    {{-- Visitor selector --}}
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
        <span style="font-size:12px;color:var(--text-3);font-weight:600;">Відвідувач з:</span>
        @foreach($usedIso as $iso)
            <a href="{{ $visUrl($iso) }}"
               class="gv-filter-btn {{ $visitorIso===$iso ? 'is-active' : '' }}"
               style="display:inline-flex;align-items:center;gap:5px;">
                <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                {{ $iso }}{{ isset($geoNames[$iso]) && $geoNames[$iso] ? ' — '.$geoNames[$iso] : '' }}
            </a>
        @endforeach
    </div>

    @if($visitorIso)
    @php
        $visPhones    = $site->phones->filter(fn($i) => $geoVis($i->geo_mode, $i->geo_countries, $visitorIso))->values();
        $visPrices    = $site->prices->filter(fn($i) => $geoVis($i->geo_mode, $i->geo_countries, $visitorIso))->values();
        $visSocials   = $site->socials->filter(fn($i) => $geoVis($i->geo_mode, $i->geo_countries, $visitorIso))->values();
        $totalVisible = $visPhones->count() + $visPrices->count() + $visSocials->count();
        $totalAll     = $site->phones->count() + $site->prices->count() + $site->socials->count();
    @endphp

    <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

        {{-- Widget preview --}}
        <div class="gv-widget">
            <div class="gv-widget__label">
                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="3"/><path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7z"/></svg>
                Бачить відвідувач з {{ $visitorIso }}
            </div>

            {{-- Phones --}}
            @if($visPhones->count() > 0)
            <div class="gv-widget-section">
                <div class="gv-widget-section__title">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Телефони
                </div>
                @foreach($visPhones as $ph)
                    <div class="gv-widget-row">
                        <span class="gv-widget-row__icon">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <span style="font-family:var(--font-mono);font-size:13px;">{{ ($ph->dial_code ? '+'.$ph->dial_code.' ' : '') . $ph->number }}</span>
                        @if($ph->label)<span style="font-size:11px;color:var(--text-3);">{{ $ph->label }}</span>@endif
                        @if($ph->is_primary)<span class="pill pill--accent" style="font-size:10px;">Основний</span>@endif
                    </div>
                    {{-- Linked socials --}}
                    @php $linkedSocials = $visSocials->where('phone_id', $ph->id); @endphp
                    @foreach($linkedSocials as $ls)
                        <div class="gv-widget-row" style="padding-left:20px;">
                            <span style="color:{{ $socialColor[$ls->platform] ?? 'var(--text-3)' }};display:flex;">{!! $socialIcon[$ls->platform] ?? '' !!}</span>
                            <span style="font-size:12px;color:var(--text-2);">{{ $ls->handle }}</span>
                        </div>
                    @endforeach
                @endforeach
            </div>
            @endif

            {{-- Prices --}}
            @if($visPrices->count() > 0)
            <div class="gv-widget-section">
                <div class="gv-widget-section__title">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Ціни
                </div>
                @foreach($visPrices as $pr)
                    <div class="gv-widget-row">
                        <span class="gv-widget-row__icon">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                        </span>
                        <span style="font-weight:600;">{{ number_format($pr->amount, 0) }} {{ $pr->currency }}</span>
                        @if($pr->period)<span style="font-size:11px;color:var(--text-3);">/ {{ $pr->period }}</span>@endif
                        <span style="font-size:12px;color:var(--text-2);">{{ $pr->label }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Socials (not linked to phone) --}}
            @php $freeSocials = $visSocials->whereNull('phone_id')->values(); @endphp
            @if($freeSocials->count() > 0)
            <div class="gv-widget-section">
                <div class="gv-widget-section__title">
                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    Соціальні мережі
                </div>
                @foreach($freeSocials as $sc)
                    <div class="gv-widget-row">
                        <span style="color:{{ $socialColor[$sc->platform] ?? 'var(--text-3)' }};display:flex;">{!! $socialIcon[$sc->platform] ?? '' !!}</span>
                        <span style="font-size:12px;color:var(--text-2);">{{ $sc->handle }}</span>
                    </div>
                @endforeach
            </div>
            @endif

            @if($totalVisible === 0)
                <div class="gv-widget-empty">Цей відвідувач не бачить жодних даних</div>
            @endif
        </div>

        {{-- Stats panel --}}
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div class="card" style="padding:16px 18px;">
                <div style="font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Покриття для {{ $visitorIso }}</div>
                <div style="font-size:28px;font-weight:700;color:var(--text);">{{ $totalAll > 0 ? round($totalVisible / $totalAll * 100) : 0 }}%</div>
                <div style="font-size:12px;color:var(--text-3);margin-top:4px;">{{ $totalVisible }} з {{ $totalAll }} елементів</div>
                <div style="height:6px;background:var(--border);border-radius:99px;margin-top:10px;overflow:hidden;">
                    <div style="height:100%;background:var(--success);border-radius:99px;width:{{ $totalAll > 0 ? round($totalVisible / $totalAll * 100) : 0 }}%;"></div>
                </div>
            </div>
            <div class="card" style="padding:16px 18px;">
                <div style="font-size:11px;color:var(--text-3);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:10px;">Деталі</div>
                @foreach([['Телефони', $visPhones->count(), $site->phones->count()], ['Ціни', $visPrices->count(), $site->prices->count()], ['Соціальні', $visSocials->count(), $site->socials->count()]] as [$lbl, $vis, $all])
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <span style="font-size:12px;color:var(--text-2);">{{ $lbl }}</span>
                        <div style="display:flex;align-items:center;gap:6px;">
                            @if($vis === $all)
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:var(--success-bg);">
                                    <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="var(--success)" stroke-width="3" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                            @elseif($vis === 0)
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:var(--danger-bg);">
                                    <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="var(--danger)" stroke-width="3" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:50%;background:var(--warning-bg);">
                                    <svg viewBox="0 0 24 24" width="10" height="10" fill="none" stroke="var(--warning)" stroke-width="2.5" stroke-linecap="round"><path d="M12 9v4"/><circle cx="12" cy="17" r=".5" fill="var(--warning)"/></svg>
                                </span>
                            @endif
                            <span style="font-size:12px;font-family:var(--font-mono);font-weight:600;color:var(--text);">{{ $vis }}/{{ $all }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endif{{-- /overview tab --}}


{{-- ═══════════════════════════════════════════════════════════
     TAB: МАТРИЦЯ
═══════════════════════════════════════════════════════════ --}}
@if($gtab === 'matrix')
<div class="card card--flush" style="overflow:hidden;">
    <table class="gv-matrix">
        <thead>
            <tr>
                <th style="width:240px;">Елемент</th>
                @foreach($usedIso as $iso)
                    <th style="width:80px;">{{ $iso }}</th>
                @endforeach
                <th style="width:100px;font-family:var(--font-sans);">Режим</th>
            </tr>
        </thead>
        <tbody>

        {{-- Phones --}}
        <tr class="gv-matrix-group">
            <td colspan="{{ count($usedIso) + 2 }}">
                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="display:inline-block;vertical-align:middle;margin-bottom:1px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Телефони
            </td>
        </tr>
        @forelse($site->phones as $phone)
            <tr>
                <td>
                    <div style="font-size:13px;font-weight:500;font-family:var(--font-mono);">
                        {{ ($phone->dial_code ? '+'.$phone->dial_code.' ' : '') . $phone->number }}
                    </div>
                    @if($phone->label)<div style="font-size:11px;color:var(--text-3);">{{ $phone->label }}</div>@endif
                </td>
                @foreach($usedIso as $iso)
                    <td>
                        @php $ok = $geoVis($phone->geo_mode, $phone->geo_countries, $iso); @endphp
                        @if(($phone->geo_mode ?? 'all') === 'all')
                            <span class="gv-vis-ok"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        @elseif($ok)
                            <span class="gv-vis-ok"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        @else
                            <span class="gv-vis-no"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></span>
                        @endif
                    </td>
                @endforeach
                <td>
                    @php $mode = $phone->geo_mode ?? 'all'; @endphp
                    @if($mode === 'all')
                        <span class="pill pill--neutral" style="font-size:10px;">Всі</span>
                    @elseif($mode === 'include')
                        <span class="pill pill--success" style="font-size:10px;">Тільки для</span>
                    @else
                        <span class="pill pill--warning" style="font-size:10px;">Всім крім</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($usedIso) + 2 }}" style="color:var(--text-3);font-size:12px;font-style:italic;">—</td></tr>
        @endforelse

        {{-- Prices --}}
        <tr class="gv-matrix-group">
            <td colspan="{{ count($usedIso) + 2 }}">
                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="display:inline-block;vertical-align:middle;margin-bottom:1px;"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Ціни
            </td>
        </tr>
        @forelse($site->prices as $price)
            <tr>
                <td>
                    <div style="font-size:13px;font-weight:500;">{{ $price->label }}</div>
                    <div style="font-size:11px;color:var(--text-3);font-family:var(--font-mono);">{{ number_format($price->amount, 0) }} {{ $price->currency }}{{ $price->period ? ' / '.$price->period : '' }}</div>
                </td>
                @foreach($usedIso as $iso)
                    <td>
                        @php $ok = $geoVis($price->geo_mode, $price->geo_countries, $iso); @endphp
                        @if(($price->geo_mode ?? 'all') === 'all')
                            <span class="gv-vis-ok"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        @elseif($ok)
                            <span class="gv-vis-ok"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        @else
                            <span class="gv-vis-no"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></span>
                        @endif
                    </td>
                @endforeach
                <td>
                    @php $mode = $price->geo_mode ?? 'all'; @endphp
                    @if($mode === 'all')
                        <span class="pill pill--neutral" style="font-size:10px;">Всі</span>
                    @elseif($mode === 'include')
                        <span class="pill pill--success" style="font-size:10px;">Тільки для</span>
                    @else
                        <span class="pill pill--warning" style="font-size:10px;">Всім крім</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($usedIso) + 2 }}" style="color:var(--text-3);font-size:12px;font-style:italic;">—</td></tr>
        @endforelse

        {{-- Socials --}}
        <tr class="gv-matrix-group">
            <td colspan="{{ count($usedIso) + 2 }}">
                <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="display:inline-block;vertical-align:middle;margin-bottom:1px;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                Соціальні мережі
            </td>
        </tr>
        @forelse($site->socials as $social)
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <span style="color:{{ $socialColor[$social->platform] ?? 'var(--text-3)' }};display:flex;">{!! $socialIcon[$social->platform] ?? '' !!}</span>
                        <span style="font-size:13px;font-weight:500;">{{ $socialLabel[$social->platform] ?? $social->platform }}</span>
                    </div>
                    <div style="font-size:11px;color:var(--text-3);">{{ $social->handle }}</div>
                </td>
                @foreach($usedIso as $iso)
                    <td>
                        @php $ok = $geoVis($social->geo_mode, $social->geo_countries, $iso); @endphp
                        @if(($social->geo_mode ?? 'all') === 'all')
                            <span class="gv-vis-ok"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        @elseif($ok)
                            <span class="gv-vis-ok"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                        @else
                            <span class="gv-vis-no"><svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg></span>
                        @endif
                    </td>
                @endforeach
                <td>
                    @php $mode = $social->geo_mode ?? 'all'; @endphp
                    @if($mode === 'all')
                        <span class="pill pill--neutral" style="font-size:10px;">Всі</span>
                    @elseif($mode === 'include')
                        <span class="pill pill--success" style="font-size:10px;">Тільки для</span>
                    @else
                        <span class="pill pill--warning" style="font-size:10px;">Всім крім</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="{{ count($usedIso) + 2 }}" style="color:var(--text-3);font-size:12px;font-style:italic;">—</td></tr>
        @endforelse

        </tbody>
    </table>
</div>
@endif{{-- /matrix tab --}}

@endif{{-- /has usedIso --}}
</div>{{-- /page-stack --}}

@push('styles')
<style>
/* Rule editor JS functions output for this page */
.field__label { display:block; font-size:12px; font-weight:500; color:var(--text-2); margin-bottom:5px; }
.field__input { width:100%; padding:7px 10px; border:1px solid var(--border); border-radius:var(--radius); background:var(--panel); color:var(--text); font-size:13px; outline:none; transition: border-color .12s; }
.field__input:focus { border-color:var(--accent); }
.field { margin-bottom:12px; }
</style>
@endpush

@push('scripts')
<script>
function gvToggle(id) {
    var form = document.getElementById('form-' + id);
    var btn  = document.getElementById('btn-'  + id);
    if (!form) return;
    var open = form.style.display !== 'none';
    form.style.display = open ? 'none' : 'block';
    if (btn) btn.classList.toggle('is-open', !open);
}

function toggleAddForm(id) {
    var form = document.getElementById(id);
    var toggleBtn = document.getElementById(id + '-btn');
    if (!form) return;
    var open = form.style.display !== 'none';
    form.style.display = open ? 'none' : 'block';
    if (toggleBtn) toggleBtn.style.display = open ? 'flex' : 'none';
}

function ruleEditorToggle(prefix, mode) {
    var countries = document.getElementById(prefix + '-countries');
    var modes = ['all','include','exclude'];
    modes.forEach(function(m) {
        var lbl = document.getElementById(prefix + '-mode-lbl-' + m);
        if (!lbl) return;
        if (m === mode) {
            lbl.style.background = 'var(--accent)';
            lbl.style.color = '#fff';
            lbl.style.borderColor = 'var(--accent)';
            lbl.style.fontWeight = '600';
        } else {
            lbl.style.background = 'var(--panel-2)';
            lbl.style.color = 'var(--text-2)';
            lbl.style.borderColor = 'var(--border)';
            lbl.style.fontWeight = '400';
        }
    });
    if (countries) {
        countries.style.display = (mode === 'include' || mode === 'exclude') ? 'block' : 'none';
    }
}

function ruleChipToggle(prefix, iso, el) {
    var chip = document.getElementById(prefix + '-chip-' + iso);
    if (!chip) return;
    if (el.checked) {
        chip.style.background = 'var(--accent-2)';
        chip.style.color = 'var(--accent-text)';
        chip.style.borderColor = 'var(--accent-2)';
    } else {
        chip.style.background = 'var(--panel-2)';
        chip.style.color = 'var(--text-2)';
        chip.style.borderColor = 'var(--border)';
    }
}
</script>
@endpush

@endsection
