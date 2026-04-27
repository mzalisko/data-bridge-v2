@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/sites.css') }}?v={{ filemtime(public_path('assets/css/pages/sites.css')) }}">
@endpush

@section('title', $site->name)

@section('content')

@php
    $color    = $site->siteGroup?->color ?? '#708499';
    $letter   = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
    $syncLog  = $site->latestSyncLog;
    $syncOk   = $syncLog?->status === 'ok' || $syncLog?->status === 'no_changes';
    $syncErr  = $syncLog?->status === 'error';
    $syncColor = $syncOk ? 'var(--success)' : ($syncErr ? 'var(--danger)' : 'var(--text-3)');
@endphp

{{-- ── Breadcrumb toolbar ── --}}
<div class="page-toolbar">
    <div style="display:flex;align-items:center;gap:var(--space-md);">
        <a href="{{ route('sites.index') }}" class="btn--ghost btn--ghost-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
            Сайти
        </a>
        <span style="color:var(--border);">/</span>
        <span class="page-title" style="font-size:16px;">{{ $site->name }}</span>
    </div>
    <div style="display:flex;gap:var(--space-sm);">
        <a href="{{ $site->url }}" target="_blank" class="btn--ghost">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Відкрити
        </a>
        <button class="btn--primary" onclick="openDrawer('drawer-site-edit')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Редагувати
        </button>
    </div>
</div>

{{-- ── Site hero ── --}}
<div class="site-hero">
    <div class="site-hero__favicon" style="background:{{ $color }}22;color:{{ $color }};">{{ $letter }}</div>

    <div class="site-hero__main">
        <div class="site-hero__name">
            {{ $site->name }}
            <span class="site-hero__status-badge site-hero__status-badge--{{ $site->is_active ? 'active' : 'off' }}">
                <span class="site-hero__status-dot"></span>
                {{ $site->is_active ? 'Active' : 'Disabled' }}
            </span>
        </div>
        <div class="site-hero__url">
            <a href="{{ $site->url }}" target="_blank" style="color:inherit;text-decoration:none;">{{ $site->url }}</a>
        </div>
    </div>

    <div class="site-hero__meta">
        @if($site->siteGroup)
        <div class="site-hero__meta-item">
            <span class="site-hero__meta-label">Група</span>
            <span class="group-pill" style="--pill-color:{{ $color }}">{{ $site->siteGroup->name }}</span>
        </div>
        @endif
        @if($syncLog)
        <div class="site-hero__meta-item">
            <span class="site-hero__meta-label">Sync</span>
            <span class="site-hero__meta-val" style="color:{{ $syncColor }}">
                ● {{ $syncLog->synced_at->diffForHumans() }}
            </span>
        </div>
        @endif
        <div class="site-hero__meta-item">
            <span class="site-hero__meta-label">Додано</span>
            <span class="site-hero__meta-val">{{ $site->created_at->format('d.m.Y') }}</span>
        </div>
    </div>

    {{-- API Key block (compact) --}}
    <div class="site-hero__api">
        @include('admin.sites._api-key', ['site' => $site])
    </div>
</div>

{{-- ── Mini-stat bar ── --}}
<div class="site-mini-stats">
    <a href="{{ route('sites.show', $site) }}?tab=phones"
       class="site-mini-stat {{ $tab === 'phones' ? 'site-mini-stat--active' : '' }}">
        <span class="site-mini-stat__icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.72-.72a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.5 16a2 2 0 0 1 .42.92z"/></svg>
        </span>
        <span class="site-mini-stat__val">{{ $site->phones->count() }}</span>
        <span class="site-mini-stat__label">Телефони</span>
    </a>
    <a href="{{ route('sites.show', $site) }}?tab=prices"
       class="site-mini-stat {{ $tab === 'prices' ? 'site-mini-stat--active' : '' }}">
        <span class="site-mini-stat__icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </span>
        <span class="site-mini-stat__val">{{ $site->prices->count() }}</span>
        <span class="site-mini-stat__label">Ціни</span>
    </a>
    <a href="{{ route('sites.show', $site) }}?tab=addresses"
       class="site-mini-stat {{ $tab === 'addresses' ? 'site-mini-stat--active' : '' }}">
        <span class="site-mini-stat__icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        </span>
        <span class="site-mini-stat__val">{{ $site->addresses->count() }}</span>
        <span class="site-mini-stat__label">Адреси</span>
    </a>
    <a href="{{ route('sites.show', $site) }}?tab=socials"
       class="site-mini-stat {{ $tab === 'socials' ? 'site-mini-stat--active' : '' }}">
        <span class="site-mini-stat__icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        </span>
        <span class="site-mini-stat__val">{{ $site->socials->count() }}</span>
        <span class="site-mini-stat__label">Соцмережі</span>
    </a>
    <a href="{{ route('sites.show', $site) }}?tab=custom_fields"
       class="site-mini-stat {{ $tab === 'custom_fields' ? 'site-mini-stat--active' : '' }}">
        <span class="site-mini-stat__icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
        </span>
        <span class="site-mini-stat__val">{{ $site->customFields->count() }}</span>
        <span class="site-mini-stat__label">Кастом</span>
    </a>
    <a href="{{ route('sites.show', $site) }}?tab=logs"
       class="site-mini-stat {{ $tab === 'logs' ? 'site-mini-stat--active' : '' }}">
        <span class="site-mini-stat__icon">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </span>
        <span class="site-mini-stat__label">Sync-лог</span>
    </a>
</div>

{{-- ── Content card ── --}}
<div class="site-content-card">
    @if($tab === 'phones')
        @include('admin.sites._tab-phones',    ['site' => $site, 'phones'    => $site->phones,    'countries' => $countries])
    @elseif($tab === 'prices')
        @include('admin.sites._tab-prices',    ['site' => $site, 'prices'    => $site->prices,    'countries' => $countries])
    @elseif($tab === 'addresses')
        @include('admin.sites._tab-addresses', ['site' => $site, 'addresses' => $site->addresses, 'countries' => $countries])
    @elseif($tab === 'socials')
        @include('admin.sites._tab-socials',   ['site' => $site, 'socials'   => $site->socials,   'countries' => $countries])
    @elseif($tab === 'custom_fields')
        @include('admin.sites._tab-custom-fields', ['site' => $site, 'customFields' => $site->customFields])
    @elseif($tab === 'logs')
        @include('admin.sites._tab-logs', ['syncLogs' => $syncLogs, 'logStatus' => $logStatus, 'site' => $site])
    @endif
</div>

{{-- Edit drawer --}}
<div class="drawer-overlay" id="drawer-site-edit-overlay" onclick="closeDrawer('drawer-site-edit')"></div>
<div class="drawer" id="drawer-site-edit">
    <div class="drawer__header">
        <span class="drawer__title">{{ $site->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-edit')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.update', $site) }}" class="form-stack" id="form-site-edit">
            @csrf @method('PUT')
            @include('admin.sites._form', ['site' => $site, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('sites.destroy', $site) }}" class="drawer__footer-left">
            @csrf @method('DELETE')
            <button type="submit" class="btn--danger"
                    onclick="return confirm('Видалити сайт «{{ $site->name }}»?')">Видалити</button>
        </form>
        <button type="button" class="btn--ghost" onclick="closeDrawer('drawer-site-edit')">Скасувати</button>
        <button type="submit" form="form-site-edit" class="btn--primary">Зберегти</button>
    </div>
</div>

@endsection
