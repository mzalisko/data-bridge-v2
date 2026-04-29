@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/sites.css') }}?v={{ filemtime(public_path('assets/css/pages/sites.css')) }}">
@endpush

@section('title', 'Sites')

@section('content')

{{-- Page head --}}
<div class="page-toolbar">
    <div>
        <h1 class="page-title">Sites</h1>
        <div class="page-subtitle">{{ $sites->total() }} sites across {{ $groups->count() }} groups</div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <a href="{{ route('sites.batch.show') }}?ids[]=" class="btn-ghost" style="display:none" id="btn-batch-action">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Batch дії <span id="batch-count-inline" style="font-size:11px;opacity:.7"></span>
        </a>
        <button class="btn-primary" onclick="openDrawer('drawer-site-create')">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Add site
        </button>
    </div>
</div>

{{-- Toolbar --}}
<div class="crm-table__wrap" style="margin-bottom:24px;">

    {{-- Search + filters bar --}}
    <div style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-2);flex-wrap:wrap;">
        <div class="page-controls__search" style="max-width:360px;flex:1;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="page-controls__search-input"
                   placeholder="Search sites by name or URL…"
                   id="site-search" value="{{ request('search') }}">
        </div>

        {{-- Group filter --}}
        <select class="page-controls__sort" onchange="applyQueryParam('group_id', this.value)">
            <option value="">All groups</option>
            @foreach($groups as $g)
                <option value="{{ $g->id }}" {{ request('group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
            @endforeach
        </select>

        {{-- Status filter --}}
        <select class="page-controls__sort" onchange="applyQueryParam('status', this.value)">
            <option value="" {{ !request('status') ? 'selected' : '' }}>All statuses</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled</option>
        </select>

        <div style="flex:1;"></div>

        {{-- Selection actions (shown when rows checked) --}}
        <span id="sel-count" style="font-size:12px;color:var(--text-3);display:none;"></span>
        <a href="{{ route('sites.batch.show') }}" id="btn-batch-go"
           class="btn-ghost" style="display:none;font-size:12px;">
            Batch дії
        </a>
        <button class="btn-ghost" id="btn-batch-clear" onclick="batchClear()" style="display:none;font-size:12px;">
            Очистити
        </button>

        {{-- Total / filtered count --}}
        <span class="page-controls__count" id="visible-count">{{ $sites->total() }} sites</span>
    </div>

    {{-- Table --}}
    @if($sites->isEmpty())
        <div class="data-tab__empty">Сайтів не знайдено.</div>
    @else
    <div style="overflow-x:auto;">
        <table class="crm-table">
            <thead>
                <tr>
                    <th style="width:36px;padding-left:16px;">
                        <input type="checkbox" id="cb-all" style="accent-color:var(--accent);width:15px;height:15px;cursor:pointer;"
                               onchange="batchToggleAll(this.checked)">
                    </th>
                    <th>Site</th>
                    <th>Group</th>
                    <th>Status</th>
                    <th>Sync</th>
                    <th>Added</th>
                    <th style="width:80px;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sites as $site)
                @php
                    $color    = $site->siteGroup?->color ?? '#708499';
                    $letter   = strtoupper(substr($site->name, 0, 1));
                    $syncLog  = $site->latestSyncLog;
                    $syncOk   = $syncLog?->status === 'success';
                    $syncTime = $syncLog?->created_at?->diffForHumans() ?? null;
                    $isFav    = in_array($site->id, $favoriteIds);
                @endphp
                <tr data-searchable="{{ strtolower($site->name) }} {{ strtolower($site->url) }} {{ strtolower($site->siteGroup?->name ?? '') }}"
                    onclick="handleRowClick(event, {{ $site->id }}, '{{ route('sites.show', $site) }}')"
                    style="cursor:pointer;" id="row-site-{{ $site->id }}">

                    {{-- Checkbox --}}
                    <td style="padding-left:16px;width:36px;" onclick="event.stopPropagation()">
                        <input type="checkbox" class="batch-cb" value="{{ $site->id }}"
                               style="accent-color:var(--accent);width:15px;height:15px;cursor:pointer;"
                               onchange="batchUpdateSelection()">
                    </td>

                    {{-- Site cell --}}
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="site-card__favicon" data-site-favicon="{{ $site->name }}"
                                 style="width:32px;height:32px;background:{{ $color }}22;color:{{ $color }};font-size:12px;">
                                {{ $letter }}
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:500;color:var(--text);">{{ $site->name }}</div>
                                <div style="font-size:11px;color:var(--text-3);font-family:var(--font-mono);">{{ $site->url }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Group --}}
                    <td>
                        @if($site->siteGroup)
                            <span class="group-pill" style="--pill-color:{{ $color }}">{{ $site->siteGroup->name }}</span>
                        @else
                            <span style="color:var(--text-3);font-size:12px;">—</span>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td>
                        <span class="status-badge {{ $site->is_active ? 'status-badge--active' : 'status-badge--disabled' }}">
                            <span class="status-badge__dot"></span>
                            {{ $site->is_active ? 'Active' : 'Disabled' }}
                        </span>
                    </td>

                    {{-- Sync --}}
                    <td style="font-size:12px;color:var(--text-3);">
                        @if($syncTime)
                            <span style="color:{{ $syncOk ? 'var(--success)' : 'var(--warning)' }};">● </span>{{ $syncTime }}
                        @else
                            <span style="color:var(--text-3);">—</span>
                        @endif
                    </td>

                    {{-- Added --}}
                    <td style="font-size:12px;color:var(--text-3);white-space:nowrap;">
                        {{ $site->created_at->format('d.m.Y') }}
                    </td>

                    {{-- Actions --}}
                    <td onclick="event.stopPropagation()" style="text-align:right;padding-right:12px;">
                        <div style="display:flex;align-items:center;gap:2px;justify-content:flex-end;opacity:0;transition:opacity .15s;" class="row-actions">
                            <button class="db-fav-btn {{ $isFav ? 'is-fav' : '' }}"
                                    title="{{ $isFav ? 'Прибрати з улюблених' : 'Додати до улюблених' }}"
                                    onclick="toggleFavorite(event, this, {{ $site->id }})">★</button>
                            <a href="{{ $site->url }}" target="_blank" class="btn-icon" title="Open site">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                            </a>
                            <button class="btn-icon" title="Edit" onclick="openDrawer('drawer-site-{{ $site->id }}')">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 20h4l11-11-4-4L4 16v4z"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                @if($sites->isEmpty())
                <tr><td colspan="7" class="data-tab__empty">No sites match the filters.</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($sites->hasPages())
    <div style="padding:12px 16px;border-top:1px solid var(--border-2);">
        {{ $sites->appends(request()->query())->links() }}
    </div>
    @endif
    @endif

</div>

{{-- Floating batch bar --}}
<div class="batch-bar" id="batch-bar">
    <span id="batch-count">0 сайтів обрано</span>
    <form method="GET" action="{{ route('sites.batch.show') }}" id="form-batch-nav" style="display:flex;gap:8px;align-items:center;">
        <div id="batch-ids-container"></div>
        <button type="submit" class="btn-primary" style="font-size:12px;height:32px;">Batch дії</button>
    </form>
    <button class="btn-ghost" onclick="batchClear()" style="font-size:12px;height:32px;">Скасувати</button>
</div>

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
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-create')">Cancel</button>
        <button type="submit" form="form-site-create" class="btn-primary">Add site</button>
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
                    onclick="return confirm('Delete site «{{ $site->name }}»?')">Delete</button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-{{ $site->id }}')">Cancel</button>
        <button type="submit" form="form-site-{{ $site->id }}" class="btn-primary">Save</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    // Client-side search in table rows
    document.getElementById('site-search').addEventListener('input', function() {
        var q = this.value.toLowerCase().trim();
        var rows = document.querySelectorAll('tbody tr[data-searchable]');
        var visible = 0;
        rows.forEach(function(row) {
            var match = row.dataset.searchable.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        var cnt = document.getElementById('visible-count');
        if (cnt) cnt.textContent = visible + ' sites';
    });

    // Row hover shows actions
    document.querySelectorAll('tbody tr[data-searchable]').forEach(function(row) {
        var actions = row.querySelector('.row-actions');
        if (!actions) return;
        row.addEventListener('mouseenter', function() { actions.style.opacity = '1'; });
        row.addEventListener('mouseleave', function() { actions.style.opacity = '0'; });
    });

    // Row click: navigate
    function handleRowClick(e, siteId, url) {
        window.location = url;
    }

    // Toggle all checkboxes
    function batchToggleAll(checked) {
        document.querySelectorAll('.batch-cb').forEach(function(cb) {
            cb.checked = checked;
        });
        batchUpdateSelection();
    }

    // Update batch state
    function batchUpdateSelection() {
        var checked = document.querySelectorAll('.batch-cb:checked');
        var count   = checked.length;
        var bar     = document.getElementById('batch-bar');
        var countEl = document.getElementById('batch-count');
        var selCnt  = document.getElementById('sel-count');
        var batchGo = document.getElementById('btn-batch-go');
        var batchClearBtn = document.getElementById('btn-batch-clear');

        if (bar)     bar.classList.toggle('is-visible', count > 0);
        if (countEl) countEl.textContent = count + ' ' + pluralSites(count) + ' обрано';
        if (selCnt)  { selCnt.textContent = count + ' selected'; selCnt.style.display = count > 0 ? '' : 'none'; }
        if (batchGo) batchGo.style.display = count > 0 ? '' : 'none';
        if (batchClearBtn) batchClearBtn.style.display = count > 0 ? '' : 'none';

        // Rebuild hidden ids for batch form
        var container = document.getElementById('batch-ids-container');
        if (container) {
            container.innerHTML = '';
            checked.forEach(function(cb) {
                var inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
                container.appendChild(inp);
            });
        }

        // Header checkbox state
        var allCbs = document.querySelectorAll('.batch-cb');
        var cbAll  = document.getElementById('cb-all');
        if (cbAll) {
            cbAll.indeterminate = count > 0 && count < allCbs.length;
            cbAll.checked = count === allCbs.length && allCbs.length > 0;
        }

        // Highlight rows
        document.querySelectorAll('tbody tr[data-searchable]').forEach(function(row) {
            var cb = row.querySelector('.batch-cb');
            row.style.background = (cb && cb.checked) ? 'var(--accent-2)' : '';
        });
    }

    function batchClear() {
        document.querySelectorAll('.batch-cb').forEach(function(cb) { cb.checked = false; });
        var cbAll = document.getElementById('cb-all');
        if (cbAll) { cbAll.checked = false; cbAll.indeterminate = false; }
        batchUpdateSelection();
    }

    function pluralSites(n) {
        if (n % 10 === 1 && n % 100 !== 11) return 'сайт';
        if ([2,3,4].includes(n % 10) && ![12,13,14].includes(n % 100)) return 'сайти';
        return 'сайтів';
    }
</script>
@endpush

@endsection
