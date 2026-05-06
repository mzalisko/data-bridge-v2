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
    $syncOk   = $syncLog?->status === 'success';
    $syncWarn = $syncLog && !$syncOk;
    $syncColor = $syncOk ? 'var(--dot-ok)' : ($syncWarn ? 'var(--dot-pause)' : 'var(--text-muted)');
@endphp

{{-- Back link --}}
<a href="{{ route('sites.index') }}" class="site-back">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polyline points="15 18 9 12 15 6"/>
    </svg>
    Всі сайти
</a>

{{-- ── Hero Header ── --}}
<div class="site-header">
    <div class="site-header__favicon" style="background:{{ $color }}26;color:{{ $color }}">
        {{ $letter }}
    </div>
    <div class="site-header__identity">
        <div class="site-header__name">{{ $site->name }}</div>
        <div class="site-header__url">{{ $site->url }}</div>
        <div class="site-header__badges">
            <span class="status-dot-inline status-dot-inline--{{ $site->is_active ? 'ok' : 'off' }}"
                  style="color:{{ $site->is_active ? 'var(--dot-ok)' : 'var(--dot-off)' }}">
                {{ $site->is_active ? 'Active' : 'Disabled' }}
            </span>
            @if($site->siteGroup)
                <span class="group-pill" style="--pill-color:{{ $color }}">{{ $site->siteGroup->name }}</span>
            @endif
            @if($syncLog)
                <span class="sync-pill">
                    <span class="sync-pill__dot" style="background:{{ $syncColor }}"></span>
                    {{ $syncLog->created_at->diffForHumans() }}
                </span>
            @endif
        </div>
    </div>
    <div class="site-header__actions">
        <a href="{{ $site->url }}" target="_blank" class="btn-ghost">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:4px;vertical-align:middle">
                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
            </svg>
            Відкрити
        </a>
        <button class="btn-primary" onclick="openDrawer('drawer-site-edit')">Редагувати</button>
    </div>
</div>

{{-- ── Stat chips ── --}}
<div class="site-stats">
    <a href="#section-phones" class="stat-chip" onclick="siteStatScroll(event,'section-phones')">
        <svg class="stat-chip__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.72-.72a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
        </svg>
        <span class="stat-chip__label">Телефони</span>
        <span class="stat-chip__count">{{ $site->phones->count() }}</span>
    </a>
    <a href="#section-prices" class="stat-chip" onclick="siteStatScroll(event,'section-prices')">
        <svg class="stat-chip__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="1" x2="12" y2="23"/>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        <span class="stat-chip__label">Ціни</span>
        <span class="stat-chip__count">{{ $site->prices->count() }}</span>
    </a>
    <a href="#section-addresses" class="stat-chip" onclick="siteStatScroll(event,'section-addresses')">
        <svg class="stat-chip__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
            <circle cx="12" cy="10" r="3"/>
        </svg>
        <span class="stat-chip__label">Адреси</span>
        <span class="stat-chip__count">{{ $site->addresses->count() }}</span>
    </a>
    <a href="#section-socials" class="stat-chip" onclick="siteStatScroll(event,'section-socials')">
        <svg class="stat-chip__icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
        </svg>
        <span class="stat-chip__label">Соцмережі</span>
        <span class="stat-chip__count">{{ $site->socials->count() }}</span>
    </a>
</div>

