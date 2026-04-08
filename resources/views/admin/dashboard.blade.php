@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="stat-grid">
    <x-stat-card
        label="Сайти"
        :value="$stats['sites_total']"
        :sub="$stats['sites_ok'] . ' активних'"
    />
    <x-stat-card
        label="Офлайн"
        :value="$stats['sites_off']"
        sub="зупинено"
    />
    <x-stat-card
        label="Групи"
        :value="$stats['groups_total']"
    />
    <x-stat-card
        label="Користувачі"
        :value="$stats['users_total']"
        sub="активних"
    />
    <x-stat-card
        label="Синхронізацій сьогодні"
        :value="$stats['syncs_today']"
        :sub="$stats['syncs_failed'] > 0 ? $stats['syncs_failed'] . ' помилок' : 'без помилок'"
    />
</div>

<div class="card">
    <div class="section-header">
        <h2 class="section-title">Останні події</h2>
    </div>

    @if($recentLogs->isEmpty())
        <p class="empty-state">Подій ще немає</p>
    @else
        <ul class="log-list">
            @foreach($recentLogs as $log)
            <li class="log-item log-item--{{ $log->level }}">
                <span class="log-item__event">{{ $log->event }}</span>
                <span class="log-item__meta">
                    {{ $log->user?->email ?? 'system' }}
                    &middot;
                    {{ $log->created_at->diffForHumans() }}
                </span>
            </li>
            @endforeach
        </ul>
    @endif
</div>

@endsection
