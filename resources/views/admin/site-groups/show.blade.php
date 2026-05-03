@extends('layouts.app')

@section('title', $siteGroup->name)

@section('content')
@php
    $color = $siteGroup->color ?? '#71717a';
    $activeSites   = $sites->where('is_active', true)->count();
    $inactiveSites = $sites->count() - $activeSites;
@endphp

<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <div class="page-head__crumb">
                <a href="{{ route('site-groups.index') }}">Групи сайтів</a> /
                <span style="color:var(--text);">{{ $siteGroup->name }}</span>
            </div>
            <h1 class="page-head__title">
                <span style="width:32px;height:32px;border-radius:8px;background:{{ $color }}22;color:{{ $color }};display:inline-flex;align-items:center;justify-content:center;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                </span>
                {{ $siteGroup->name }}
            </h1>
            @if($siteGroup->description)
                <p class="page-head__subtitle">{{ $siteGroup->description }}</p>
            @endif
        </div>
        <div class="page-head__actions">
            <a href="{{ route('site-groups.index') }}" class="btn btn--ghost btn--md">← Назад</a>
            <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-group-edit')">Редагувати групу</button>
        </div>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif

    {{-- ========= STAT CARDS ========= --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
        <div class="stat-card">
            <div class="stat-card__label">Всього сайтів</div>
            <div class="stat-card__row"><span class="stat-card__value">{{ $sites->count() }}</span></div>
            <div class="stat-card__delta">у цій групі</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Активних</div>
            <div class="stat-card__row"><span class="stat-card__value" style="color:var(--success);">{{ $activeSites }}</span></div>
            <div class="stat-card__delta">{{ $inactiveSites }} вимкнений</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Колір</div>
            <div class="stat-card__row" style="margin-top:8px;">
                <span style="display:inline-flex;align-items:center;gap:8px;">
                    <span style="width:24px;height:24px;border-radius:6px;background:{{ $color }};border:1px solid var(--border);"></span>
                    <span style="font-family:var(--font-mono);font-size:12px;color:var(--text-2);">{{ $color }}</span>
                </span>
            </div>
        </div>
    </div>

    {{-- ========= SITES IN GROUP ========= --}}
    <div class="card card--flush">
        <div class="section-head">
            <h3 class="section-head__title">Сайти в цій групі</h3>
            <a href="{{ route('sites.index', ['group_id' => $siteGroup->id]) }}" class="section-head__link">Всі сайти з фільтром →</a>
        </div>
        <div style="overflow:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Сайт</th>
                        <th>Статус</th>
                        <th>Додано</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                        <tr onclick="window.location='{{ route('sites.show', $site) }}'">
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <x-favicon :name="$site->name" :size="22"/>
                                    <div>
                                        <div style="font-weight:500;color:var(--text);">{{ $site->name }}</div>
                                        <div style="color:var(--text-3);font-size:11px;font-family:var(--font-mono);">{{ $site->url ? (parse_url($site->url, PHP_URL_HOST) ?: $site->url) : '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><x-status-pill :status="$site->is_active ? 'Online' : 'Offline'"/></td>
                            <td style="color:var(--text-3);font-size:12px;">{{ $site->created_at->format('d M Y') }}</td>
                            <td><span style="color:var(--text-3);font-size:12px;">→</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">У групі ще немає сайтів</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ========= EDIT DRAWER ========= --}}
<div class="drawer-overlay" id="drawer-group-edit-overlay" onclick="closeDrawer('drawer-group-edit')"></div>
<div class="drawer" id="drawer-group-edit">
    <div class="drawer__header">
        <span class="drawer__title">{{ $siteGroup->name }}</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-group-edit')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('site-groups.update', $siteGroup) }}" id="form-group-edit">
            @csrf @method('PUT')
            @include('admin.site-groups._form', ['group' => $siteGroup])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('site-groups.destroy', $siteGroup) }}" class="drawer__footer-left" onsubmit="return confirm('Видалити групу «{{ $siteGroup->name }}»?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn--danger btn--md">Видалити</button>
        </form>
        <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-group-edit')">Скасувати</button>
        <button type="submit" form="form-group-edit" class="btn btn--primary btn--md">Зберегти</button>
    </div>
</div>

@endsection
