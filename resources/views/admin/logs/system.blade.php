@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/logs.css') }}?v={{ filemtime(public_path('assets/css/pages/logs.css')) }}">
@endpush

@section('title', 'System log')

@section('content')

<div class="page-toolbar" style="margin-bottom:20px;">
    <div>
        <h1 class="page-title">Activity log</h1>
        <div class="page-subtitle">Everything that happened across your sites and team.</div>
    </div>
</div>

{{-- Tab bar --}}
<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:1px solid var(--border-2);">
    <a href="{{ route('logs.system') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap;border-bottom:2px solid var(--accent);margin-bottom:-1px;color:var(--text);transition:color .15s;">
        System
    </a>
    <a href="{{ route('logs.sync') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap;border-bottom:2px solid transparent;margin-bottom:-1px;color:var(--text-3);transition:color .15s;">
        Sync
    </a>
</div>

{{-- Card --}}
<div class="card" style="padding:0;">

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('logs.system') }}"
          style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-2);">
        <input type="text" name="event" value="{{ request('event') }}"
               placeholder="Search event…"
               class="form-input" style="flex:1;max-width:340px;height:34px;padding:0 12px;font-size:13px;">
        <select name="level" onchange="this.form.submit()"
                class="form-select" style="height:34px;padding:0 10px;font-size:13px;width:auto;">
            <option value="">All levels</option>
            @foreach(['info', 'warning', 'error', 'debug'] as $lvl)
                <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['level', 'event']))
            <a href="{{ route('logs.system') }}" class="btn-ghost" style="height:34px;">Clear</a>
        @endif
        <div style="flex:1;"></div>
        <span style="font-size:12px;color:var(--text-3);">{{ $logs->total() }} events</span>
    </form>

    {{-- Rows --}}
    @if($logs->isEmpty())
        <div style="padding:40px 20px;text-align:center;color:var(--text-3);font-size:13px;">No events match the current filters</div>
    @else
        @foreach($logs as $log)
        @php
            $dotColor = match($log->level) {
                'error'   => 'var(--danger)',
                'warning' => 'var(--warning)',
                'info'    => 'var(--accent)',
                default   => 'var(--text-3)',
            };
        @endphp
        <div style="display:grid;grid-template-columns:120px 1fr auto;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border-2);">
            <span style="color:var(--text-3);font-size:12px;font-family:var(--font-mono);">
                {{ $log->created_at?->format('d.m.Y H:i') ?? '—' }}
            </span>
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;min-width:0;">
                <span style="width:6px;height:6px;border-radius:99px;background:{{ $dotColor }};flex-shrink:0;"></span>
                @if($log->user)
                    <span style="display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:var(--accent-2);color:var(--accent);font-size:9px;font-weight:700;flex-shrink:0;">
                        {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                    </span>
                    <span style="color:var(--text);font-weight:500;">{{ $log->user->name }}</span>
                @else
                    <span style="color:var(--text-3);font-family:var(--font-mono);font-size:12px;">system</span>
                @endif
                <span style="color:var(--text-2);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $log->event }}</span>
            </div>
            <span style="font-size:11px;color:{{ $dotColor }};white-space:nowrap;">{{ $log->level }}</span>
        </div>
        @endforeach

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div style="padding:12px 16px;border-top:1px solid var(--border-2);">
            {{ $logs->appends(request()->query())->links() }}
        </div>
        @endif
    @endif

</div>

@endsection
