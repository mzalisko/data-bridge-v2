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
<div class="group-nav">
    @foreach($allGroups as $g)
    <a href="{{ route('site-groups.show', $g) }}"
       class="group-nav__item {{ $g->id === $siteGroup->id ? 'is-active' : '' }}">
        <span class="group-nav__dot" style="background:{{ $g->color ?? '#706f70' }}"></span>
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
        <div class="site-card" onclick="window.location='{{ route('sites.show', $site) }}'">
            <span class="status-dot status-dot--{{ $site->is_active ? 'ok' : 'off' }}"></span>
            <div class="site-card__info">
                <span class="site-card__name">{{ $site->name }}</span>
                <a href="{{ $site->url }}" target="_blank" class="site-card__url" onclick="event.stopPropagation()">
                    {{ $site->url }}
                </a>
            </div>
            <span class="site-card__meta">{{ $site->created_at->format('d.m.Y') }}</span>
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
