@extends('layouts.app')

@section('title', 'Sites')

@section('content')
<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <h1 class="page-head__title">Sites</h1>
            <p class="page-head__subtitle">{{ $totalCount }} sites across {{ $groups->count() }} groups</p>
        </div>
        <div class="page-head__actions">
            <button class="btn btn--secondary btn--md">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>
                </svg>
                Export
            </button>
            <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-site-create')">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Add site
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    {{-- ========= MAIN CARD ========= --}}
    <div class="card card--flush">

        {{-- Toolbar --}}
        <form method="GET" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-2);">
            <div class="input" style="flex:1;max-width:380px;">
                <span class="input__icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search sites by name or domain…">
            </div>
            <div class="select-wrap">
                <select name="group_id" onchange="this.form.submit()">
                    <option value="">All groups</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ (string)request('group_id') === (string)$g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
                <span class="select-wrap__chevron">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <div class="select-wrap">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Online</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Offline</option>
                </select>
                <span class="select-wrap__chevron">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <div style="flex:1"></div>
            <span style="font-size:12px;color:var(--text-3);">{{ $sites->total() }} of {{ $totalCount }}</span>
        </form>

        {{-- Table --}}
        <div style="overflow:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="check-all"></th>
                        <th>Site</th>
                        <th>Group</th>
                        <th>Status</th>
                        <th>Phones</th>
                        <th>Last sync</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                        @php
                            $statusName = $site->is_active ? 'Online' : 'Offline';
                            $syncLog    = $site->latestSyncLog;
                            $syncWhen   = $syncLog?->synced_at?->diffForHumans() ?? '—';
                            $groupColor = $site->siteGroup?->color ?? '#71717a';
                        @endphp
                        <tr onclick="window.location='{{ route('sites.show', $site) }}'">
                            <td onclick="event.stopPropagation()"><input type="checkbox" name="ids[]" value="{{ $site->id }}"></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <x-favicon :name="$site->name" :size="22"/>
                                    <div>
                                        <div style="font-weight:500;color:var(--text);">{{ $site->name }}</div>
                                        <div style="color:var(--text-3);font-size:11px;font-family:var(--font-mono);">{{ $site->url ? (parse_url($site->url, PHP_URL_HOST) ?: $site->url) : '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($site->siteGroup)
                                    <span class="group-chip">
                                        <span class="group-chip__dot" style="background:{{ $groupColor }}"></span>
                                        {{ $site->siteGroup->name }}
                                    </span>
                                @else
                                    <span style="color:var(--text-3);">—</span>
                                @endif
                            </td>
                            <td><x-status-pill :status="$statusName"/></td>
                            <td class="mono">{{ $site->phones?->count() ?? 0 }}</td>
                            <td style="color:var(--text-3);font-size:12px;">{{ $syncWhen }}</td>
                            <td onclick="event.stopPropagation()">
                                <button class="icon-btn">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">No sites match the current filters</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($sites->hasPages())
            <div>{{ $sites->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>

{{-- ========= CREATE DRAWER ========= --}}
<div class="drawer-overlay" id="drawer-site-create-overlay" onclick="closeDrawer('drawer-site-create')"></div>
<div class="drawer" id="drawer-site-create">
    <div class="drawer__header">
        <span class="drawer__title">Add site</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-site-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.store') }}" class="form-stack" id="form-site-create">
            @csrf
            @include('admin.sites._form', ['site' => null, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-site-create')">Cancel</button>
        <button type="submit" form="form-site-create" class="btn btn--primary btn--md">Create</button>
    </div>
</div>

@endsection
