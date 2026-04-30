@extends('layouts.app')

@section('title', 'Overview')

@section('content')
<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <h1 class="page-head__title">Overview</h1>
            <p class="page-head__subtitle">All sites, groups, and team activity in one place.</p>
        </div>
        <div class="page-head__actions">
            <button class="btn btn--secondary btn--md">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>
                </svg>
                Export
            </button>
            <a href="{{ route('sites.index') }}" class="btn btn--primary btn--md">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Add site
            </a>
        </div>
    </div>

    {{-- ========= 4 STAT CARDS ========= --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;">
        <div class="stat-card">
            <div class="stat-card__label">Sites</div>
            <div class="stat-card__row">
                <span class="stat-card__value">{{ $stats['sites'] ?? 0 }}</span>
            </div>
            <div class="stat-card__delta" style="color:var(--success);">{{ $stats['active'] ?? 0 }} online</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Total contacts</div>
            <div class="stat-card__row">
                <span class="stat-card__value">{{ number_format($stats['contacts'] ?? 0) }}</span>
            </div>
            <div class="stat-card__delta">across all sites</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Conflicts</div>
            <div class="stat-card__row">
                <span class="stat-card__value" @if(($stats['problems'] ?? 0) > 0) style="color:var(--warning);" @endif>{{ $stats['problems'] ?? 0 }}</span>
            </div>
            <div class="stat-card__delta">{{ ($stats['problems'] ?? 0) > 0 ? 'needs review' : 'all synced' }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__label">Site groups</div>
            <div class="stat-card__row">
                <span class="stat-card__value">{{ $stats['groups'] ?? 0 }}</span>
            </div>
            <div class="stat-card__delta">organize your work</div>
        </div>
    </div>

    {{-- ========= 2-COL: ACTIVITY + SIDEBAR ========= --}}
    <div style="display:grid;grid-template-columns:1.7fr 1fr;gap:20px;">

        {{-- LEFT: Recent activity --}}
        <div class="card card--flush">
            <div class="section-head">
                <h3 class="section-head__title">Recent activity</h3>
                <a href="{{ route('logs.system') }}" class="section-head__link">View all</a>
            </div>
            @if($recentSyncs->isEmpty())
                <div style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;border-top:1px solid var(--border-2);">No activity yet.</div>
            @else
                @foreach($recentSyncs->take(6) as $sync)
                    @php
                        $kind = $sync->status === 'success' ? 'success' : ($sync->status === 'error' ? 'danger' : 'warning');
                        $kindLabel = $sync->status === 'success' ? 'ok' : ($sync->status === 'error' ? 'error' : 'warning');
                    @endphp
                    <div class="activity-row">
                        <span class="activity-row__when">{{ $sync->synced_at?->diffForHumans() ?? '—' }}</span>
                        <div class="activity-row__body">
                            <span class="dot dot--{{ $kind }}"></span>
                            <span class="activity-row__who-system">system</span>
                            <span class="activity-row__action">
                                {{ $sync->status === 'success' ? 'synced' : 'failed to sync' }}
                            </span>
                            <a href="{{ route('sites.show', $sync->site_id) }}" class="activity-row__target">{{ $sync->site?->name ?? 'unknown' }}</a>
                        </div>
                        <span class="activity-row__kind">{{ $kindLabel }}</span>
                    </div>
                @endforeach
            @endif
        </div>

        {{-- RIGHT: Plan mix + Top sites --}}
        <div style="display:flex;flex-direction:column;gap:20px;">

            {{-- Plan mix (groups breakdown) --}}
            <div class="card card--flush">
                <div class="section-head">
                    <h3 class="section-head__title">Group mix</h3>
                </div>
                <div style="padding:4px 20px 20px;display:flex;flex-direction:column;gap:10px;">
                    @php
                        $groups = \App\Models\SiteGroup::withCount('sites')->orderByDesc('sites_count')->take(4)->get();
                        $totalSites = max(1, $stats['sites'] ?? 1);
                        $palette = ['var(--accent)', 'oklch(0.65 0.14 264)', 'oklch(0.7 0.05 264)', 'var(--warning)'];
                    @endphp
                    @forelse($groups as $i => $g)
                        @php $pct = round(($g->sites_count / $totalSites) * 100); @endphp
                        <div>
                            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                                <span style="color:var(--text-2);">{{ $g->name }}</span>
                                <span style="color:var(--text-3);font-family:var(--font-mono);">{{ $g->sites_count }} · {{ $pct }}%</span>
                            </div>
                            <div style="height:6px;border-radius:99px;background:var(--panel-2);overflow:hidden;">
                                <div style="width:{{ $pct }}%;height:100%;background:{{ $g->color ?? $palette[$i % 4] }};"></div>
                            </div>
                        </div>
                    @empty
                        <div style="font-size:12px;color:var(--text-3);">No groups yet.</div>
                    @endforelse
                </div>
            </div>

            {{-- Top sites --}}
            <div class="card card--flush">
                <div class="section-head">
                    <h3 class="section-head__title">Top sites</h3>
                    <a href="{{ route('sites.index') }}" class="section-head__link">All sites</a>
                </div>
                @php $listSites = $favoriteSites->isNotEmpty() ? $favoriteSites : $quickSites; @endphp
                @forelse($listSites->take(4) as $site)
                    <a href="{{ route('sites.show', $site) }}" style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-top:1px solid var(--border-2);text-decoration:none;color:inherit;">
                        <x-favicon :name="$site->name" :size="22"/>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13px;font-weight:500;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $site->name }}</div>
                            <div style="font-size:11px;color:var(--text-3);font-family:var(--font-mono);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $site->url }}</div>
                        </div>
                        <span style="font-size:12px;color:var(--text-2);font-family:var(--font-mono);">{{ $site->phones_count ?? $site->phones?->count() ?? 0 }}</span>
                    </a>
                @empty
                    <div style="padding:20px;font-size:12px;color:var(--text-3);border-top:1px solid var(--border-2);">No sites yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
