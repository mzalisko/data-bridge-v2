@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/site-groups.css') }}?v={{ filemtime(public_path('assets/css/pages/site-groups.css')) }}">
@endpush

@section('title', 'Групи сайтів')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Групи сайтів</h1>
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
        <button class="btn-primary" onclick="openDrawer('drawer-group-create')">+ Нова група</button>
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
                   placeholder="Пошук груп…"
                   value="{{ request('search') }}" id="group-search">
        </div>
        <span class="page-controls__count">{{ $groups->total() }} груп</span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($groups->isEmpty())
    <div class="empty-page"><p>Груп ще немає. Натисніть «+ Нова група» щоб розпочати.</p></div>
@else
    <div class="group-list" id="groups-list">
        @foreach($groups as $group)
        @php
            $sites = $group->sites->take(3);
            $extra = $group->sites_count - $sites->count();
            $colorHex = $group->color ?? '#708499';
            $letter = mb_strtoupper(mb_substr($group->name, 0, 1, 'UTF-8'), 'UTF-8');
        @endphp
        <div class="group-row"
             style="--group-color:{{ $colorHex }}"
             data-searchable="{{ $group->name }} {{ $group->description }}"
             onclick="window.location='{{ route('site-groups.show', $group) }}'">
            <div class="group-row__icon"
                 style="background:{{ $colorHex }}26;color:{{ $colorHex }};">
                {{ $letter }}
            </div>
            <div class="group-row__info">
                <span class="group-row__name">{{ $group->name }}</span>
                @if($group->description)
                    <span class="group-row__desc">{{ Str::limit($group->description, 60) }}</span>
                @endif
            </div>
            <div class="group-row__sites" onclick="event.stopPropagation()">
                @foreach($sites as $site)
                    <span class="group-row__site-chip">{{ parse_url($site->url, PHP_URL_HOST) ?: $site->url }}</span>
                @endforeach
                @if($extra > 0)
                    <span class="group-row__site-chip">+{{ $extra }}</span>
                @endif
            </div>


            <div class="group-row__end" onclick="event.stopPropagation()">
                <span class="group-row__count">{{ $group->sites_count }}</span>
                <button class="btn-icon" title="Редагувати"
                        onclick="openDrawer('drawer-group-{{ $group->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>
    <div class="pagination-wrap">{{ $groups->links() }}</div>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-group-create-overlay" onclick="closeDrawer('drawer-group-create')"></div>
<div class="drawer" id="drawer-group-create">
    <div class="drawer__header">
        <span class="drawer__title">Нова група</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-group-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('site-groups.store') }}" class="form-stack" id="form-group-create">
            @csrf
            @include('admin.site-groups._form', ['group' => null])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-create')">Скасувати</button>
        <button type="submit" form="form-group-create" class="btn-primary">Створити</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($groups as $group)
<div class="drawer-overlay" id="drawer-group-{{ $group->id }}-overlay" onclick="closeDrawer('drawer-group-{{ $group->id }}')"></div>
<div class="drawer" id="drawer-group-{{ $group->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $group->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-group-{{ $group->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST"
              action="{{ route('site-groups.update', $group) }}"
              class="form-stack"
              id="form-group-{{ $group->id }}">
            @csrf @method('PUT')
            @include('admin.site-groups._form', ['group' => $group])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('site-groups.destroy', $group) }}" class="drawer__footer-left">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити групу «{{ $group->name }}»?')">Видалити</button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-{{ $group->id }}')">Скасувати</button>
        <button type="submit" form="form-group-{{ $group->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initViewToggle('groups-view', 'groups-list', 'btn-view-list', 'btn-view-grid');
    // Замінюємо клас для груп (grid використовує group-list--grid)
    (function() {
        var list = document.getElementById('groups-list');
        if (!list) return;
        var saved = localStorage.getItem('groups-view') || 'list';
        if (saved === 'grid') list.classList.add('group-list--grid');
        document.getElementById('btn-view-list').addEventListener('click', function() {
            list.classList.remove('group-list--grid');
        });
        document.getElementById('btn-view-grid').addEventListener('click', function() {
            list.classList.add('group-list--grid');
        });
    })();

    initClientSearch('group-search', '.group-row');
</script>
@endpush

@endsection