{{-- ── Two-column layout ── --}}
<div class="site-overview">

    {{-- Left: collapsible data sections --}}
    <div class="site-overview__main">

        @if(session('success'))
            <div class="alert alert--success" style="margin-bottom:var(--space-md)">{{ session('success') }}</div>
        @endif

        {{-- ─ Phones ─ --}}
        <div class="site-section" id="section-phones">
            <div class="site-section__head" onclick="toggleSection(this)">
                <svg class="site-section__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
                <svg class="site-section__icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.72-.72a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <span class="site-section__title">Телефони</span>
                <span class="site-section__count">{{ $site->phones->count() }}</span>
                <button class="site-section__add" onclick="event.stopPropagation(); openDrawer('drawer-phone-create')">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Додати
                </button>
            </div>
            <div class="site-section__body">
                @include('admin.sites._tab-phones', [
                    'site'        => $site,
                    'phones'      => $site->phones,
                    'countries'   => $countries,
                    'sectionMode' => true,
                ])
            </div>
        </div>

        {{-- ─ Prices ─ --}}
        <div class="site-section" id="section-prices">
            <div class="site-section__head" onclick="toggleSection(this)">
                <svg class="site-section__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
                <svg class="site-section__icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                <span class="site-section__title">Ціни</span>
                <span class="site-section__count">{{ $site->prices->count() }}</span>
                <button class="site-section__add" onclick="event.stopPropagation(); openDrawer('drawer-price-create')">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Додати
                </button>
            </div>
            <div class="site-section__body">
                @include('admin.sites._tab-prices', [
                    'site'        => $site,
                    'prices'      => $site->prices,
                    'countries'   => $countries,
                    'sectionMode' => true,
                ])
            </div>
        </div>

        {{-- ─ Addresses ─ --}}
        <div class="site-section" id="section-addresses">
            <div class="site-section__head" onclick="toggleSection(this)">
                <svg class="site-section__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
                <svg class="site-section__icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <span class="site-section__title">Адреси</span>
                <span class="site-section__count">{{ $site->addresses->count() }}</span>
                <button class="site-section__add" onclick="event.stopPropagation(); openDrawer('drawer-address-create')">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Додати
                </button>
            </div>
            <div class="site-section__body">
                @include('admin.sites._tab-addresses', [
                    'site'        => $site,
                    'addresses'   => $site->addresses,
                    'countries'   => $countries,
                    'sectionMode' => true,
                ])
            </div>
        </div>

        {{-- ─ Socials ─ --}}
        <div class="site-section" id="section-socials">
            <div class="site-section__head" onclick="toggleSection(this)">
                <svg class="site-section__arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
                <svg class="site-section__icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                    <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/>
                    <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                </svg>
                <span class="site-section__title">Соцмережі</span>
                <span class="site-section__count">{{ $site->socials->count() }}</span>
                <button class="site-section__add" onclick="event.stopPropagation(); openDrawer('drawer-social-create')">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Додати
                </button>
            </div>
            <div class="site-section__body">
                @include('admin.sites._tab-socials', [
                    'site'        => $site,
                    'socials'     => $site->socials,
                    'countries'   => $countries,
                    'sectionMode' => true,
                ])
            </div>
        </div>

    </div>{{-- /site-overview__main --}}

    {{-- Right aside --}}
    <aside class="site-aside">

        {{-- Metadata --}}
        <div class="aside-card">
            <div class="aside-card__head">Інформація</div>
            <div class="aside-card__body">
                <div class="aside-meta-row">
                    <span class="aside-meta-row__label">Статус</span>
                    <span class="aside-meta-row__val"
                          style="color:{{ $site->is_active ? 'var(--dot-ok)' : 'var(--dot-off)' }}">
                        ● {{ $site->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </div>
                <div class="aside-meta-row">
                    <span class="aside-meta-row__label">Група</span>
                    <span class="aside-meta-row__val">
                        @if($site->siteGroup)
                            <span class="group-pill" style="--pill-color:{{ $color }};font-size:11px">{{ $site->siteGroup->name }}</span>
                        @else
                            <span style="color:var(--text-muted)">—</span>
                        @endif
                    </span>
                </div>
                @if($syncLog)
                <div class="aside-meta-row">
                    <span class="aside-meta-row__label">Sync</span>
                    <span class="aside-meta-row__val" style="color:{{ $syncColor }}">
                        {{ $syncOk ? '✓' : '✗' }} {{ $syncLog->created_at->diffForHumans() }}
                    </span>
                </div>
                @if($syncLog->duration_ms)
                <div class="aside-meta-row">
                    <span class="aside-meta-row__label">Тривалість</span>
                    <span class="aside-meta-row__val">{{ $syncLog->duration_ms }} ms</span>
                </div>
                @endif
                @endif
                <div class="aside-meta-row">
                    <span class="aside-meta-row__label">Додано</span>
                    <span class="aside-meta-row__val">{{ $site->created_at->format('d.m.Y') }}</span>
                </div>
            </div>
        </div>

        {{-- API Key --}}
        <div class="aside-card">
            @include('admin.sites._api-key', ['site' => $site])
        </div>

    </aside>

</div>{{-- /site-overview --}}

{{-- ── Edit site drawer ── --}}
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

@push('scripts')
<script>
function toggleSection(headEl) {
    headEl.closest('.site-section').classList.toggle('is-collapsed');
}

function siteStatScroll(e, id) {
    e.preventDefault();
    var el = document.getElementById(id);
    if (!el) return;
    var section = el.closest ? el : el;
    if (section.classList.contains('is-collapsed')) {
        section.classList.remove('is-collapsed');
    }
    section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>
@endpush

@endsection
