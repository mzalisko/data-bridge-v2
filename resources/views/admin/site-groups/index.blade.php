@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/site-groups.css') }}?v={{ filemtime(public_path('assets/css/pages/site-groups.css')) }}">
@endpush

@section('title', 'Groups')

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-head__title">Groups</h1>
        <p class="page-head__sub">{{ $groups->total() }} {{ $groups->total() === 1 ? 'group' : 'groups' }}</p>
    </div>
    <button class="btn btn--md btn--primary" onclick="openDrawer('drawer-group-create')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New group
    </button>
</div>

{{-- Search bar --}}
<div class="groups-filter-bar">
    <div class="sites-search-wrap">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" class="sites-search-input"
               placeholder="Search groups…"
               value="{{ request('search') }}" id="group-search">
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($groups->isEmpty())
    <div class="sites-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <p>No groups yet. Click "New group" to start.</p>
    </div>
@else
    <div class="group-grid" id="groups-list">
        @foreach($groups as $group)
        @php
            $sites = $group->sites->take(4);
            $extra = $group->sites_count - $sites->count();
            $colorHex = $group->color ?? '#708499';
        @endphp
        <div class="group-card"
             data-searchable="{{ $group->name }} {{ $group->description }}"
             onclick="window.location='{{ route('site-groups.show', $group) }}'">

            <div class="group-card__head">
                <div class="group-card__icon"
                     style="background:{{ $colorHex }}22;color:{{ $colorHex }};">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                    </svg>
                </div>
                <button class="btn-icon group-card__edit" title="Edit"
                        onclick="event.stopPropagation(); openDrawer('drawer-group-{{ $group->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
            </div>

            <div class="group-card__body">
                <span class="group-card__name">{{ $group->name }}</span>
                @if($group->description)
                    <span class="group-card__desc">{{ Str::limit($group->description, 80) }}</span>
                @endif
            </div>

            <div class="group-card__stats">
                <span class="group-card__stat">
                    <span class="group-card__stat-val">{{ $group->sites_count }}</span>
                    <span class="group-card__stat-label">sites</span>
                </span>
            </div>

            @if($sites->isNotEmpty())
            <div class="group-card__chips" onclick="event.stopPropagation()">
                @foreach($sites as $site)
                    <span class="group-card__chip">{{ parse_url($site->url, PHP_URL_HOST) ?: $site->url }}</span>
                @endforeach
                @if($extra > 0)
                    <span class="group-card__chip group-card__chip--more">+{{ $extra }}</span>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    <div class="pagination-wrap">{{ $groups->links() }}</div>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-group-create-overlay" onclick="closeDrawer('drawer-group-create')"></div>
<div class="drawer" id="drawer-group-create">
    <div class="drawer__header">
        <span class="drawer__title">New group</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-group-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('site-groups.store') }}" class="form-stack" id="form-group-create">
            @csrf
            @include('admin.site-groups._form', ['group' => null])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn--ghost" onclick="closeDrawer('drawer-group-create')">Cancel</button>
        <button type="submit" form="form-group-create" class="btn--primary">Create</button>
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
            <button type="submit" class="btn--danger"
                    onclick="return confirm('Delete group «{{ $group->name }}»?')">Delete</button>
        </form>
        <button type="button" class="btn--ghost" onclick="closeDrawer('drawer-group-{{ $group->id }}')">Cancel</button>
        <button type="submit" form="form-group-{{ $group->id }}" class="btn--primary">Save</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initClientSearch('group-search', '.group-card');
</script>
@endpush

@endsection
