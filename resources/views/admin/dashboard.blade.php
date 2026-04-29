@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/dashboard.css') }}?v={{ filemtime(public_path('assets/css/pages/dashboard.css')) }}">
@endpush

@section('title', 'Dashboard')

@section('content')

{{-- Page head --}}
<div class="page-toolbar" style="margin-bottom:20px;">
    <div>
        <h1 class="page-title">Overview</h1>
        <div class="page-subtitle">All sites, groups, and sync activity in one place.</div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('sites.index') }}" class="btn-ghost">All sites</a>
        <a href="{{ route('site-groups.index') }}" class="btn-primary">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 5v14M5 12h14"/></svg>
            Add site
        </a>
    </div>
</div>

{{-- 4 Stat cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-card__label">Total sites</div>
        <div class="stat-card__value">{{ $stats['sites'] }}</div>
        <div class="stat-card__delta" style="color:var(--success);">{{ $stats['active'] }} online</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Site groups</div>
        <div class="stat-card__value">{{ $stats['groups'] }}</div>
        <div class="stat-card__delta"><a href="{{ route('site-groups.index') }}" style="color:var(--accent);text-decoration:none;font-size:12px;">View all →</a></div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Sync errors</div>
        <div class="stat-card__value" style="{{ $stats['problems'] > 0 ? 'color:var(--danger)' : '' }}">{{ $stats['problems'] }}</div>
        <div class="stat-card__delta" style="{{ $stats['problems'] > 0 ? 'color:var(--danger)' : 'color:var(--success)' }}">
            {{ $stats['problems'] > 0 ? 'needs review' : 'all synced' }}
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Favorites</div>
        <div class="stat-card__value">{{ $favoriteSites->count() }}</div>
        <div class="stat-card__delta">pinned sites</div>
    </div>
</div>

{{-- 2-column layout: activity + sidebar --}}
<div style="display:grid;grid-template-columns:1.7fr 1fr;gap:20px;">

    {{-- Recent sync activity --}}
    <div class="card" style="padding:0;">
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border-2);">
            <span style="font-size:14px;font-weight:600;">Recent sync activity</span>
            <a href="{{ route('logs.sync') }}" style="color:var(--accent);font-size:12px;font-weight:500;text-decoration:none;">View all</a>
        </div>

        @if($recentSyncs->isEmpty())
            <div class="data-tab__empty">No sync events yet.</div>
        @else
            <div>
                @foreach($recentSyncs->take(8) as $sync)
                <div style="display:flex;align-items:center;gap:12px;padding:11px 20px;border-bottom:1px solid var(--border-2);">
                    <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;background:{{ $sync->status === 'success' ? 'var(--success)' : 'var(--danger)' }};"></span>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <a href="{{ route('sites.show', $sync->site_id) }}"
                               style="font-size:13px;font-weight:500;color:var(--text);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;">
                                {{ $sync->site?->name ?? 'Unknown site' }}
                            </a>
                            @if($sync->status === 'error')
                                <span class="status-badge status-badge--disabled" style="font-size:10px;padding:1px 6px;">error</span>
                            @endif
                        </div>
                        <div style="font-size:11px;color:var(--text-3);margin-top:1px;">
                            @if($sync->status === 'success')
                                Synced successfully · {{ $sync->duration_ms ? $sync->duration_ms.'ms' : '' }}
                            @else
                                {{ $sync->error_msg ?? 'Sync error' }}
                            @endif
                        </div>
                    </div>
                    <span style="font-size:11px;color:var(--text-3);white-space:nowrap;flex-shrink:0;">
                        {{ $sync->synced_at?->diffForHumans() ?? '' }}
                    </span>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Right sidebar: problems + favorites + quick sites --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Problems --}}
        <div class="card" style="padding:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--border-2);">
                <span style="font-size:14px;font-weight:600;">
                    @if($problemSites->isEmpty())
                        <span style="color:var(--success);">✓</span> All sites healthy
                    @else
                        <span style="color:var(--danger);">⚠</span> Problems ({{ $problemSites->count() }})
                    @endif
                </span>
            </div>
            @if($problemSites->isEmpty())
                <div style="padding:16px 16px;font-size:12px;color:var(--text-3);">All sites are synced.</div>
            @else
                @foreach($problemSites as $site)
                <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border-2);">
                    <div style="width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;background:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }}20;color:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }};flex-shrink:0;">
                        {{ mb_strtoupper(mb_substr($site->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $site->name }}</div>
                        <div style="font-size:11px;color:var(--danger);">{{ Str::limit($site->latestSyncLog?->error_msg ?? 'Connection error', 40) }}</div>
                    </div>
                    <a href="{{ route('sites.show', $site) }}" class="btn-icon" style="flex-shrink:0;" title="View">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
                @endforeach
            @endif
        </div>

        {{-- Top sites (favorites or recent) --}}
        <div class="card" style="padding:0;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--border-2);">
                <span style="font-size:14px;font-weight:600;">
                    {{ $favoriteSites->isNotEmpty() ? '★ Favorites' : 'Recent sites' }}
                </span>
                <a href="{{ route('sites.index') }}" style="color:var(--accent);font-size:12px;font-weight:500;text-decoration:none;">All sites</a>
            </div>

            @php $listSites = $favoriteSites->isNotEmpty() ? $favoriteSites : $quickSites; @endphp
            @if($listSites->isEmpty())
                <div style="padding:16px;font-size:12px;color:var(--text-3);">No recent sites.</div>
            @else
                @foreach($listSites->take(5) as $site)
                @php $isFav = in_array($site->id, $favoriteIds); @endphp
                <div style="display:flex;align-items:center;gap:10px;padding:10px 16px;border-bottom:1px solid var(--border-2);cursor:pointer;"
                     onclick="window.location='{{ route('sites.show', $site) }}'">
                    <div style="width:28px;height:28px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;background:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }}20;color:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }};flex-shrink:0;"
                         data-site-favicon="{{ $site->name }}">
                        {{ mb_strtoupper(mb_substr($site->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:12px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $site->name }}</div>
                        <div style="font-size:11px;color:var(--text-3);font-family:var(--font-mono);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $site->url }}</div>
                    </div>
                    <span style="width:7px;height:7px;border-radius:50%;flex-shrink:0;background:{{ $site->latestSyncLog?->status === 'success' ? 'var(--success)' : ($site->latestSyncLog ? 'var(--danger)' : 'var(--border)') }};"></span>
                    <button class="db-fav-btn {{ $isFav ? 'is-fav' : '' }}"
                            onclick="event.stopPropagation(); toggleFavorite(event, this, {{ $site->id }})">★</button>
                </div>
                @endforeach
            @endif
        </div>

    </div>{{-- /right --}}

</div>{{-- /2-col --}}

@endsection
