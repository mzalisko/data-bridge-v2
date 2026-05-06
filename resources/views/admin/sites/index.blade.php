@extends('layouts.app')

@section('title', 'Сайти')

@section('content')
<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <h1 class="page-head__title">Сайти</h1>
            <p class="page-head__subtitle">{{ $totalCount }} сайтів в {{ $groups->count() }} групах</p>
        </div>
        <div class="page-head__actions">
            <button class="btn btn--secondary btn--md">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 4v11"/><path d="m7 11 5 5 5-5"/><path d="M5 20h14"/>
                </svg>
                Експорт
            </button>
            <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-site-create')">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Додати сайт
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    {{-- ========= MAIN CARD ========= --}}
    <div class="card card--flush">

        {{-- Toolbar --}}
        <form method="GET" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-2);">
            <div class="input" style="flex:1;max-width:380px;">
                <span class="input__icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Пошук сайту за назвою або доменом…">
            </div>
            <div class="select-wrap">
                <select name="group_id" onchange="this.form.submit()">
                    <option value="">Всі групи</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" {{ (string)request('group_id') === (string)$g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                    @endforeach
                </select>
                <span class="select-wrap__chevron">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <div class="select-wrap">
                <select name="status" onchange="this.form.submit()">
                    <option value="">Всі статуси</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Онлайн</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Офлайн</option>
                </select>
                <span class="select-wrap__chevron">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                </span>
            </div>
            <div style="flex:1"></div>
            <span style="font-size:12px;color:var(--text-3);">{{ $sites->total() }} з {{ $totalCount }}</span>
        </form>

        {{-- Table --}}
        <div style="overflow:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th style="width:36px;"><input type="checkbox" id="check-all"></th>
                        <th>Сайт</th>
                        <th>Група</th>
                        <th>Статус</th>
                        <th>Телефони</th>
                        <th>Остання синхронізація</th>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sites as $site)
                        @php
                            $statusName = $site->is_active ? 'Онлайн' : 'Офлайн';
                            $syncLog    = $site->latestSyncLog;
                            $syncWhen   = $syncLog?->synced_at?->diffForHumans() ?? '—';
                            $groupColor = $site->siteGroup?->color ?? '#71717a';
                        @endphp
                        <tr onclick="window.location='{{ route('sites.show', $site) }}'" data-site-id="{{ $site->id }}" class="site-row">
                            <td onclick="event.stopPropagation()">
                                <input type="checkbox" class="bulk-checkbox" name="ids[]" value="{{ $site->id }}" onchange="bulkUpdateBar()">
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <x-favicon :name="$site->name" :size="22"/>
                                    <div>
                                        <div style="font-weight:500;color:var(--text);">{{ $site->name }}</div>
                                        <div style="color:var(--text-3);font-size:11px;font-family:var(--font-mono);">{{ $site->url ? (parse_url($site->url, PHP_URL_HOST) ?: $site->url) : '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($site->siteGroup)
                                    <span class="group-chip">
                                        <span class="group-chip__dot" style="background:{{ $groupColor }}"></span>
                                        {{ $site->siteGroup->name }}
                                    </span>
                                @else
                                    <span style="color:var(--text-3);">—</span>
                                @endif
                            </td>
                            <td><x-status-pill :status="$statusName"/></td>
                            <td class="mono">{{ $site->phones?->count() ?? 0 }}</td>
                            <td style="color:var(--text-3);font-size:12px;">{{ $syncWhen }}</td>
                            <td onclick="event.stopPropagation()">
                                <button class="icon-btn">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">Немає сайтів за вибраними фільтрами</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($sites->hasPages())
            <div>{{ $sites->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>

{{-- ========= CREATE DRAWER ========= --}}
<div class="drawer-overlay" id="drawer-site-create-overlay" onclick="closeDrawer('drawer-site-create')"></div>
<div class="drawer" id="drawer-site-create">
    <div class="drawer__header">
        <span class="drawer__title">Додати сайт</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-site-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.store') }}" class="form-stack" id="form-site-create">
            @csrf
            @include('admin.sites._form', ['site' => null, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-site-create')">Скасувати</button>
        <button type="submit" form="form-site-create" class="btn btn--primary btn--md">Створити</button>
    </div>
</div>

{{-- ── Bulk action bar (activates when sites are selected) ── --}}
<div class="bulk-bar" id="bulk-bar">
    <div class="bulk-bar__count">
        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle;margin-right:4px;"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
        <span id="bulk-count">0</span> сайтів
    </div>
    <div class="bulk-bar__actions">
        <button class="bulk-bar__btn" onclick="bulkAddPhone()" title="Додати однаковий телефон у всі вибрані сайти">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="display:inline-block;vertical-align:middle;margin-right:4px;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            Телефон
        </button>
        <button class="bulk-bar__btn" onclick="bulkAddPrice()" title="Додати ціну у всі вибрані">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="display:inline-block;vertical-align:middle;margin-right:4px;"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Ціна
        </button>
        <button class="bulk-bar__btn" onclick="bulkAddSocial()" title="Додати соцмережу у всі вибрані">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="display:inline-block;vertical-align:middle;margin-right:4px;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            Соцмережа
        </button>
        <button class="bulk-bar__btn" onclick="bulkAddGeo()" title="Додати гео у всі вибрані">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="display:inline-block;vertical-align:middle;margin-right:4px;"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Гео
        </button>
        <button class="bulk-bar__btn" style="color:var(--danger);border-color:rgba(225,29,72,.3);" onclick="bulkClear()">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" style="display:inline-block;vertical-align:middle;margin-right:2px;"><path d="M18 6 6 18M6 6l12 12"/></svg>
            Скасувати
        </button>
    </div>
</div>

@push('scripts')
<script>
// ── Bulk selection ─────────────────────────────────────────────
function bulkGetIds() {
    return Array.from(document.querySelectorAll('.bulk-checkbox:checked')).map(function(cb) {
        return parseInt(cb.value, 10);
    });
}
function bulkUpdateBar() {
    var ids  = bulkGetIds();
    var bar  = document.getElementById('bulk-bar');
    var cnt  = document.getElementById('bulk-count');
    if (cnt) cnt.textContent = ids.length;
    if (bar) bar.classList.toggle('is-visible', ids.length > 0);
}
function bulkClear() {
    document.querySelectorAll('.bulk-checkbox:checked').forEach(function(cb) { cb.checked = false; });
    bulkUpdateBar();
}

// Stub handlers — replace with actual UI (drawer/modal) when implementing bulk
function bulkAddPhone()  { alert('Bulk add phone — TODO: open compact drawer with POST ' + '{{ route("bulk.phones") }}'); }
function bulkAddPrice()  { alert('Bulk add price — TODO: open compact drawer with POST ' + '{{ route("bulk.prices") }}'); }
function bulkAddSocial() { alert('Bulk add social — TODO: open compact drawer with POST ' + '{{ route("bulk.socials") }}'); }
function bulkAddGeo()    { alert('Bulk add geo — TODO: open compact drawer with POST ' + '{{ route("bulk.geos") }}'); }
</script>
@endpush

@endsection
