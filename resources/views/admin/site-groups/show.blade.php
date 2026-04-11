@extends('layouts.app')

@section('title', $siteGroup->name)

@section('content')

<div class="page-toolbar">
    <div style="display:flex;align-items:center;gap:var(--space-md);">
        <a href="{{ route('site-groups.index') }}" class="btn-ghost">← Групи</a>
        <div style="display:flex;align-items:center;gap:var(--space-sm);">
            <span class="group-card__dot" style="background:{{ $siteGroup->color ?? '#706f70' }};width:12px;height:12px;border-radius:50%;display:inline-block;"></span>
            <h1 class="page-title">{{ $siteGroup->name }}</h1>
        </div>
        <span class="role-badge" style="background:rgba(129,140,248,.12);color:var(--accent);">{{ $siteGroup->sites_count }} сайтів</span>
    </div>
    <button class="btn-primary" onclick="openDrawer('drawer-group-edit')">Редагувати</button>
</div>

@if($siteGroup->description)
    <p style="color:var(--text-secondary);font-size:var(--font-size-sm);margin-bottom:var(--space-lg);">{{ $siteGroup->description }}</p>
@endif

{{-- Group nav --}}
<div class="page-controls__pills" style="margin-bottom: var(--space-lg); overflow-x: auto; padding-bottom: 4px; flex-wrap: nowrap;">
    @foreach($allGroups as $g)
    <a href="{{ route('site-groups.show', $g) }}"
       class="filter-pill {{ $g->id === $siteGroup->id ? 'is-active' : '' }}">
        <span class="filter-pill__dot" style="background:{{ $g->color ?? '#706f70' }}"></span>
        {{ $g->name }}
    </a>
    @endforeach
</div>

{{-- Sites in group --}}
@if($sites->isEmpty())
    <div class="empty-page">
        <p>У цій групі ще немає сайтів.</p>
    </div>
@else
    <div class="sites-list" id="sites-list">
        @foreach($sites as $site)
        @php
            $color = $siteGroup->color ?? '#708499';
            $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
        @endphp
        <div class="site-card {{ !$site->is_active ? 'site-card--disabled' : '' }}" onclick="window.location='{{ route('sites.show', $site) }}'">
            <div class="site-card__favicon"
                 style="background:{{ $color }}26;color:{{ $color }};">
                {{ $letter }}
            </div>
            <div class="site-card__info">
                <div class="site-card__name-row">
                    <span class="site-card__name">{{ $site->name }}</span>
                </div>
                <div class="site-card__meta-row">
                    <span class="site-card__url">{{ $site->url }}</span>
                </div>
            </div>
            <div class="site-card__status">
                <span class="status-badge status-badge--{{ $site->is_active ? 'active' : 'disabled' }}">
                    <span class="status-badge__dot"></span>{{ $site->is_active ? 'Active' : 'Disabled' }}
                </span>
            </div>
            <span class="site-card__date" style="margin-left: auto;">{{ $site->created_at->format('d.m.Y') }}</span>
        </div>
        @endforeach
    </div>
@endif

{{-- Edit group drawer --}}
<div class="drawer-overlay" id="drawer-group-edit-overlay" onclick="closeDrawer('drawer-group-edit')"></div>
<div class="drawer" id="drawer-group-edit">
    <div class="drawer__header">
        <span class="drawer__title">{{ $siteGroup->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-group-edit')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST"
              action="{{ route('site-groups.update', $siteGroup) }}"
              class="form-stack"
              id="form-group-edit">
            @csrf
            @method('PUT')
            @include('admin.site-groups._form', ['group' => $siteGroup])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('site-groups.destroy', $siteGroup) }}" class="drawer__footer-left">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити групу «{{ $siteGroup->name }}»?')">
                Видалити
            </button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-edit')">Скасувати</button>
        <button type="submit" form="form-group-edit" class="btn-primary">Зберегти</button>
    </div>
</div>

@endsection
