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

<form method="GET" action="{{ route('logs.sync') }}" class="logs-filter">
    <select name="site_id" onchange="this.form.submit()">
        <option value="">Всі сайти</option>
        @foreach($sites as $site)
            <option value="{{ $site->id }}" {{ request('site_id') == $site->id ? 'selected' : '' }}>
                {{ $site->name }}
            </option>
        @endforeach
    </select>

    <select name="status" onchange="this.form.submit()">
        <option value="">Всі статуси</option>
        @foreach(['ok' => 'OK', 'no_changes' => 'Без змін', 'error' => 'Помилка'] as $val => $label)
            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>

    @if(request()->hasAny(['status', 'site_id']))
        <a href="{{ route('logs.sync') }}" class="btn-ghost">Скинути</a>
    @endif
</form>

@if($logs->isEmpty())
    <div class="logs-empty">Логів не знайдено</div>
@else
<table class="log-table">
    <thead>
        <tr>
            <th>Сайт</th>
            <th>Статус</th>
            <th>Час (мс)</th>
            <th>Помилка</th>
            <th>Дата</th>
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
                @else
                    <span class="cell-muted">—</span>
                @endif
            </td>
            <td>
                <span class="status-badge status-badge--{{ $log->status }}">
                    @match($log->status)
                        'ok'         => 'OK',
                        'no_changes' => 'Без змін',
                        'error'      => 'Помилка',
                        default      => $log->status,
                    @endmatch
                </span>
            </td>
            <td class="cell-muted">{{ $log->duration_ms !== null ? $log->duration_ms . ' мс' : '—' }}</td>
            <td class="cell-muted">{{ $log->error_msg ?? '—' }}</td>
            <td class="cell-muted">{{ $log->synced_at?->format('d.m.Y H:i:s') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="pagination-wrap">
    {{ $logs->links() }}
</div>
@endif

@endsection
