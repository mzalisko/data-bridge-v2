@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/dashboard.css') }}?v={{ filemtime(public_path('assets/css/pages/dashboard.css')) }}">
@endpush

@section('title', 'Dashboard')

@section('content')

{{-- ── Page header ── --}}
<div class="page-toolbar">
    <div>
        <h1 class="page-title">Dashboard</h1>
    </div>
    @if($errorCount > 0)
        <div class="db-status db-status--err">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ $errorCount }} {{ $errorCount === 1 ? 'помилка' : 'помилок' }}
        </div>
    @else
        <div class="db-status db-status--ok">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Всі сайти в нормі
        </div>
    @endif
</div>

{{-- ── Stat cards ── --}}
<div class="dash-stats">
    <div class="dash-stat-card">
        <div class="dash-stat-card__icon dash-stat-card__icon--blue">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </div>
        <div class="dash-stat-card__body">
            <div class="dash-stat-card__value">{{ $totalSites }}</div>
            <div class="dash-stat-card__label">Всього сайтів</div>
        </div>
        <div class="dash-stat-card__sub">{{ $activeSites }} активних</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-card__icon dash-stat-card__icon--green">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 2.1l4 4-4 4"/><path d="M3 12.2v-2a4 4 0 0 1 4-4h13.8"/><path d="M7 21.9l-4-4 4-4"/><path d="M21 11.8v2a4 4 0 0 1-4 4H3.2"/></svg>
        </div>
        <div class="dash-stat-card__body">
            <div class="dash-stat-card__value">{{ $syncedToday }}</div>
            <div class="dash-stat-card__label">Синхронізовано сьогодні</div>
        </div>
        <div class="dash-stat-card__sub">сайтів</div>
    </div>

    <div class="dash-stat-card {{ $errorCount > 0 ? 'dash-stat-card--danger' : '' }}">
        <div class="dash-stat-card__icon {{ $errorCount > 0 ? 'dash-stat-card__icon--red' : 'dash-stat-card__icon--muted' }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="dash-stat-card__body">
            <div class="dash-stat-card__value">{{ $errorCount }}</div>
            <div class="dash-stat-card__label">Помилки синхронізації</div>
        </div>
        <div class="dash-stat-card__sub">{{ $errorCount === 0 ? 'все чисто' : 'потребують уваги' }}</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-card__icon dash-stat-card__icon--purple">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="dash-stat-card__body">
            <div class="dash-stat-card__value">{{ number_format($totalContacts) }}</div>
            <div class="dash-stat-card__label">Всього контактів</div>
        </div>
        <div class="dash-stat-card__sub">телефони · ціни · адреси</div>
    </div>
</div>

