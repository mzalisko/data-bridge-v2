@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/site-groups.css') }}?v={{ filemtime(public_path('assets/css/pages/site-groups.css')) }}">
@endpush

@section('title', 'Site groups')

@section('content')

<div class="page-toolbar">
    <div>
        <h1 class="page-title">Site groups</h1>
        <div class="page-subtitle">Organize sites by agency, client, or purpose.</div>
    </div>
    <button class="btn-primary" onclick="openDrawer('drawer-group-create')">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
        New group
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

@if($groups->isEmpty())
    <div class="empty-page"><p>No groups yet. Click «New group» to start.</p></div>
@else
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;" id="groups-list">
        @foreach($groups as $group)
        @php
            $color = $group->color ?? '#708499';
            $groupSites = $group->sites->take(4);
            $extraSites = $group->sites_count - $groupSites->count();
        @endphp
        <div class="card" style="padding:0;cursor:pointer;"
             data-searchable="{{ $group->name }} {{ $group->description }}"
             onclick="window.location='{{ route('site-groups.show', $group) }}'">

            {{-- Group header --}}
            <div style="padding:18px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                <div style="display:flex;gap:12px;align-items:flex-start;">
                    <span style="width:38px;height:38px;border-radius:8px;flex-shrink:0;background:{{ $color }}22;color:{{ $color }};display:inline-flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/>
                            <rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
                        </svg>
                    </span>
                    <div>
                        <div style="font-size:15px;font-weight:600;">{{ $group->name }}</div>
                        @if($group->description)
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">{{ Str::limit($group->description, 60) }}</div>
                        @else
                        <div style="font-size:12px;color:var(--text-3);margin-top:2px;">No description</div>
                        @endif
                    </div>
                </div>
                <button class="btn-icon" title="Edit" style="flex-shrink:0;"
                        onclick="event.stopPropagation(); openDrawer('drawer-group-{{ $group->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                        <circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/>
                    </svg>
                </button>
            </div>

            {{-- Stats row --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--border-2);">
                <div style="padding:12px 16px;">
                    <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Sites</div>
                    <div style="font-size:18px;font-weight:600;margin-top:4px;">{{ $group->sites_count }}</div>
                </div>
                <div style="padding:12px 16px;border-left:1px solid var(--border-2);">
                    <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Active</div>
                    <div style="font-size:18px;font-weight:600;margin-top:4px;">{{ $group->sites->where('is_active', true)->count() }}</div>
                </div>
                <div style="padding:12px 16px;border-left:1px solid var(--border-2);">
                    <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Color</div>
                    <div style="margin-top:6px;display:flex;align-items:center;gap:6px;">
                        <span style="width:14px;height:14px;border-radius:3px;background:{{ $color }};display:inline-block;"></span>
                        <span style="font-size:12px;font-family:var(--font-mono);color:var(--text-3);">{{ $color }}</span>
                    </div>
                </div>
            </div>

            {{-- Site chips --}}
            <div style="border-top:1px solid var(--border-2);padding:10px 16px;" onclick="event.stopPropagation()">
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    @foreach($groupSites as $site)
                    <a href="{{ route('sites.show', $site) }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:3px 8px;background:var(--panel-2);border:1px solid var(--border);border-radius:999px;font-size:11px;color:var(--text-2);text-decoration:none;cursor:pointer;">
                        <span style="width:14px;height:14px;border-radius:3px;background:{{ $color }}22;color:{{ $color }};font-size:8px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;">
                            {{ strtoupper(substr($site->name, 0, 1)) }}
                        </span>
                        {{ $site->url ? parse_url($site->url, PHP_URL_HOST) ?: $site->name : $site->name }}
                    </a>
                    @endforeach
                    @if($extraSites > 0)
                        <span style="font-size:11px;color:var(--text-3);padding:4px 8px;">+{{ $extraSites }} more</span>
                    @endif
                    @if($groupSites->isEmpty())
                        <span style="font-size:11px;color:var(--text-3);">No sites yet</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($groups->hasPages())
    <div style="margin-top:20px;">{{ $groups->appends(request()->query())->links() }}</div>
    @endif
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
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-create')">Cancel</button>
        <button type="submit" form="form-group-create" class="btn-primary">Create</button>
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
        <form method="POST" action="{{ route('site-groups.update', $group) }}" class="form-stack" id="form-group-{{ $group->id }}">
            @csrf @method('PUT')
            @include('admin.site-groups._form', ['group' => $group])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('site-groups.destroy', $group) }}" class="drawer__footer-left">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Delete group «{{ $group->name }}»?')">Delete</button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-{{ $group->id }}')">Cancel</button>
        <button type="submit" form="form-group-{{ $group->id }}" class="btn-primary">Save</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initClientSearch('group-search', '[data-searchable]');
</script>
@endpush

@endsection
