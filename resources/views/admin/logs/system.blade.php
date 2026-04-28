@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/logs.css') }}?v={{ filemtime(public_path('assets/css/pages/logs.css')) }}">
@endpush

@section('title', 'Системні логи')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Логи</h1>
</div>

<div class="logs-tabs">
    <a href="{{ route('logs.system') }}" class="logs-tab is-active">Системні</a>
    <a href="{{ route('logs.sync') }}" class="logs-tab">Синхронізація</a>
</div>

<div class="logs-controls">
    <form method="GET" action="{{ route('logs.system') }}" class="logs-controls__row" id="logs-filter-form">
        <div class="logs-search-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="event" class="logs-search-input"
                   value="{{ request('event') }}" placeholder="Пошук події…"
                   onchange="document.getElementById('logs-filter-form').submit()">
        </div>

        <div class="logs-filter-pills">
            <span class="logs-filter-label">Рівень:</span>
            @foreach(['' => 'Всі', 'info' => 'Info', 'warning' => 'Warning', 'error' => 'Error', 'debug' => 'Debug'] as $val => $label)
                <a href="{{ route('logs.system', array_merge(request()->except('level', 'page'), $val !== '' ? ['level' => $val] : [])) }}"
                   class="filter-pill {{ request('level', '') === $val ? 'is-active' : '' }}">
                    @if($val !== '')
                        <span class="filter-pill__dot level-dot level-dot--{{ $val }}"></span>
                    @endif
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if(request()->hasAny(['level', 'event']))
            <a href="{{ route('logs.system') }}" class="btn--ghost btn--ghost-sm logs-reset">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Скинути
            </a>
        @endif
    </form>
</div>

@if($logs->isEmpty())
    <div class="logs-empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.3">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
        </svg>
        <span>Логів не знайдено</span>
    </div>
@else
<div class="log-table-wrap">
    <table class="log-table">
        <thead>
            <tr>
                <th style="width:100px">Рівень</th>
                <th>Подія</th>
                <th style="width:160px">Користувач</th>
                <th style="width:130px">IP</th>
                <th style="width:160px">Час</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>
                    <span class="level-badge level-badge--{{ $log->level }}">{{ $log->level }}</span>
                </td>
                <td class="log-event-cell">{{ $log->event }}</td>
                <td class="cell-muted">{{ $log->user?->name ?? '—' }}</td>
                <td class="cell-mono">{{ $log->ip_address ?? '—' }}</td>
                <td class="cell-muted">{{ $log->created_at?->format('d.m.Y H:i') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="pagination-wrap">
    {{ $logs->links() }}
</div>
@endif

@endsection