{{-- ── 2-column layout ── --}}
<div class="db-layout">

    {{-- ── CENTER: Sync Timeline ── --}}
    <div class="db-main">

        {{-- Timeline --}}
        <div class="db-card" id="syncs-card">
            <div class="db-card__header">
                <span class="db-card__title">Стрічка синхронізацій</span>
                <span class="db-card__count">{{ $recentSyncs->total() }} подій</span>
            </div>

            @if($recentSyncs->isEmpty())
                <div class="db-empty">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 2.1l4 4-4 4"/><path d="M3 12.2v-2a4 4 0 0 1 4-4h13.8"/><path d="M7 21.9l-4-4 4-4"/><path d="M21 11.8v2a4 4 0 0 1-4 4H3.2"/></svg>
                    <p>Синхронізацій поки немає</p>
                </div>
            @else
                <ul class="db-timeline" id="syncs-timeline">
                    @foreach($recentSyncs as $sync)
                    <li class="db-event db-event--{{ $sync->status }}">
                        <span class="db-event__dot"></span>
                        <div class="db-event__body">
                            <div class="db-event__top">
                                <a href="{{ route('sites.show', $sync->site_id) }}" class="db-event__site">{{ $sync->site?->name ?? 'Невідомий сайт' }}</a>
                                <span class="db-event__time">{{ $sync->synced_at?->diffForHumans() ?? '' }}</span>
                            </div>
                            @if($sync->status === 'ok')
                                <span class="db-event__desc">Синхронізовано · {{ $sync->duration_ms }}ms</span>
                            @elseif($sync->status === 'no_changes')
                                <span class="db-event__desc">Без змін · {{ $sync->duration_ms }}ms</span>
                            @else
                                <span class="db-event__desc db-event__desc--err">{{ $sync->error_msg ?? 'Помилка синхронізації' }}</span>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ul>
                @if($recentSyncs->hasPages())
                <div class="db-card__footer" id="syncs-pagination">
                    {{ $recentSyncs->links() }}
                </div>
                @endif
            @endif
        </div>

        {{-- Recent system logs --}}
        @if($recentLogs->isNotEmpty())
        <div class="db-card" id="logs-card">
            <div class="db-card__header">
                <span class="db-card__title">Системні події</span>
                <span class="db-card__count">{{ $recentLogs->total() }} подій</span>
            </div>
            <ul class="db-timeline" id="logs-timeline">
                @foreach($recentLogs as $log)
                <li class="db-event db-event--{{ $log->level ?? 'info' }}">
                    <span class="db-event__dot"></span>
                    <div class="db-event__body">
                        <div class="db-event__top">
                            <span class="db-event__site">{{ $log->event }}</span>
                            <span class="db-event__time">{{ $log->created_at?->diffForHumans() ?? '' }}</span>
                        </div>
                        <span class="db-event__desc">{{ $log->user?->email ?? 'system' }}</span>
                    </div>
                </li>
                @endforeach
            </ul>
            @if($recentLogs->hasPages())
            <div class="db-card__footer" id="logs-pagination">
                {{ $recentLogs->links() }}
            </div>
            @endif
        </div>
        @endif

    </div>{{-- /db-main --}}

    {{-- ── SIDEBAR ── --}}
    <aside class="db-side">

        {{-- Problems --}}
        <div class="db-card">
            <div class="db-card__header">
                <span class="db-card__title">
                    @if($problemSites->isEmpty())
                        <span style="color:var(--success)">✓</span> Проблем немає
                    @else
                        <span style="color:var(--danger)">⚠</span> Проблеми ({{ $problemSites->count() }})
                    @endif
                </span>
            </div>
            @if($problemSites->isEmpty())
                <p class="db-side__empty">Усі сайти синхронізовані</p>
            @else
                <ul class="db-problem-list">
                    @foreach($problemSites as $site)
                    <li class="db-problem-item">
                        <div class="db-problem-item__favicon" style="background:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }}20;color:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }}">
                            {{ mb_strtoupper(mb_substr($site->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                        </div>
                        <div class="db-problem-item__info">
                            <span class="db-problem-item__name">{{ $site->name }}</span>
                            <span class="db-problem-item__err">{{ $site->latestSyncLog?->error_msg ?? "Помилка з'єднання" }}</span>
                        </div>
                        <a href="{{ route('sites.show', $site) }}" class="btn-icon" title="Переглянути">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Favorites --}}
        @if($favoriteSites->isNotEmpty())
        <div class="db-card">
            <div class="db-card__header">
                <span class="db-card__title">Улюблені сайти</span>
                <a href="{{ route('sites.index') }}" class="db-card__link">Всі →</a>
            </div>
            <ul class="db-quick-list">
                @foreach($favoriteSites as $site)
                <li>
                    <a href="{{ route('sites.show', $site) }}" class="db-quick-item">
                        <div class="db-quick-item__favicon" style="background:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }}20;color:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }}">
                            {{ mb_strtoupper(mb_substr($site->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                        </div>
                        <span class="db-quick-item__name">{{ $site->name }}</span>
                        <span class="db-quick-item__dot" style="background:{{ $site->latestSyncLog?->status === 'ok' ? 'var(--success)' : ($site->latestSyncLog ? 'var(--danger)' : 'var(--text-3)') }}"></span>
                    </a>
                    <button class="db-fav-btn is-fav" title="Прибрати з улюблених" onclick="toggleFavorite(event, this, {{ $site->id }})">★</button>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Recently Synchronized --}}
        <div class="db-card">
            <div class="db-card__header">
                <span class="db-card__title">Нещодавно синхронізовані</span>
            </div>
            @if($quickSites->isEmpty())
                <p class="db-side__empty">Немає недавніх подій</p>
            @else
                <ul class="db-quick-list">
                    @foreach($quickSites as $site)
                    @php
                        $isFav        = in_array($site->id, $favoriteIds);
                        $syncStatus   = $site->latestSyncLog?->status;
                        $isConnected  = $site->apiKey && !$site->apiKey->revoked_at;
                        $dotColor     = match(true) {
                            $syncStatus === 'ok'         => 'var(--success)',
                            $syncStatus === 'no_changes' => 'var(--success)',
                            $syncStatus === 'error'      => 'var(--danger)',
                            default                      => 'var(--text-3)',
                        };
                    @endphp
                    <li>
                        <a href="{{ route('sites.show', $site) }}" class="db-quick-item">
                            <div class="db-quick-item__favicon" style="background:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }}20;color:{{ sprintf('#%06x', crc32($site->name) & 0xFFFFFF) }}">
                                {{ mb_strtoupper(mb_substr($site->name, 0, 1, 'UTF-8'), 'UTF-8') }}
                            </div>
                            <div class="db-quick-item__info">
                                <span class="db-quick-item__name">{{ $site->name }}</span>
                                <span class="db-quick-item__sub">
                                    @if($isConnected)
                                        <span style="color:var(--success)">●</span> Підключений
                                        @if($site->latestSyncLog?->synced_at)
                                            · {{ $site->latestSyncLog->synced_at->diffForHumans() }}
                                        @endif
                                    @else
                                        <span style="color:var(--text-3)">○</span> Без ключа
                                    @endif
                                </span>
                            </div>
                            <span class="db-quick-item__dot" style="background:{{ $dotColor }}"></span>
                        </a>
                        <button class="db-fav-btn {{ $isFav ? 'is-fav' : '' }}"
                                title="{{ $isFav ? 'Прибрати з улюблених' : 'Додати до улюблених' }}"
                                onclick="toggleFavorite(event, this, {{ $site->id }})">★</button>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </aside>{{-- /db-side --}}

</div>

@push('scripts')
<script>
(function () {
    function ajaxCard(cardId, pageParam) {
        var card = document.getElementById(cardId);
        if (!card) return;
        card.addEventListener('click', function (e) {
            var link = e.target.closest('a[href]');
            if (!link) return;
            var href = link.getAttribute('href');
            if (!href || !href.includes(pageParam)) return;
            e.preventDefault();
            card.style.opacity = '0.6';
            fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var fresh = doc.getElementById(cardId);
                    if (fresh) { card.innerHTML = fresh.innerHTML; history.pushState(null, '', href); }
                    card.style.opacity = '1';
                })
                .catch(function () { card.style.opacity = '1'; });
        });
    }

    ajaxCard('syncs-card', 'syncs_page');
    ajaxCard('logs-card',  'logs_page');
})();
</script>
@endpush

@endsection
