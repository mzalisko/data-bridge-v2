@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/sites.css') }}?v={{ filemtime(public_path('assets/css/pages/sites.css')) }}">
@endpush

@section('title', 'Sites')

@section('content')

<div class="page-head">
    <div>
        <h1 class="page-head__title">Sites</h1>
        <p class="page-head__sub">{{ $totalCount }} total · {{ $activeCount }} active</p>
    </div>
    <button class="btn btn--md btn--primary" onclick="openDrawer('drawer-site-create')">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add site
    </button>
</div>

{{-- Filter / Batch bar --}}
<div class="sites-filter-bar" id="sites-filter-bar">

    {{-- Normal state --}}
    <div class="sites-filter-normal" id="sites-filter-normal">
        <div class="sites-search-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="sites-search-input"
                   placeholder="Search sites…"
                   value="{{ request('search') }}" id="site-search">
        </div>

        <div class="sites-filter-pills">
            <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}"
               class="filter-pill {{ !request('status') ? 'is-active' : '' }}">
                All <span class="filter-pill__count">{{ $totalCount }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}"
               class="filter-pill {{ request('status') === 'active' ? 'is-active' : '' }}">
                <span class="filter-pill__dot" style="background:var(--success)"></span>
                Active <span class="filter-pill__count">{{ $activeCount }}</span>
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}"
               class="filter-pill {{ request('status') === 'inactive' ? 'is-active' : '' }}">
                <span class="filter-pill__dot" style="background:var(--danger)"></span>
                Inactive <span class="filter-pill__count">{{ $inactiveCount }}</span>
            </a>
            @if($groups->isNotEmpty())
                <div class="filter-divider"></div>
                @foreach($groups as $group)
                <a href="{{ request()->fullUrlWithQuery(['group_id' => $group->id, 'page' => null]) }}"
                   class="filter-pill {{ request('group_id') == $group->id ? 'is-active' : '' }}">
                    <span class="filter-pill__dot" style="background:{{ $group->color ?? '#708499' }}"></span>
                    {{ $group->name }}
                </a>
                @endforeach
                @if(request('group_id'))
                <a href="{{ request()->fullUrlWithQuery(['group_id' => null, 'page' => null]) }}"
                   class="filter-pill">✕ Clear</a>
                @endif
            @endif
        </div>

        <div class="sites-filter-right">
            <select class="form-select form-select--sm" onchange="applyQueryParam('sort', this.value)">
                <option value="date"   {{ request('sort', 'date') === 'date'   ? 'selected' : '' }}>Newest</option>
                <option value="name"   {{ request('sort', 'date') === 'name'   ? 'selected' : '' }}>Name A→Z</option>
                <option value="status" {{ request('sort', 'date') === 'status' ? 'selected' : '' }}>Status</option>
                <option value="group"  {{ request('sort', 'date') === 'group'  ? 'selected' : '' }}>Group</option>
            </select>
        </div>
    </div>

    {{-- Batch state (shown when items selected) --}}
    <div class="sites-batch-bar" id="sites-batch-bar" style="display:none">
        <span class="sites-batch-bar__count" id="batch-count">0 selected</span>
        <div class="sites-batch-bar__actions">
            <form method="GET" action="{{ route('sites.batch.show') }}" id="form-batch-nav">
                <div id="batch-ids-container"></div>
                <button type="submit" class="btn btn--md btn--secondary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Bulk actions
                </button>
            </form>
        </div>
        <button class="btn btn--md btn--ghost" onclick="batchClear()">Cancel</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($sites->isEmpty())
    <div class="sites-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <p>No sites found.</p>
    </div>
