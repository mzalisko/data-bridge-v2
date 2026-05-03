@extends('layouts.app')

@section('title', 'Журнал синхронізацій')

@section('content')
<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <h1 class="page-head__title">Журнал активності</h1>
            <p class="page-head__subtitle">Синхронізації по всіх сайтах.</p>
        </div>
        <div class="page-head__actions">
            <button class="btn btn--secondary btn--md">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>
                </svg>
                Експорт журналу
            </button>
        </div>
    </div>

    {{-- ========= SOURCE TABS ========= --}}
    <div class="region-tabs" style="border-bottom:none;background:transparent;padding:0;">
        <a href="{{ route('logs.system') }}" class="{{ request()->routeIs('logs.system') ? 'is-active' : '' }}">Системні події</a>
        <a href="{{ route('logs.sync') }}" class="{{ request()->routeIs('logs.sync') ? 'is-active' : '' }}">Синхронізації</a>
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
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук сайту…">
            </div>
            <div class="select-wrap">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Всі статуси</option>
                    @foreach(['success' => 'Успішно', 'error' => 'Помилка', 'pending' => 'Очікує'] as $st => $stLabel)
                        <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ $stLabel }}</option>
                    @endforeach
                </select>
                <span class="select-wrap__chevron">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <div style="flex:1"></div>
            <span style="font-size:12px;color:var(--text-3);">{{ $logs->total() }} подій</span>
        </form>

        {{-- Rows --}}
        @forelse($logs as $log)
            @php
                $kind = $log->status === 'success' ? 'success' : ($log->status === 'error' ? 'danger' : 'warning');
                $when = $log->synced_at?->diffForHumans() ?? '—';
            @endphp
            <div class="activity-row">
                <span class="activity-row__when">{{ $when }}</span>
                <div class="activity-row__body">
                    <span class="dot dot--{{ $kind }}"></span>
                    <span class="activity-row__who-system">system</span>
                    <span class="activity-row__action">
                        {{ $log->status === 'success' ? 'синхронізовано' : ($log->status === 'error' ? 'помилка синхронізації' : 'очікує') }}
                    </span>
                    @if($log->site)
                        <a href="{{ route('sites.show', $log->site_id) }}" class="activity-row__target">{{ $log->site->name }}</a>
                    @else
                        <span class="activity-row__target">невідомий сайт</span>
                    @endif
                    @if($log->status === 'error' && $log->error_msg)
                        <span style="color:var(--text-3);font-size:12px;">· {{ \Illuminate\Support\Str::limit($log->error_msg, 50) }}</span>
                    @elseif($log->duration_ms)
                        <span style="color:var(--text-3);font-size:12px;">· {{ $log->duration_ms }}ms</span>
                    @endif
                </div>
                <span class="activity-row__kind">{{ $log->status }}</span>
            </div>
        @empty
            <div style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">Подій синхронізації не знайдено</div>
        @endforelse

        @if($logs->hasPages())
            <div>{{ $logs->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>
@endsection
