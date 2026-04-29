@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/logs.css') }}?v={{ filemtime(public_path('assets/css/pages/logs.css')) }}">
@endpush

@section('title', 'Sync log')

@section('content')

<div class="page-toolbar" style="margin-bottom:20px;">
    <div>
        <h1 class="page-title">Activity log</h1>
        <div class="page-subtitle">Sync events and errors across all sites.</div>
    </div>
</div>

{{-- Tab bar --}}
<div style="display:flex;gap:0;margin-bottom:20px;border-bottom:1px solid var(--border-2);">
    <a href="{{ route('logs.system') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap;border-bottom:2px solid transparent;margin-bottom:-1px;color:var(--text-3);transition:color .15s;">
        System
    </a>
    <a href="{{ route('logs.sync') }}"
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap;border-bottom:2px solid var(--accent);margin-bottom:-1px;color:var(--text);transition:color .15s;">
        Sync
    </a>
</div>

{{-- Card --}}
<div class="card" style="padding:0;">

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('logs.sync') }}"
          style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-2);">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search site…"
               class="form-input" style="flex:1;max-width:340px;height:34px;padding:0 12px;font-size:13px;">
        <select name="status" onchange="this.form.submit()"
                class="form-select" style="height:34px;padding:0 10px;font-size:13px;width:auto;">
            <option value="">All statuses</option>
            @foreach(['success', 'error', 'pending'] as $st)
                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
        @if(request()->hasAny(['status', 'search']))
            <a href="{{ route('logs.sync') }}" class="btn-ghost" style="height:34px;">Clear</a>
        @endif
        <div style="flex:1;"></div>
        <span style="font-size:12px;color:var(--text-3);">{{ $logs->total() }} events</span>
    </form>

    {{-- Rows --}}
    @if($logs->isEmpty())
        <div style="padding:40px 20px;text-align:center;color:var(--text-3);font-size:13px;">No sync events found</div>
    @else
        @foreach($logs as $log)
        @php
            $dotColor = $log->status === 'success' ? 'var(--success)' : ($log->status === 'error' ? 'var(--danger)' : 'var(--warning)');
        @endphp
        <div style="display:grid;grid-template-columns:120px 1fr auto;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border-2);">
            <span style="color:var(--text-3);font-size:12px;font-family:var(--font-mono);">
                {{ $log->synced_at?->format('d.m.Y H:i') ?? '—' }}
            </span>
            <div style="display:flex;align-items:center;gap:8px;font-size:13px;min-width:0;">
                <span style="width:6px;height:6px;border-radius:99px;background:{{ $dotColor }};flex-shrink:0;"></span>
                @if($log->site)
                    <a href="{{ route('sites.show', $log->site_id) }}"
                       style="font-weight:500;color:var(--text);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">{{ $log->site->name }}</a>
                @else
                    <span style="color:var(--text-3);">Unknown site</span>
                @endif
                <span style="color:var(--text-2);">
                    @if($log->status === 'success')
                        synced · {{ $log->duration_ms ? $log->duration_ms.'ms' : '' }}
                    @else
                        {{ $log->error_msg ?? 'sync error' }}
                    @endif
                </span>
            </div>
            <span style="font-size:11px;color:{{ $dotColor }};white-space:nowrap;">{{ $log->status }}</span>
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
