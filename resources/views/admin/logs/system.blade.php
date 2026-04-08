@extends('layouts.app')

@section('title', 'Системні логи')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Логи</h1>
</div>

<div class="logs-tabs">
    <a href="{{ route('logs.system') }}" class="logs-tab is-active">Системні</a>
    <a href="{{ route('logs.sync') }}" class="logs-tab">Синхронізація</a>
</div>

<form method="GET" action="{{ route('logs.system') }}" class="logs-filter">
    <select name="level" onchange="this.form.submit()">
        <option value="">Всі рівні</option>
        @foreach(['info', 'warning', 'error', 'debug'] as $lvl)
            <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>
                {{ ucfirst($lvl) }}
            </option>
        @endforeach
    </select>
    <input type="text" name="event" value="{{ request('event') }}" placeholder="Пошук події…">
    <button type="submit" class="btn-primary">Фільтр</button>
    @if(request()->hasAny(['level', 'event']))
        <a href="{{ route('logs.system') }}" class="btn-ghost">Скинути</a>
    @endif
</form>

@if($logs->isEmpty())
    <div class="logs-empty">Логів не знайдено</div>
@else
<table class="log-table">
    <thead>
        <tr>
            <th>Рівень</th>
            <th>Подія</th>
            <th>Користувач</th>
            <th>IP</th>
            <th>Час</th>
        </tr>
    </thead>
    <tbody>
        @foreach($logs as $log)
        <tr>
            <td>
                <span class="level-badge level-badge--{{ $log->level }}">{{ $log->level }}</span>
            </td>
            <td>{{ $log->event }}</td>
            <td class="cell-muted">{{ $log->user?->name ?? '—' }}</td>
            <td class="cell-mono">{{ $log->ip_address ?? '—' }}</td>
            <td class="cell-muted">{{ $log->created_at?->format('d.m.Y H:i:s') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="pagination-wrap">
    {{ $logs->links() }}
</div>
@endif

@endsection
