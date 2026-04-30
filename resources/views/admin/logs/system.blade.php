@extends('layouts.app')

@section('title', 'Activity log')

@section('content')
<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <h1 class="page-head__title">Activity log</h1>
            <p class="page-head__subtitle">Everything that happened across your sites and team.</p>
        </div>
        <div class="page-head__actions">
            <button class="btn btn--secondary btn--md">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>
                </svg>
                Export log
            </button>
        </div>
    </div>

    {{-- ========= SOURCE TABS ========= --}}
    <div class="region-tabs" style="border-bottom:none;background:transparent;padding:0;">
        <a href="{{ route('logs.system') }}" class="{{ request()->routeIs('logs.system') ? 'is-active' : '' }}">System events</a>
        <a href="{{ route('logs.sync') }}" class="{{ request()->routeIs('logs.sync') ? 'is-active' : '' }}">Sync events</a>
    </div>

    {{-- ========= MAIN CARD ========= --}}
    <div class="card card--flush">

        {{-- Filter bar --}}
        <form method="GET" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-2);">
            <div class="input" style="flex:1;max-width:380px;">
                <span class="input__icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                </span>
                <input type="text" name="event" value="{{ request('event') }}" placeholder="Search log…">
            </div>
            <div class="select-wrap">
                <select name="level" onchange="this.form.submit()">
                    <option value="">All levels</option>
                    @foreach(['info', 'warning', 'error', 'debug'] as $lvl)
                        <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                    @endforeach
                </select>
                <span class="select-wrap__chevron">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <div style="flex:1"></div>
            <span style="font-size:12px;color:var(--text-3);">{{ $logs->total() }} events</span>
        </form>

        {{-- Rows --}}
        @forelse($logs as $log)
            @php
                $kind = match($log->level) {
                    'error'   => 'danger',
                    'warning' => 'warning',
                    'info'    => 'success',
                    default   => 'muted',
                };
                $when = $log->created_at?->diffForHumans() ?? '—';
                $userName = $log->user?->name;
            @endphp
            <div class="activity-row">
                <span class="activity-row__when">{{ $when }}</span>
                <div class="activity-row__body">
                    <span class="dot dot--{{ $kind }}"></span>
                    @if($userName)
                        <span class="avatar" style="width:20px;height:20px;font-size:9px;background:var(--accent-2);color:var(--accent-text);">{{ mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8') }}</span>
                        <b style="color:var(--text);font-weight:500;">{{ $userName }}</b>
                    @else
                        <span class="activity-row__who-system">system</span>
                    @endif
                    <span class="activity-row__action">{{ $log->event }}</span>
                </div>
                <span class="activity-row__kind">{{ $log->level }}</span>
            </div>
        @empty
            <div style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">No events match the current filters</div>
        @endforelse

        @if($logs->hasPages())
            <div>{{ $logs->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>
@endsection
