@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/dashboard.css') }}?v={{ filemtime(public_path('assets/css/pages/dashboard.css')) }}">
@endpush

@section('title', 'Dashboard')

@section('content')

{{-- ── Page head ── --}}
<div class="page-head">
    <div>
        <h1 class="page-head__title">Overview</h1>
        <p class="page-head__sub">All sites, groups, and team activity in one place.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('sites.index') }}" class="btn btn--md btn--secondary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            All sites
        </a>
        <a href="{{ route('sites.index') }}" class="btn btn--md btn--primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add site
        </a>
    </div>
</div>

{{-- ── 4 Stat cards ── --}}
<div class="dash-stats">
    <div class="stat-card">
        <div class="stat-card__label">Sites</div>
        <div class="stat-card__body">
            <span class="stat-card__value">{{ $totalSites }}</span>
            <svg class="stat-card__spark" viewBox="0 0 60 24" preserveAspectRatio="none">
                <polyline points="0,20 10,16 20,18 30,10 40,12 50,6 60,4" stroke="var(--success)" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="stat-card__delta" style="color:var(--success)">{{ $activeSites }} active</div>
    </div>

    <div class="stat-card">
        <div class="stat-card__label">Total contacts</div>
        <div class="stat-card__body">
            <span class="stat-card__value">{{ number_format($totalContacts) }}</span>
            <svg class="stat-card__spark" viewBox="0 0 60 24" preserveAspectRatio="none">
                <polyline points="0,22 10,18 20,14 30,10 40,7 50,4 60,2" stroke="var(--accent)" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="stat-card__delta">Across all sites</div>
    </div>

    <div class="stat-card">
        <div class="stat-card__label">Synced today</div>
        <div class="stat-card__body">
            <span class="stat-card__value">{{ $syncedToday }}</span>
            <svg class="stat-card__spark" viewBox="0 0 60 24" preserveAspectRatio="none">
                <polyline points="0,16 10,14 20,12 30,8 40,10 50,6 60,4" stroke="var(--accent)" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="stat-card__delta">{{ $totalSites > 0 ? round($syncedToday / $totalSites * 100) : 0 }}% of sites</div>
    </div>

    <div class="stat-card">
        <div class="stat-card__label">Errors</div>
        <div class="stat-card__body">
            <span class="stat-card__value">{{ $errorCount }}</span>
            @php $errColor = $errorCount > 0 ? 'var(--danger)' : 'var(--success)'; @endphp
            <svg class="stat-card__spark" viewBox="0 0 60 24" preserveAspectRatio="none">
                <polyline points="0,20 10,20 20,18 30,18 40,14 50,10 60,{{ $errorCount > 0 ? 6 : 20 }}" stroke="{{ $errColor }}" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="stat-card__delta" style="color:{{ $errColor }}">
            {{ $errorCount > 0 ? 'Needs review' : 'All clear' }}
        </div>
    </div>
</div>

{{-- ── 2-col main grid ── --}}
<div class="dash-grid">

    {{-- Left: Recent activity --}}
    <div class="dash-card">
        <div class="dash-card__head">
            <h3 class="dash-card__title">Recent activity</h3>
            <a href="{{ route('logs.system') }}" class="dash-card__link">View all</a>
        </div>
        <div class="dash-activity">
            @forelse($recentActivity as $log)
            @php
                $kind = match($log->level) { 'error' => 'err', 'warning' => 'warn', 'info' => 'info', default => 'ok' };
                $when = $log->created_at->diffForHumans(null, true, true);
            @endphp
            <div class="activity-row">
                <span class="activity-row__when">{{ $when }}</span>
                <div class="activity-row__body">
                    <span class="activity-dot activity-dot--{{ $kind }}"></span>
                    @if($log->user)
                        <span class="activity-avatar">{{ mb_strtoupper(mb_substr($log->user->name, 0, 1, 'UTF-8'), 'UTF-8') }}</span>
                        <span class="activity-row__text"><b>{{ $log->user->name }}</b> {{ $log->event }}</span>
                    @else
                        <span class="activity-row__system">system</span>
                        <span class="activity-row__text">{{ $log->event }}</span>
                    @endif
                </div>
                <span class="activity-row__kind activity-row__kind--{{ $kind }}">{{ $log->level }}</span>
            </div>
            @empty
            <div class="dash-empty">No activity yet</div>
            @endforelse
        </div>
    </div>

    {{-- Right: Sync status + Top sites --}}
    <div class="dash-aside">

        <div class="dash-card">
            <div class="dash-card__head">
                <h3 class="dash-card__title">Sync status</h3>
            </div>
            <div class="dash-status-bars">
                @php
                    $ok   = max(0, $activeSites - $errorCount);
                    $bars = [
                        'Online'  => [$ok, 'var(--success)'],
                        'Errors'  => [$errorCount, 'var(--danger)'],
                        'Offline' => [$totalSites - $activeSites, 'var(--text-3)'],
                    ];
                @endphp
                @foreach($bars as $label => [$count, $color])
                @php $pct = $totalSites > 0 ? round($count / $totalSites * 100) : 0; @endphp
                <div class="status-bar">
                    <div class="status-bar__meta">
                        <span style="color:var(--text-2)">{{ $label }}</span>
                        <span style="font-family:var(--font-mono);color:var(--text-3);font-size:12px">{{ $count }} · {{ $pct }}%</span>
                    </div>
                    <div class="status-bar__track">
                        <div class="status-bar__fill" style="width:{{ $pct }}%;background:{{ $color }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="dash-card dash-card--no-pad">
            <div class="dash-card__head" style="padding:14px 20px">
                <h3 class="dash-card__title">Top sites</h3>
                <a href="{{ route('sites.index') }}" class="dash-card__link">All sites</a>
            </div>
            @forelse($topSites as $site)
            @php
                $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
                $h = 0;
                foreach (str_split($site->name) as $c) { $h = ($h * 31 + ord($c)) % 360; }
            @endphp
            <a href="{{ route('sites.show', $site) }}" class="top-site-row">
                <span class="top-site-row__favicon" style="background:oklch(0.94 0.04 {{ $h }});color:oklch(0.4 0.1 {{ $h }});">{{ $letter }}</span>
                <div class="top-site-row__info">
                    <span class="top-site-row__name">{{ $site->name }}</span>
                    <span class="top-site-row__url">{{ parse_url($site->url, PHP_URL_HOST) ?: $site->url }}</span>
                </div>
                <span class="top-site-row__count">{{ $site->contact_total }}</span>
            </a>
            @empty
            <div class="dash-empty" style="padding:20px">No sites yet</div>
            @endforelse
        </div>

    </div>
</div>

@endsection
