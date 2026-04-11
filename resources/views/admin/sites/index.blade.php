@extends('layouts.app')

@section('title', 'Сайти')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Сайти</h1>
    <div style="display:flex;align-items:center;gap:var(--space-sm);">
        <div class="view-toggle">
            <button id="btn-view-list" class="view-toggle__btn is-active" title="Список">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
            <button id="btn-view-grid" class="view-toggle__btn" title="Сітка">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </button>
        </div>
        <button class="btn-primary" onclick="openDrawer('drawer-site-create')">+ Новий сайт</button>
    </div>
</div>

{{-- Controls bar --}}
<div class="page-controls">
    <div class="page-controls__search-row">
        <div class="page-controls__search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="page-controls__search-input"
                   placeholder="Пошук сайтів…"
                   value="{{ request('search') }}" id="site-search">
        </div>
        <span class="page-controls__count">{{ $sites->total() }} сайтів</span>
    </div>
    <div class="page-controls__pills">
        <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}"
           class="filter-pill {{ !request('status') ? 'is-active' : '' }}">
            Всі <span class="filter-pill__count">{{ $totalCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}"
           class="filter-pill {{ request('status') === 'active' ? 'is-active' : '' }}">
            <span class="filter-pill__dot" style="background:var(--dot-ok)"></span>
            Active <span class="filter-pill__count">{{ $activeCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}"
           class="filter-pill {{ request('status') === 'inactive' ? 'is-active' : '' }}">
            <span class="filter-pill__dot" style="background:var(--dot-off)"></span>
            Disabled <span class="filter-pill__count">{{ $inactiveCount }}</span>
        </a>
        @if($groups->isNotEmpty())
            <div class="filter-pill-sep"></div>
            @foreach($groups as $group)
            <a href="{{ request()->fullUrlWithQuery(['group_id' => $group->id, 'page' => null]) }}"
               class="filter-pill {{ request('group_id') == $group->id ? 'is-active' : '' }}">
                <span class="filter-pill__dot" style="background:{{ $group->color ?? '#708499' }}"></span>
                {{ $group->name }}
            </a>
            @endforeach
            @if(request('group_id'))
            <a href="{{ request()->fullUrlWithQuery(['group_id' => null, 'page' => null]) }}"
               class="filter-pill">✕ Очистити</a>
            @endif
        @endif
        <select class="page-controls__sort" onchange="applyQueryParam('sort', this.value)">
            <option value="date"   {{ request('sort', 'date') === 'date'   ? 'selected' : '' }}>За датою ↓</option>
            <option value="name"   {{ request('sort', 'date') === 'name'   ? 'selected' : '' }}>За назвою A→Z</option>
            <option value="status" {{ request('sort', 'date') === 'status' ? 'selected' : '' }}>За статусом</option>
            <option value="group"  {{ request('sort', 'date') === 'group'  ? 'selected' : '' }}>За групою</option>
        </select>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($sites->isEmpty())
    <div class="empty-page"><p>Сайтів не знайдено.</p></div>
@else
    <div class="sites-list" id="sites-list">
        @foreach($sites as $site)
        @php
            $color = $site->siteGroup?->color ?? '#708499';
            $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
            $syncOk = $site->latestSyncLog?->status === 'success';
            $syncWarn = $site->latestSyncLog && !$syncOk;
            $syncDot = $syncOk ? 'var(--dot-ok)' : ($syncWarn ? 'var(--dot-pause)' : 'var(--text-muted)');
            $syncTime = $site->latestSyncLog?->created_at?->diffForHumans() ?? null;
        @endphp
        <div class="site-card {{ !$site->is_active ? 'site-card--disabled' : '' }}"
             data-searchable="{{ $site->name }} {{ $site->url }} {{ $site->siteGroup?->name }}"
             onclick="window.location='{{ route('sites.show', $site) }}'">
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
                    @if($syncTime)
                        <span class="site-card__meta-sep">·</span>
                        <span class="site-card__sync-dot" style="background:{{ $syncDot }}"></span>
                        <span class="site-card__sync-time">{{ $syncTime }}</span>
                    @endif
                </div>
            </div>
            <div class="site-card__status">
                <span class="status-badge status-badge--{{ $site->is_active ? 'active' : 'disabled' }}">
                    <span class="status-badge__dot"></span>{{ $site->is_active ? 'Active' : 'Disabled' }}
                </span>
            </div>
            <div class="site-card__group">
                @if($site->siteGroup)
                    <span class="group-pill" style="--pill-color:{{ $color }}">
                        {{ $site->siteGroup->name }}
                    </span>
                @endif
            </div>
            <span class="site-card__date">{{ $site->created_at->format('d.m.Y') }}</span>
            <div class="site-card__actions" onclick="event.stopPropagation()">
                @php $isFav = in_array($site->id, $favoriteIds); @endphp
                <button class="db-fav-btn {{ $isFav ? 'is-fav' : '' }}" 
                        style="font-size: 18px; margin-right: 4px;"
                        title="{{ $isFav ? 'Прибрати з улюблених' : 'Додати до улюблених' }}" 
                        onclick="toggleFavorite(event, this, {{ $site->id }})">★</button>
                <a href="{{ $site->url }}" target="_blank" class="btn-icon" title="Відкрити">
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
    <div class="pagination-wrap">{{ $sites->links() }}</div>
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
        <form method="POST" action="{{ route('sites.update', $site) }}" class="form-stack" id="form-site-{{ $site->id }}">
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
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-{{ $site->id }}')">Скасувати</button>
        <button type="submit" form="form-site-{{ $site->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initViewToggle('sites-view', 'sites-list', 'btn-view-list', 'btn-view-grid');
    initClientSearch('site-search', '.site-card');
</script>
@endpush

@endsection
