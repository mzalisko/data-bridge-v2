@extends('layouts.app')

@section('title', 'Сайти')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Сайти</h1>
    <div style="display:flex;align-items:center;gap:var(--space-sm);">
        <div class="view-toggle">
            <button id="btn-view-list" class="view-toggle__btn is-active" title="Список">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/>
                    <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
            <button id="btn-view-grid" class="view-toggle__btn" title="Сітка">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </button>
        </div>
        <button class="btn-primary" onclick="openDrawer('drawer-site-create')">
            + Новий сайт
        </button>
    </div>
</div>

{{-- Group filter bar --}}
<div class="group-nav">
    <a href="{{ request()->fullUrlWithQuery(['group_id' => null, 'page' => null]) }}"
       class="group-nav__item {{ !request('group_id') ? 'is-active' : '' }}">
        Всі сайти
        <span class="group-nav__count">{{ $groups->sum('sites_count') }}</span>
    </a>
    @foreach($groups as $group)
    <a href="{{ request()->fullUrlWithQuery(['group_id' => $group->id, 'page' => null]) }}"
       class="group-nav__item {{ request('group_id') == $group->id ? 'is-active' : '' }}">
        <span class="group-nav__dot" style="background:{{ $group->color ?? '#706f70' }}"></span>
        {{ $group->name }}
        <span class="group-nav__count">{{ $group->sites_count }}</span>
    </a>
    @endforeach
</div>

{{-- Filter + sort bar --}}
<div class="filter-bar">
    <div class="filter-pills">
        <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}"
           class="filter-pill {{ !request('status') ? 'is-active' : '' }}">Всі</a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}"
           class="filter-pill {{ request('status') === 'active' ? 'is-active' : '' }}">
            <span class="status-dot status-dot--ok"></span> Активні
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}"
           class="filter-pill {{ request('status') === 'inactive' ? 'is-active' : '' }}">
            <span class="status-dot status-dot--off"></span> Зупинені
        </a>
    </div>
    <div class="filter-sort">
        <span class="filter-sort__label">Сортування</span>
        <select class="form-input form-input--sm" onchange="applyQueryParam('sort', this.value)">
            <option value="date"   {{ request('sort','date') === 'date'   ? 'selected' : '' }}>За датою ↓</option>
            <option value="name"   {{ request('sort','date') === 'name'   ? 'selected' : '' }}>За назвою A→Z</option>
            <option value="status" {{ request('sort','date') === 'status' ? 'selected' : '' }}>За статусом</option>
            <option value="group"  {{ request('sort','date') === 'group'  ? 'selected' : '' }}>За групою</option>
        </select>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($sites->isEmpty())
    <div class="empty-page">
        <p>Сайтів не знайдено.</p>
    </div>
@else
    <div class="sites-list" id="sites-list">
        @foreach($sites as $site)
        <div class="site-card" onclick="window.location='{{ route('sites.show', $site) }}'">
            <span class="status-dot status-dot--{{ $site->is_active ? 'ok' : 'off' }}" title="{{ $site->is_active ? 'Активний' : 'Зупинено' }}"></span>
            <div class="site-card__info">
                <span class="site-card__name">{{ $site->name }}</span>
                <span class="site-card__url">{{ $site->url }}</span>
            </div>
            <div class="site-card__group">
                @if($site->siteGroup)
                    <span class="group-pill" style="--pill-color:{{ $site->siteGroup->color ?? '#706f70' }}">
                        {{ $site->siteGroup->name }}
                    </span>
                @endif
            </div>
            <span class="site-card__meta">{{ $site->created_at->format('d.m.Y') }}</span>
            <div class="site-card__actions" onclick="event.stopPropagation()">
                <a href="{{ $site->url }}" target="_blank" class="btn-icon" title="Відкрити сайт">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                </a>
                <button class="btn-icon" title="Редагувати"
                        onclick="openDrawer('drawer-site-{{ $site->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-wrap">
        {{ $sites->links() }}
    </div>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-site-create-overlay" onclick="closeDrawer('drawer-site-create')"></div>
<div class="drawer" id="drawer-site-create">
    <div class="drawer__header">
        <span class="drawer__title">Новий сайт</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.store') }}" class="form-stack" id="form-site-create">
            @csrf
            @include('admin.sites._form', ['site' => null, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-create')">Скасувати</button>
        <button type="submit" form="form-site-create" class="btn-primary">Додати</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($sites as $site)
<div class="drawer-overlay" id="drawer-site-{{ $site->id }}-overlay" onclick="closeDrawer('drawer-site-{{ $site->id }}')"></div>
<div class="drawer" id="drawer-site-{{ $site->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $site->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-{{ $site->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST"
              action="{{ route('sites.update', $site) }}"
              class="form-stack"
              id="form-site-{{ $site->id }}">
            @csrf
            @method('PUT')
            @include('admin.sites._form', ['site' => $site, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('sites.destroy', $site) }}" class="drawer__footer-left">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити сайт «{{ $site->name }}»?')">
                Видалити
            </button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-{{ $site->id }}')">Скасувати</button>
        <button type="submit" form="form-site-{{ $site->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initViewToggle('sites-view', 'sites-list', 'btn-view-list', 'btn-view-grid');
</script>
@endpush

@endsection
