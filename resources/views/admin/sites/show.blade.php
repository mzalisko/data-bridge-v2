@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/sites.css') }}?v={{ filemtime(public_path('assets/css/pages/sites.css')) }}">
@endpush

@section('title', $site->name)

@section('content')

<div class="page-toolbar">
    <div style="display:flex;align-items:center;gap:var(--space-md);">
        <a href="{{ route('sites.index') }}" class="btn-ghost">← Сайти</a>
        <span class="status-dot status-dot--{{ $site->is_active ? 'ok' : 'off' }}"></span>
        <h1 class="page-title">{{ $site->name }}</h1>
    </div>
    <div style="display:flex;gap:var(--space-sm);">
        <a href="{{ $site->url }}" target="_blank" class="btn-ghost">↗ Відкрити</a>
        <button class="btn-primary" onclick="openDrawer('drawer-site-edit')">Редагувати</button>
    </div>
</div>

@php
    $color  = $site->siteGroup?->color ?? '#708499';
    $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
    $syncLog  = $site->latestSyncLog;
    $syncOk   = $syncLog?->status === 'success';
    $syncWarn = $syncLog && !$syncOk;
    $syncColor = $syncOk ? 'var(--dot-ok)' : ($syncWarn ? 'var(--dot-pause)' : 'var(--text-muted)');
@endphp

<div class="site-show">
    {{-- Sidebar --}}
    <div class="site-show__sidebar">
        <div class="site-show__favicon"
             style="background:{{ $color }}26;color:{{ $color }};">
            {{ $letter }}
        </div>
        <div>
            <div class="site-show__name">{{ $site->name }}</div>
            <div class="site-show__url">{{ $site->url }}</div>
        </div>

        <div class="site-show__info">
            <div class="site-show__info-row">
                <span class="site-show__info-label">Статус</span>
                <span class="site-show__info-val"
                      style="color:{{ $site->is_active ? 'var(--dot-ok)' : 'var(--dot-off)' }}">
                    ● {{ $site->is_active ? 'Active' : 'Disabled' }}
                </span>
            </div>
            <div class="site-show__info-row">
                <span class="site-show__info-label">Група</span>
                <span class="site-show__info-val">{{ $site->siteGroup?->name ?? '—' }}</span>
            </div>
            @if($syncLog)
            <div class="site-show__info-row">
                <span class="site-show__info-label">Sync</span>
                <span class="site-show__info-val" style="color:{{ $syncColor }}">
                    {{ $syncLog->created_at->diffForHumans() }}
                </span>
            </div>
            @endif
            <div class="site-show__info-row">
                <span class="site-show__info-label">Додано</span>
                <span class="site-show__info-val">{{ $site->created_at->format('d.m.Y') }}</span>
            </div>
        </div>

        @include('admin.sites._api-key', ['site' => $site])

        {{-- Navigation --}}
        <nav class="site-show__nav">
            <a class="site-show__nav-item {{ $tab === 'phones' ? 'is-active' : '' }}"
               href="{{ route('sites.show', $site) }}?tab=phones">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.72-.72a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.5 16a2 2 0 0 1 .42.92z"/>
                </svg>
                Телефони
                <span class="site-show__nav-count">{{ $site->phones->count() ?: '—' }}</span>
            </a>
            <a class="site-show__nav-item {{ $tab === 'prices' ? 'is-active' : '' }}"
               href="{{ route('sites.show', $site) }}?tab=prices">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                Ціни
                <span class="site-show__nav-count">{{ $site->prices->count() ?: '—' }}</span>
            </a>
            <a class="site-show__nav-item {{ $tab === 'addresses' ? 'is-active' : '' }}"
               href="{{ route('sites.show', $site) }}?tab=addresses">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Адреси
                <span class="site-show__nav-count">{{ $site->addresses->count() ?: '—' }}</span>
            </a>
            <a class="site-show__nav-item {{ $tab === 'socials' ? 'is-active' : '' }}"
               href="{{ route('sites.show', $site) }}?tab=socials">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
                Соцмережі
                <span class="site-show__nav-count">{{ $site->socials->count() ?: '—' }}</span>
            </a>
        </nav>
    </div>

    {{-- Content --}}
    <div class="site-show__content">
        @if($tab === 'phones')
            @include('admin.sites._tab-phones', ['site' => $site, 'phones' => $site->phones, 'countries' => $countries])
        @elseif($tab === 'prices')
            @include('admin.sites._tab-prices', ['site' => $site, 'prices' => $site->prices])
        @elseif($tab === 'addresses')
            @include('admin.sites._tab-addresses', ['site' => $site, 'addresses' => $site->addresses])
        @elseif($tab === 'socials')
            @include('admin.sites._tab-socials', ['site' => $site, 'socials' => $site->socials])
        @endif
    </div>
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
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити сайт «{{ $site->name }}»?')">Видалити</button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-edit')">Скасувати</button>
        <button type="submit" form="form-site-edit" class="btn-primary">Зберегти</button>
    </div>
</div>

@endsection
