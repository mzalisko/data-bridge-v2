@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/logs.css') }}?v={{ filemtime(public_path('assets/css/pages/logs.css')) }}">
@endpush

@section('title', 'Логи синхронізації')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Логи</h1>
</div>

<div class="logs-tabs">
    <a href="{{ route('logs.system') }}" class="logs-tab">Системні</a>
    <a href="{{ route('logs.sync') }}" class="logs-tab is-active">Синхронізація</a>
</div>

<div class="logs-controls">
    <form method="GET" action="{{ route('logs.sync') }}" class="logs-controls__row" id="logs-filter-form">
        <div class="logs-search-wrap logs-search-wrap--select">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--text-3);pointer-events:none">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <select name="site_id" class="logs-site-select" onchange="document.getElementById('logs-filter-form').submit()">
                <option value="">Всі сайти</option>
                @foreach($sites as $site)
                    <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                        {{ $site->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="logs-filter-pills">
            <span class="logs-filter-label">Статус:</span>
            @foreach(['' => 'Всі', 'ok' => 'OK', 'no_changes' => 'Без змін', 'error' => 'Помилка'] as $val => $label)
                <a href="{{ route('logs.sync', array_merge(request()->except('status', 'page'), $val !== '' ? ['status' => $val] : [])) }}"
                   class="filter-pill {{ request('status', '') === $val ? 'is-active' : '' }}">
                    @if($val !== '')
                        <span class="filter-pill__dot sync-dot sync-dot--{{ $val }}"></span>
                    @endif
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if(request()->hasAny(['status', 'site_id']))
            <a href="{{ route('logs.sync') }}" class="btn--ghost btn--ghost-sm logs-reset">
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
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
        </svg>
        <span>Логів не знайдено</span>
    </div>
@else
<div class="log-table-wrap">
    <table class="log-table">
        <thead>
            <tr>
                <th style="width:220px">Сайт</th>
                <th style="width:120px">Статус</th>
                <th style="width:100px">Час (мс)</th>
                <th>Помилка</th>
                <th style="width:160px">Дата</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>
                    @if($log->site)
                        <a href="{{ route('sites.show', $log->site_id) }}" class="log-site-link">
                            {{ $log->site->name }}
                        </a>
                        <span class="log-site-url">{{ parse_url($log->site->url, PHP_URL_HOST) ?: $log->site->url }}</span>
                    @else
                        <span class="cell-muted">—</span>
                    @endif
                </td>
                <td>
                    <span class="sync-badge sync-badge--{{ $log->status }}">
                        @match($log->status)
                            'ok'         => 'OK',
                            'no_changes' => 'Без змін',
                            'error'      => 'Помилка',
                            default      => $log->status,
                        @endmatch
                    </span>
                </td>
                <td class="cell-mono">{{ $log->duration_ms !== null ? number_format($log->duration_ms) : '—' }}</td>
                <td class="log-error-cell">{{ $log->error_msg ?? '—' }}</td>
                <td class="cell-muted">{{ $log->synced_at?->format('d.m.Y H:i') ?? '—' }}</td>
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
