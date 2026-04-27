@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/sites.css') }}?v={{ filemtime(public_path('assets/css/pages/sites.css')) }}">
@endpush

@section('title', 'Сайти')

@section('content')

<div class="page-toolbar">
    <div>
        <h1 class="page-title">Сайти</h1>
    </div>
    <div style="display:flex;align-items:center;gap:var(--space-sm);">
        <button id="btn-batch-toggle" class="btn-batch-toggle" title="Вибрати кілька" onclick="toggleBatchMode()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="5" width="4" height="4" rx="1"/><line x1="10" y1="7" x2="21" y2="7"/>
                <rect x="3" y="11" width="4" height="4" rx="1"/><line x1="10" y1="13" x2="21" y2="13"/>
                <rect x="3" y="17" width="4" height="4" rx="1"/><line x1="10" y1="19" x2="21" y2="19"/>
            </svg>
            Вибрати
        </button>
        <button class="btn--primary" onclick="openDrawer('drawer-site-create')">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Новий сайт
        </button>
    </div>
</div>

{{-- Filter bar --}}
<div class="sites-filter-bar">
    <div class="sites-search-wrap">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" class="sites-search-input"
               placeholder="Пошук сайтів…"
               value="{{ request('search') }}" id="site-search">
    </div>

    <div class="sites-filter-pills">
        <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}"
           class="filter-pill {{ !request('status') ? 'is-active' : '' }}">
            Всі <span class="filter-pill__count">{{ $totalCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}"
           class="filter-pill {{ request('status') === 'active' ? 'is-active' : '' }}">
            <span class="filter-pill__dot" style="background:var(--success)"></span>
            Активні <span class="filter-pill__count">{{ $activeCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}"
           class="filter-pill {{ request('status') === 'inactive' ? 'is-active' : '' }}">
            <span class="filter-pill__dot" style="background:var(--danger)"></span>
            Вимкнені <span class="filter-pill__count">{{ $inactiveCount }}</span>
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
               class="filter-pill">✕ Очистити</a>
            @endif
        @endif
    </div>

    <div class="sites-filter-right">
        <span class="sites-count-label">{{ $sites->total() }} сайтів</span>
        <select class="form-select form-select--sm" onchange="applyQueryParam('sort', this.value)">
            <option value="date"   {{ request('sort', 'date') === 'date'   ? 'selected' : '' }}>Дата ↓</option>
            <option value="name"   {{ request('sort', 'date') === 'name'   ? 'selected' : '' }}>Назва A→Z</option>
            <option value="status" {{ request('sort', 'date') === 'status' ? 'selected' : '' }}>Статус</option>
            <option value="group"  {{ request('sort', 'date') === 'group'  ? 'selected' : '' }}>Група</option>
        </select>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($sites->isEmpty())
    <div class="sites-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <p>Сайтів не знайдено.</p>
    </div>
@else
    {{-- Sites table --}}
    <div class="sites-table-wrap">
        <table class="sites-table" id="sites-list">
            <thead>
                <tr>
                    <th class="sites-table__th sites-table__th--cb">
                        <input type="checkbox" class="sites-cb sites-cb--all" id="cb-all" onchange="batchToggleAll(this)">
                    </th>
                    <th class="sites-table__th">Сайт</th>
                    <th class="sites-table__th sites-table__th--group">Група</th>
                    <th class="sites-table__th sites-table__th--status">Статус</th>
                    <th class="sites-table__th sites-table__th--sync">Sync</th>
                    <th class="sites-table__th sites-table__th--date">Додано</th>
                    <th class="sites-table__th sites-table__th--actions"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sites as $site)
                @php
                    $color = $site->siteGroup?->color ?? '#708499';
                    $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
                    $syncOk = $site->latestSyncLog?->status === 'ok' || $site->latestSyncLog?->status === 'no_changes';
                    $syncErr = $site->latestSyncLog?->status === 'error';
                    $syncDot = $syncOk ? 'var(--success)' : ($syncErr ? 'var(--danger)' : 'var(--text-3)');
                    $syncTime = $site->latestSyncLog?->synced_at?->diffForHumans() ?? null;
                @endphp
                <tr class="sites-table__row {{ !$site->is_active ? 'sites-table__row--disabled' : '' }}"
                    data-searchable="{{ $site->name }} {{ $site->url }} {{ $site->siteGroup?->name }}"
                    data-site-id="{{ $site->id }}"
                    onclick="handleSiteRowClick(event, {{ $site->id }}, '{{ route('sites.show', $site) }}')">

                    <td class="sites-table__td sites-table__td--cb" onclick="event.stopPropagation()">
                        <input type="checkbox" class="sites-cb batch-cb" value="{{ $site->id }}"
                               onchange="batchUpdateSelection()">
                    </td>

                    <td class="sites-table__td">
                        <div class="sites-table__site-cell">
                            <div class="sites-table__favicon"
                                 style="background:{{ $color }}22;color:{{ $color }};">{{ $letter }}</div>
                            <div class="sites-table__site-info">
                                <span class="sites-table__site-name">{{ $site->name }}</span>
                                <span class="sites-table__site-url">{{ $site->url }}</span>
                            </div>
                        </div>
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
                            @php $isFav = in_array($site->id, $favoriteIds); @endphp
                            <button class="btn-icon db-fav-btn {{ $isFav ? 'is-fav' : '' }}"
                                    title="{{ $isFav ? 'Прибрати' : 'Улюблений' }}"
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
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">{{ $sites->links() }}</div>
@endif

{{-- Batch floating bar --}}
<div class="batch-bar" id="batch-bar">
    <span class="batch-bar__count" id="batch-count">0 обрано</span>
    <div class="batch-bar__actions">
        <form method="GET" action="{{ route('sites.batch.show') }}" id="form-batch-nav">
            <div id="batch-ids-container"></div>
            <button type="submit" class="btn--primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                Batch дії
            </button>
        </form>
        <button class="btn--ghost" onclick="batchClear()">Скасувати</button>
    </div>
</div>

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
        <button type="button" class="btn--ghost" onclick="closeDrawer('drawer-site-create')">Скасувати</button>
        <button type="submit" form="form-site-create" class="btn--primary">Додати</button>
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
                    onclick="return confirm('Видалити сайт «{{ $site->name }}»?')">Видалити</button>
        </form>
        <button type="button" class="btn--ghost" onclick="closeDrawer('drawer-site-{{ $site->id }}')">Скасувати</button>
        <button type="submit" form="form-site-{{ $site->id }}" class="btn--primary">Зберегти</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initClientSearch('site-search', '.sites-table__row');

    var batchMode = false;

    function toggleBatchMode() {
        batchMode = !batchMode;
        var table = document.getElementById('sites-list');
        var btn   = document.getElementById('btn-batch-toggle');
        if (table) table.classList.toggle('is-batch-mode', batchMode);
        if (btn)   btn.classList.toggle('is-active', batchMode);
        if (!batchMode) {
            document.querySelectorAll('.batch-cb').forEach(function(cb) { cb.checked = false; });
            document.getElementById('cb-all').checked = false;
            batchUpdateSelection();
        }
    }

    function batchToggleAll(master) {
        document.querySelectorAll('.batch-cb').forEach(function(cb) { cb.checked = master.checked; });
        batchUpdateSelection();
    }

    function handleSiteRowClick(e, siteId, url) {
        if (batchMode) {
            var cb = e.currentTarget.querySelector('.batch-cb');
            if (cb) { cb.checked = !cb.checked; batchUpdateSelection(); }
        } else {
            window.location = url;
        }
    }

    function batchUpdateSelection() {
        var checked = document.querySelectorAll('.batch-cb:checked');
        var count = checked.length;

        var bar = document.getElementById('batch-bar');
        var countEl = document.getElementById('batch-count');
        if (bar) bar.classList.toggle('is-visible', count > 0);
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
        batchMode = false;
        var table = document.getElementById('sites-list');
        var btn   = document.getElementById('btn-batch-toggle');
        if (table) table.classList.remove('is-batch-mode');
        if (btn)   btn.classList.remove('is-active');
        document.querySelectorAll('.batch-cb').forEach(function(cb) { cb.checked = false; });
        var masterCb = document.getElementById('cb-all');
        if (masterCb) { masterCb.checked = false; masterCb.indeterminate = false; }
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