@else
    <div class="sites-table-wrap">
        <table class="sites-table" id="sites-list">
            <thead>
                <tr>
                    <th class="sites-table__th sites-table__th--cb">
                        <input type="checkbox" class="sites-cb" id="cb-all" onchange="batchToggleAll(this)">
                    </th>
                    <th class="sites-table__th">Site</th>
                    <th class="sites-table__th sites-table__th--group">Group</th>
                    <th class="sites-table__th sites-table__th--status">Status</th>
                    <th class="sites-table__th sites-table__th--sync">Last sync</th>
                    <th class="sites-table__th sites-table__th--date">Added</th>
                    <th class="sites-table__th sites-table__th--actions"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sites as $site)
                @php
                    $color = $site->siteGroup?->color ?? '#708499';
                    $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
                    $syncOk  = $site->latestSyncLog?->status === 'ok' || $site->latestSyncLog?->status === 'no_changes';
                    $syncErr = $site->latestSyncLog?->status === 'error';
                    $syncDot = $syncOk ? 'var(--success)' : ($syncErr ? 'var(--danger)' : 'var(--text-3)');
                    $syncTime = $site->latestSyncLog?->synced_at?->diffForHumans() ?? null;
                @endphp
                <tr class="sites-table__row {{ !$site->is_active ? 'sites-table__row--disabled' : '' }}"
                    data-searchable="{{ $site->name }} {{ $site->url }} {{ $site->siteGroup?->name }}"
                    data-site-id="{{ $site->id }}">

                    <td class="sites-table__td sites-table__td--cb" onclick="event.stopPropagation()">
                        <input type="checkbox" class="sites-cb batch-cb" value="{{ $site->id }}"
                               onchange="batchUpdateSelection()">
                    </td>

                    <td class="sites-table__td">
                        <a href="{{ route('sites.show', $site) }}" class="sites-table__site-cell">
                            <div class="sites-table__favicon"
                                 style="background:{{ $color }}22;color:{{ $color }};">{{ $letter }}</div>
                            <div class="sites-table__site-info">
                                <span class="sites-table__site-name">{{ $site->name }}</span>
                                <span class="sites-table__site-url">{{ $site->url }}</span>
                            </div>
                        </a>
                    </td>

                    <td class="sites-table__td sites-table__td--group">
                        @if($site->siteGroup)
                            <span class="group-pill" style="--pill-color:{{ $color }}">
                                {{ $site->siteGroup->name }}
                            </span>
                        @else
                            <span class="sites-table__no-group">—</span>
                        @endif
                    </td>

                    <td class="sites-table__td sites-table__td--status">
                        <span class="status-dot-pill status-dot-pill--{{ $site->is_active ? 'active' : 'disabled' }}">
                            <span class="status-dot-pill__dot"></span>
                            {{ $site->is_active ? 'Active' : 'Off' }}
                        </span>
                    </td>

                    <td class="sites-table__td sites-table__td--sync">
                        @if($syncTime)
                            <div class="sites-table__sync">
                                <span class="sites-table__sync-dot" style="background:{{ $syncDot }}"></span>
                                <span class="sites-table__sync-time">{{ $syncTime }}</span>
                            </div>
                        @else
                            <span class="sites-table__no-group">—</span>
                        @endif
                    </td>

                    <td class="sites-table__td sites-table__td--date">
                        {{ $site->created_at->format('d.m.Y') }}
                    </td>

                    <td class="sites-table__td sites-table__td--actions" onclick="event.stopPropagation()">
                        <div class="sites-table__actions">
                            <a href="{{ $site->url }}" target="_blank" class="btn-icon" title="Open site">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                            </a>
                            <button class="btn-icon" title="Edit"
                                    onclick="openDrawer('drawer-site-{{ $site->id }}')">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $sites->links() }}</div>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-site-create-overlay" onclick="closeDrawer('drawer-site-create')"></div>
<div class="drawer" id="drawer-site-create">
    <div class="drawer__header">
        <span class="drawer__title">New site</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.store') }}" class="form-stack" id="form-site-create">
            @csrf
            @include('admin.sites._form', ['site' => null, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn--ghost" onclick="closeDrawer('drawer-site-create')">Cancel</button>
        <button type="submit" form="form-site-create" class="btn--primary">Add site</button>
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
            <button type="submit" class="btn--danger"
                    onclick="return confirm('Delete site «{{ $site->name }}»?')">Delete</button>
        </form>
        <button type="button" class="btn--ghost" onclick="closeDrawer('drawer-site-{{ $site->id }}')">Cancel</button>
        <button type="submit" form="form-site-{{ $site->id }}" class="btn--primary">Save</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initClientSearch('site-search', '.sites-table__row');

    function batchToggleAll(master) {
        document.querySelectorAll('.batch-cb').forEach(function(cb) { cb.checked = master.checked; });
        batchUpdateSelection();
    }

    function batchUpdateSelection() {
        var checked = document.querySelectorAll('.batch-cb:checked');
        var count = checked.length;

        var normalBar = document.getElementById('sites-filter-normal');
        var batchBar  = document.getElementById('sites-batch-bar');
        var countEl   = document.getElementById('batch-count');

        if (count > 0) {
            normalBar.style.display = 'none';
            batchBar.style.display  = 'flex';
        } else {
            normalBar.style.display = '';
            batchBar.style.display  = 'none';
        }

        if (countEl) countEl.textContent = count + ' ' + pluralSites(count);

        var container = document.getElementById('batch-ids-container');
        if (container) {
            container.innerHTML = '';
            checked.forEach(function(cb) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ids[]';
                inp.value = cb.value;
                container.appendChild(inp);
            });
        }

        document.querySelectorAll('.sites-table__row').forEach(function(row) {
            var cb = row.querySelector('.batch-cb');
            row.classList.toggle('is-batch-selected', cb && cb.checked);
        });

        var allCbs = document.querySelectorAll('.batch-cb');
        var masterCb = document.getElementById('cb-all');
        if (masterCb) {
            masterCb.indeterminate = count > 0 && count < allCbs.length;
            masterCb.checked = count > 0 && count === allCbs.length;
        }
    }

    function batchClear() {
        document.querySelectorAll('.batch-cb').forEach(function(cb) { cb.checked = false; });
        var masterCb = document.getElementById('cb-all');
        if (masterCb) { masterCb.checked = false; masterCb.indeterminate = false; }
        batchUpdateSelection();
    }

    function pluralSites(n) {
        if (n === 1) return 'site';
        return 'sites';
    }
</script>
@endpush

@endsection
