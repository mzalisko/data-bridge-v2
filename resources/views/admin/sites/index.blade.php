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

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($sites->isEmpty())
    <div class="empty-page">
        <p>Сайтів ще немає. Натисніть «+ Новий сайт» щоб додати перший.</p>
    </div>
@else
    <div class="sites-list" id="sites-list">
        @foreach($sites as $site)
        <div class="site-card" onclick="openDrawer('drawer-site-{{ $site->id }}')">
            <span class="status-dot status-dot--{{ $site->is_active ? 'ok' : 'off' }}"></span>
            <div class="site-card__info">
                <span class="site-card__name">{{ $site->name }}</span>
                <a href="{{ $site->url }}" target="_blank" class="site-card__url" onclick="event.stopPropagation()">
                    {{ $site->url }}
                </a>
            </div>
            <div class="site-card__group">
                @if($site->siteGroup)
                    <span class="group-pill" style="--pill-color:{{ $site->siteGroup->color ?? '#706f70' }}">
                        {{ $site->siteGroup->name }}
                    </span>
                @endif
            </div>
            <span class="site-card__meta">{{ $site->created_at->format('d.m.Y') }}</span>
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
