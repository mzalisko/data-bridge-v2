@extends('layouts.app')
@section('title', 'Глобальні дані')

@section('content')

{{-- ── Page header ─────────────────────────────────────────── --}}
<div class="page-head">
    <div class="page-head__info">
        <h1 class="page-head__title">Глобальні дані</h1>
        <p class="page-head__subtitle">Пошук і масові операції по всіх сайтах</p>
    </div>
    <div class="page-head__actions">
        <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-bulk-add')">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Додати на сайти
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success" style="margin-bottom:8px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--error" style="margin-bottom:8px;">{{ session('error') }}</div>
@endif

<div class="card card--flush">

    {{-- ── Type tabs ───────────────────────────────────────── --}}
    <div class="tabs" style="padding:0 4px;">
        @foreach(['phones'=>'Телефони','prices'=>'Ціни','addresses'=>'Адреси','socials'=>'Соцмережі'] as $key=>$label)
        <a href="{{ route('data.index', ['type'=>$key,'q'=>$q]) }}"
           class="tabs__item {{ $type===$key ? 'is-active' : '' }}"
           style="display:inline-flex;align-items:center;gap:6px;">
            {{ $label }}
            <span style="font-size:10px;font-family:var(--font-mono);background:var(--panel-2);color:var(--text-3);padding:1px 6px;border-radius:99px;">{{ $counts[$key] }}</span>
        </a>
        @endforeach
    </div>

    {{-- ── Search bar ──────────────────────────────────────── --}}
    <div style="border-top:1px solid var(--border-2);padding:10px 16px;display:flex;align-items:center;gap:8px;background:var(--panel-2);">
        <form method="GET" action="{{ route('data.index') }}" style="display:flex;align-items:center;gap:8px;flex:1;">
            <input type="hidden" name="type" value="{{ $type }}">
            <div style="position:relative;flex:1;max-width:480px;">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--text-3);">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input type="text" name="q" value="{{ $q }}" class="dt-input"
                       style="padding-left:30px;width:100%;"
                       placeholder="Пошук по значенню, мітці, сайту…" autocomplete="off">
            </div>
            <button type="submit" class="btn btn--primary btn--sm">Шукати</button>
            @if($q)
            <a href="{{ route('data.index', ['type'=>$type]) }}" class="btn btn--ghost btn--sm">✕ Скинути</a>
            @endif
        </form>
        <span style="font-size:11px;color:var(--text-3);white-space:nowrap;margin-left:8px;">
            {{ $rows->total() }} записів
            @if($rows->hasPages()) · стор. {{ $rows->currentPage() }}/{{ $rows->lastPage() }} @endif
        </span>
    </div>

    {{-- ── Table ───────────────────────────────────────────── --}}
    @if($rows->isEmpty())
    <div style="padding:48px 24px;text-align:center;color:var(--text-3);font-size:13px;">
        @if($q) Нічого не знайдено за запитом «{{ $q }}» @else Записів ще немає @endif
    </div>
    @else
    <div style="overflow-x:auto;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="border-bottom:1px solid var(--border-2);background:var(--panel-2);">
                <th style="padding:8px 10px 8px 14px;width:32px;">
                    <input type="checkbox" id="gdb-cb-all" onchange="gdbSelectAll(this)"
                           style="width:14px;height:14px;cursor:pointer;accent-color:var(--accent);">
                </th>
                <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);white-space:nowrap;">Сайт</th>
                @if($type==='phones')
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Номер</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Країна</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Мітка</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Гео</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Статус</th>
                @elseif($type==='prices')
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Мітка</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Сума</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Валюта</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Гео</th>
                @elseif($type==='addresses')
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Місто</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Країна</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Вулиця</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Гео</th>
                @else
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Платформа</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Handle</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">URL</th>
                    <th style="padding:8px 12px;text-align:left;font-size:11px;font-weight:600;color:var(--text-3);">Гео</th>
                @endif
                <th style="padding:8px 12px;width:40px;"></th>
            </tr>
        </thead>
        <tbody>
        @foreach($rows->items() as $row)
        @php $grpColor = $row->site?->siteGroup?->color ?? '#708499'; @endphp
        <tr data-id="{{ $row->id }}"
            style="border-bottom:1px solid var(--border-2);transition:background .1s;"
            onmouseover="this.style.background='var(--panel-2)'" onmouseout="this.style.background=gdbIsSelected({{ $row->id }})?'rgba(99,179,237,.07)':''">
            <td style="padding:8px 10px 8px 14px;">
                <input type="checkbox" class="gdb-row-cb" value="{{ $row->id }}"
                       onchange="gdbUpdateSelection()"
                       style="width:14px;height:14px;cursor:pointer;accent-color:var(--accent);">
            </td>
            <td style="padding:8px 12px;">
                <a href="{{ route('sites.show', $row->site_id) }}" target="_blank"
                   style="display:flex;align-items:center;gap:7px;text-decoration:none;color:inherit;white-space:nowrap;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $grpColor }};flex-shrink:0;display:inline-block;"></span>
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);">{{ $row->site?->name ?? '—' }}</span>
                </a>
            </td>
            @if($type==='phones')
                <td style="padding:8px 12px;font-family:var(--font-mono);font-size:13px;font-weight:600;color:var(--text);">
                    {{ ($row->dial_code ? '+'.$row->dial_code.' ' : '') . $row->number }}
                </td>
                <td style="padding:8px 12px;">
                    @if($row->country_iso)<span style="font-size:11px;font-family:var(--font-mono);background:var(--panel-2);padding:2px 6px;border-radius:4px;color:var(--text-2);">{{ $row->country_iso }}</span>@else<span style="color:var(--text-3);">—</span>@endif
                </td>
                <td style="padding:8px 12px;font-size:12px;color:var(--text-3);">{{ $row->label ?: '—' }}</td>
                <td style="padding:8px 12px;">
                    @if(($row->geo_mode??'all')!=='all')
                        <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--panel-2);color:var(--text-3);">{{ ['include'=>'Тільки','exclude'=>'Крім'][$row->geo_mode] ?? $row->geo_mode }}</span>
                    @else<span style="color:var(--text-3);font-size:11px;">Всі</span>@endif
                </td>
                <td style="padding:8px 12px;">
                    @if($row->is_blocked)<span style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(245,101,101,.12);color:var(--danger);">BLOCKED</span>
                    @elseif($row->is_standby)<span style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(99,179,237,.12);color:#63b3ed;">Резерв</span>
                    @elseif(!($row->is_visible??true))<span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--panel-2);color:var(--text-3);">Схов.</span>
                    @else<span style="font-size:10px;color:var(--text-3);">—</span>@endif
                </td>
            @elseif($type==='prices')
                <td style="padding:8px 12px;font-size:12px;color:var(--text-2);">{{ $row->label ?: '—' }}</td>
                <td style="padding:8px 12px;font-family:var(--font-mono);font-weight:700;color:#34d399;">{{ number_format($row->amount,2) }}</td>
                <td style="padding:8px 12px;"><span style="font-size:11px;font-family:var(--font-mono);background:var(--panel-2);padding:2px 6px;border-radius:4px;color:var(--text-2);">{{ $row->currency }}</span></td>
                <td style="padding:8px 12px;">
                    @if(($row->geo_mode??'all')!=='all')
                        <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--panel-2);color:var(--text-3);">{{ ['include'=>'Тільки','exclude'=>'Крім'][$row->geo_mode] ?? $row->geo_mode }}</span>
                    @else<span style="color:var(--text-3);font-size:11px;">Всі</span>@endif
                </td>
            @elseif($type==='addresses')
                <td style="padding:8px 12px;font-size:12px;font-weight:600;color:var(--text);">{{ $row->city ?: '—' }}</td>
                <td style="padding:8px 12px;">
                    @if($row->country_iso)<span style="font-size:11px;font-family:var(--font-mono);background:var(--panel-2);padding:2px 6px;border-radius:4px;color:var(--text-2);">{{ $row->country_iso }}</span>@else<span style="color:var(--text-3);">—</span>@endif
                </td>
                <td style="padding:8px 12px;font-size:12px;color:var(--text-3);">{{ $row->street ?: '—' }}</td>
                <td style="padding:8px 12px;">
                    @if(($row->geo_mode??'all')!=='all')
                        <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--panel-2);color:var(--text-3);">{{ ['include'=>'Тільки','exclude'=>'Крім'][$row->geo_mode] ?? $row->geo_mode }}</span>
                    @else<span style="color:var(--text-3);font-size:11px;">Всі</span>@endif
                </td>
            @else
                <td style="padding:8px 12px;">
                    <span style="font-size:11px;padding:2px 8px;border-radius:4px;background:var(--panel-2);color:var(--text-2);font-weight:600;">{{ ucfirst($row->platform) }}</span>
                </td>
                <td style="padding:8px 12px;font-size:12px;color:var(--text-2);">{{ $row->handle ?: '—' }}</td>
                <td style="padding:8px 12px;font-size:11px;color:var(--text-3);max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $row->url ?: '—' }}</td>
                <td style="padding:8px 12px;">
                    @if(($row->geo_mode??'all')!=='all')
                        <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:var(--panel-2);color:var(--text-3);">{{ ['include'=>'Тільки','exclude'=>'Крім'][$row->geo_mode] ?? $row->geo_mode }}</span>
                    @else<span style="color:var(--text-3);font-size:11px;">Всі</span>@endif
                </td>
            @endif
            <td style="padding:8px 10px;text-align:right;">
                <a href="{{ route('sites.show', [$row->site_id, 'tab'=>'data']) }}" target="_blank"
                   class="icon-btn" title="Відкрити сайт">
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M14 4h6v6"/><path d="M20 4 10 14"/><path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/></svg>
                </a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>

    {{-- ── Pagination ───────────────────────────────────────── --}}
    @if($rows->hasPages())
    <div style="padding:10px 16px;border-top:1px solid var(--border-2);display:flex;align-items:center;justify-content:space-between;gap:8px;background:var(--panel-2);">
        <span style="font-size:11px;color:var(--text-3);">{{ $rows->firstItem() }}–{{ $rows->lastItem() }} з {{ $rows->total() }}</span>
        <div style="display:flex;gap:4px;flex-wrap:wrap;">
            @if($rows->onFirstPage())
                <span class="btn btn--ghost btn--sm" style="opacity:.4;pointer-events:none;">←</span>
            @else
                <a href="{{ $rows->previousPageUrl() }}" class="btn btn--ghost btn--sm">←</a>
            @endif
            @foreach($rows->getUrlRange(max(1,$rows->currentPage()-2), min($rows->lastPage(),$rows->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}" class="btn btn--sm {{ $page===$rows->currentPage() ? 'btn--primary' : 'btn--ghost' }}"
                   style="min-width:30px;justify-content:center;">{{ $page }}</a>
            @endforeach
            @if($rows->hasMorePages())
                <a href="{{ $rows->nextPageUrl() }}" class="btn btn--ghost btn--sm">→</a>
            @else
                <span class="btn btn--ghost btn--sm" style="opacity:.4;pointer-events:none;">→</span>
            @endif
        </div>
    </div>
    @endif
    @endif

</div>{{-- /card --}}

{{-- ── Floating action bar ─────────────────────────────────── --}}
<div id="gdb-action-bar" style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:200;background:var(--bg-card);border:1px solid var(--border);border-radius:var(--radius-card);padding:10px 16px;box-shadow:0 8px 32px rgba(0,0,0,.18);display:none;align-items:center;gap:10px;">
    <span id="gdb-sel-count" style="font-size:12px;font-weight:600;color:var(--text-2);white-space:nowrap;padding-right:8px;border-right:1px solid var(--border-2);">0 обрано</span>
    <button class="btn btn--primary btn--sm" onclick="gdbOpenEdit()">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4z"/></svg>
        Редагувати
    </button>
    <button class="btn btn--ghost btn--sm" onclick="gdbOpenCopy()">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
        Копіювати
    </button>
    <button class="btn btn--sm" style="background:rgba(245,101,101,.1);color:var(--danger);border:1px solid rgba(245,101,101,.25);" onclick="gdbDeleteSelected()">
        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
        Видалити
    </button>
    <button class="btn btn--ghost btn--sm" onclick="gdbClearSelection()">✕</button>
</div>

{{-- ── Hidden forms ─────────────────────────────────────────── --}}
<form method="POST" action="{{ route('data.bulk-delete') }}" id="form-gdb-delete">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="q" value="{{ $q }}">
    <div id="gdb-delete-ids"></div>
</form>

{{-- ── Edit drawer — multi-op builder ─────────────────────────── --}}
<div class="drawer-overlay" id="drawer-gdb-edit-overlay" onclick="closeDrawer('drawer-gdb-edit')"></div>
<div class="drawer" id="drawer-gdb-edit" style="width:500px;">
    <div class="drawer__header">
        <span class="drawer__title">Конструктор змін</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-gdb-edit')">✕</button>
    </div>
    <div class="drawer__body">

        {{-- Info bar --}}
        <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:var(--panel-2);border-radius:var(--radius-item);border:1px solid var(--border-2);margin-bottom:16px;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="color:var(--accent);flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span id="gdb-edit-info" style="font-size:12px;font-weight:600;color:var(--text-2);">0 записів обрано</span>
            <span style="font-size:11px;color:var(--text-3);margin-left:auto;">Тип: <strong>{{ $type }}</strong></span>
        </div>

        {{-- Operations list --}}
        <div id="gdb-edit-ops-list" style="display:flex;flex-direction:column;gap:8px;min-height:48px;"></div>

        {{-- Add operation button --}}
        <button type="button" onclick="addEditOp()" id="gdb-add-op-btn"
                style="margin-top:10px;width:100%;padding:9px;border:1.5px dashed var(--border);border-radius:var(--radius-item);background:none;color:var(--text-3);font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:border-color .15s,color .15s;"
                onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text-3)'">
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Додати поле
        </button>

        {{-- Result log --}}
        <div id="gdb-edit-result-log" style="display:none;margin-top:16px;border-top:1px solid var(--border-2);padding-top:12px;">
            <div style="font-size:10px;font-weight:700;color:var(--text-3);margin-bottom:8px;text-transform:uppercase;letter-spacing:.6px;">Результат виконання</div>
            <div id="gdb-edit-result-rows" style="display:flex;flex-direction:column;gap:5px;"></div>
        </div>

    </div>
    <div class="drawer__footer">
        <button class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-gdb-edit')">Скасувати</button>
        <button class="btn btn--primary btn--md" id="gdb-edit-submit-btn" onclick="bulkEditSubmit()">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M20 6 9 17l-5-5"/></svg>
            <span id="gdb-edit-submit-label">Застосувати</span>
        </button>
    </div>
</div>

{{-- ── Copy drawer ──────────────────────────────────────────── --}}
<div class="drawer-overlay" id="drawer-gdb-copy-overlay" onclick="closeDrawer('drawer-gdb-copy')"></div>
<div class="drawer" id="drawer-gdb-copy">
    <div class="drawer__header">
        <span class="drawer__title" id="gdb-copy-title">Копіювати до сайтів</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-gdb-copy')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('data.bulk-copy') }}" id="form-gdb-copy" class="form-stack">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="q" value="{{ $q }}">
            <div id="gdb-copy-ids"></div>
            <label class="dt-label">Оберіть сайти-цілі</label>
            <div style="display:flex;flex-direction:column;gap:4px;max-height:400px;overflow-y:auto;border:1px solid var(--border-2);border-radius:var(--radius-item);padding:6px;">
                <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border-bottom:1px solid var(--border-2);cursor:pointer;">
                    <input type="checkbox" id="gdb-copy-all" onchange="gdbCopySelectAll(this)"
                           style="width:14px;height:14px;accent-color:var(--accent);">
                    <span style="font-size:12px;font-weight:600;color:var(--text-3);">Вибрати всі</span>
                </label>
                @foreach($sites as $s)
                @php $sc = $s->siteGroup?->color ?? '#708499'; @endphp
                <label style="display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:6px;cursor:pointer;transition:background .1s;"
                       onmouseover="this.style.background='var(--panel-2)'" onmouseout="this.style.background=''">
                    <input type="checkbox" name="target_ids[]" value="{{ $s->id }}"
                           style="width:14px;height:14px;accent-color:var(--accent);">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $sc }};flex-shrink:0;"></span>
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);flex:1;">{{ $s->name }}</span>
                    <span style="font-size:11px;color:var(--text-3);font-family:var(--font-mono);">{{ parse_url($s->url ?? '', PHP_URL_HOST) }}</span>
                </label>
                @endforeach
            </div>
        </form>
    </div>
    <div class="drawer__footer">
        <button class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-gdb-copy')">Скасувати</button>
        <button class="btn btn--primary btn--md" onclick="document.getElementById('form-gdb-copy').submit()">Скопіювати</button>
    </div>
</div>

{{-- ── Bulk Add drawer ──────────────────────────────────────── --}}
<div class="drawer-overlay" id="drawer-bulk-add-overlay" onclick="closeDrawer('drawer-bulk-add')"></div>
<div class="drawer" id="drawer-bulk-add" style="width:520px;">
    <div class="drawer__header">
        <span class="drawer__title">Додати на кілька сайтів</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-bulk-add')">✕</button>
    </div>
    <div class="drawer__body" id="bulk-add-body">

        {{-- Type selector --}}
        <div style="margin-bottom:16px;">
            <label class="dt-label">Тип запису</label>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                @foreach(['phones'=>'Телефон','prices'=>'Ціна','addresses'=>'Адреса','socials'=>'Соцмережа'] as $k=>$lbl)
                <label style="display:flex;align-items:center;gap:6px;padding:6px 12px;border:1.5px solid var(--border);border-radius:var(--radius-item);cursor:pointer;font-size:12px;font-weight:600;transition:all .15s;"
                       id="bulk-type-lbl-{{ $k }}"
                       onmouseover="this.style.borderColor='var(--accent)'"
                       onmouseout="bulkTypeHover(this,'{{ $k }}')">
                    <input type="radio" name="bulk_type" value="{{ $k }}" style="display:none;"
                           onchange="bulkTypeChange('{{ $k }}')"
                           {{ $k==='phones' ? 'checked' : '' }}>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="border-top:1px solid var(--border-2);padding-top:16px;">

        {{-- Phone fields --}}
        <div id="bulk-fields-phones" class="bulk-type-fields form-stack">
            <div style="display:grid;grid-template-columns:90px 1fr;gap:10px;">
                <div><label class="dt-label">Код</label><input type="text" class="dt-input" name="dial_code" placeholder="+380" maxlength="8"></div>
                <div><label class="dt-label">Номер *</label><input type="text" class="dt-input" name="number" placeholder="50 123 4567" required></div>
            </div>
            <div><label class="dt-label">Мітка</label><input type="text" class="dt-input" name="label" placeholder="Головний, Продажі…"></div>
            <div><label class="dt-label">Країна ISO</label>
                <select class="dt-input" name="country_iso">
                    <option value="">— не вказано —</option>
                    @foreach($countries as $c)<option value="{{ $c->iso }}">{{ $c->iso }} — {{ $c->name }}</option>@endforeach
                </select>
            </div>
        </div>

        {{-- Price fields --}}
        <div id="bulk-fields-prices" class="bulk-type-fields form-stack" style="display:none;">
            <div><label class="dt-label">Мітка *</label><input type="text" class="dt-input" name="label" placeholder="Базовий план…" required></div>
            <div style="display:grid;grid-template-columns:1fr 90px;gap:10px;">
                <div><label class="dt-label">Сума *</label><input type="number" class="dt-input" name="amount" step="0.01" min="0" placeholder="0.00" required></div>
                <div><label class="dt-label">Валюта</label>
                    <select class="dt-input" name="currency">
                        <option value="UAH">UAH</option><option value="USD">USD</option><option value="EUR">EUR</option>
                    </select>
                </div>
            </div>
            <div><label class="dt-label">Период</label><input type="text" class="dt-input" name="period" placeholder="місяць, рік…"></div>
        </div>

        {{-- Address fields --}}
        <div id="bulk-fields-addresses" class="bulk-type-fields form-stack" style="display:none;">
            <div style="display:grid;grid-template-columns:1fr 80px;gap:10px;">
                <div><label class="dt-label">Місто *</label><input type="text" class="dt-input" name="city" placeholder="Київ" required></div>
                <div><label class="dt-label">ISO</label>
                    <select class="dt-input" name="country_iso">
                        <option value="">—</option>
                        @foreach($countries as $c)<option value="{{ $c->iso }}">{{ $c->iso }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div><label class="dt-label">Вулиця</label><input type="text" class="dt-input" name="street" placeholder="вул. Хрещатик, 1"></div>
            <div><label class="dt-label">Мітка</label><input type="text" class="dt-input" name="label" placeholder="Головний офіс…"></div>
        </div>

        {{-- Social fields --}}
        <div id="bulk-fields-socials" class="bulk-type-fields form-stack" style="display:none;">
            <div><label class="dt-label">Платформа *</label>
                <select class="dt-input" name="platform" required>
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                    <option value="telegram">Telegram</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="viber">Viber</option>
                    <option value="youtube">YouTube</option>
                    <option value="tiktok">TikTok</option>
                    <option value="twitter">Twitter / X</option>
                    <option value="linkedin">LinkedIn</option>
                </select>
            </div>
            <div><label class="dt-label">Handle / нікнейм</label><input type="text" class="dt-input" name="handle" placeholder="@username"></div>
            <div><label class="dt-label">URL *</label><input type="url" class="dt-input" name="url" placeholder="https://t.me/username" required></div>
        </div>

        {{-- Geo rule (shared) --}}
        <div style="border-top:1px solid var(--border-2);padding-top:14px;margin-top:4px;">
            <label class="dt-label">Гео-правило</label>
            <div class="dt-geo-row">
                <span class="dt-geo-label">Видно:</span>
                @foreach(['all'=>'Всім','include'=>'Тільки','exclude'=>'Крім'] as $mv=>$ml)
                <label class="dt-geo-pill {{ $mv==='all'?'is-on':'' }}" id="bulk-gpill-{{ $mv }}">
                    <input type="radio" name="geo_mode" value="{{ $mv }}" {{ $mv==='all'?'checked':'' }} style="display:none;"
                           onchange="bulkGeoMode('{{ $mv }}')">{{ $ml }}
                </label>
                @endforeach
                <span id="bulk-geo-chips" class="dt-geo-chips" style="display:none;">
                    @foreach($countries as $c)
                    <label class="dt-geo-chip" id="bulk-chip-{{ $c->iso }}">
                        <input type="checkbox" name="geo_countries[]" value="{{ $c->iso }}" style="display:none;"
                               onchange="this.closest('label').classList.toggle('is-on',this.checked)">{{ $c->iso }}
                    </label>
                    @endforeach
                </span>
            </div>
        </div>

        </div>{{-- /border-top --}}

        {{-- Site picker --}}
        <div style="border-top:1px solid var(--border-2);padding-top:16px;margin-top:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <label class="dt-label" style="margin:0;">Сайти-цілі *</label>
                <button type="button" class="btn btn--ghost btn--sm" onclick="bulkToggleAllSites()" id="bulk-site-toggle-btn">Вибрати всі</button>
            </div>
            <div style="border:1px solid var(--border-2);border-radius:var(--radius-item);max-height:280px;overflow-y:auto;">
                @foreach($sites as $s)
                @php $sc = $s->siteGroup?->color ?? '#708499'; @endphp
                <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-bottom:1px solid var(--border-2);cursor:pointer;transition:background .1s;"
                       onmouseover="this.style.background='var(--panel-2)'" onmouseout="this.style.background=''">
                    <input type="checkbox" class="bulk-site-cb" value="{{ $s->id }}"
                           style="width:14px;height:14px;accent-color:var(--accent);">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $sc }};flex-shrink:0;"></span>
                    <span style="font-size:12px;font-weight:600;color:var(--text-2);flex:1;">{{ $s->name }}</span>
                    <span style="font-size:11px;color:var(--text-3);font-family:var(--font-mono);">{{ parse_url($s->url ?? '', PHP_URL_HOST) }}</span>
                </label>
                @endforeach
            </div>
        </div>

        {{-- Result toast --}}
        <div id="bulk-add-result" style="display:none;margin-top:12px;padding:10px 14px;border-radius:var(--radius-item);font-size:12px;"></div>

    </div>{{-- /drawer__body --}}
    <div class="drawer__footer">
        <button class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-bulk-add')">Закрити</button>
        <button class="btn btn--primary btn--md" id="bulk-add-btn" onclick="bulkAddSubmit()">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Додати
        </button>
    </div>
</div>

<script>
// ─────────────────────────── Selection ───────────────────────────────────────
var _gdbSelected = new Set();

function gdbIsSelected(id) { return _gdbSelected.has(String(id)); }

function gdbUpdateSelection() {
    _gdbSelected.clear();
    document.querySelectorAll('.gdb-row-cb:checked').forEach(function(cb) { _gdbSelected.add(cb.value); });
    var count = _gdbSelected.size;
    var bar = document.getElementById('gdb-action-bar');
    bar.style.display = count > 0 ? 'flex' : 'none';
    document.getElementById('gdb-sel-count').textContent = count + ' обрано';
    var all = document.querySelectorAll('.gdb-row-cb').length;
    var cbAll = document.getElementById('gdb-cb-all');
    if (cbAll) { cbAll.indeterminate = count > 0 && count < all; cbAll.checked = count === all && all > 0; }
    document.querySelectorAll('tr[data-id]').forEach(function(tr) {
        var cb = tr.querySelector('.gdb-row-cb');
        tr.style.background = (cb && cb.checked) ? 'rgba(99,179,237,.07)' : '';
    });
}

function gdbSelectAll(cbAll) {
    document.querySelectorAll('.gdb-row-cb').forEach(function(cb) { cb.checked = cbAll.checked; });
    gdbUpdateSelection();
}

function gdbClearSelection() {
    document.querySelectorAll('.gdb-row-cb, #gdb-cb-all').forEach(function(cb) { cb.checked = false; });
    gdbUpdateSelection();
    closeDrawer('drawer-gdb-edit');
    closeDrawer('drawer-gdb-copy');
}

function gdbGetSelectedIds() { return Array.from(_gdbSelected); }

function gdbFillIds(containerId, ids, name) {
    var c = document.getElementById(containerId);
    c.innerHTML = '';
    ids.forEach(function(id) {
        var inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = name || 'ids[]'; inp.value = id;
        c.appendChild(inp);
    });
}

// ─────────────────────── Edit Builder (multi-op) ─────────────────────────────
var _editOps  = [];
var _editType = '{{ $type }}';
var _editFields = {
    phones:    [{v:'number',l:'Номер'},{v:'label',l:'Мітка'},{v:'country_iso',l:'Країна ISO'},{v:'dial_code',l:'Код (+)'}],
    prices:    [{v:'amount',l:'Сума'},{v:'currency',l:'Валюта'},{v:'label',l:'Мітка'},{v:'period',l:'Период'}],
    addresses: [{v:'city',l:'Місто'},{v:'country_iso',l:'Країна ISO'},{v:'street',l:'Вулиця'},{v:'label',l:'Мітка'}],
    socials:   [{v:'handle',l:'Handle'},{v:'url',l:'URL'},{v:'platform',l:'Платформа'}]
};
var _editCountries = @json($countries->map(fn($c) => ['iso' => $c->iso, 'name' => $c->name]));

function gdbOpenEdit() {
    var ids = gdbGetSelectedIds();
    if (!ids.length) return;
    document.getElementById('gdb-edit-info').textContent = ids.length + ' записів будуть змінені';
    document.getElementById('gdb-edit-result-log').style.display = 'none';
    document.getElementById('gdb-edit-result-rows').innerHTML = '';
    if (_editOps.length === 0) addEditOp();
    renderEditOps();
    updateEditSubmitLabel();
    closeDrawer('drawer-gdb-copy');
    openDrawer('drawer-gdb-edit');
}

function addEditOp() {
    var fields = _editFields[_editType] || [];
    var used   = _editOps.map(function(op) { return op.field; });
    var avail  = fields.filter(function(f) { return !used.includes(f.v); });
    var field  = (avail[0] || fields[0] || {v:''}).v;
    _editOps.push({field: field, value: ''});
    renderEditOps();
    updateEditSubmitLabel();
}

function removeEditOp(idx) {
    _editOps.splice(idx, 1);
    renderEditOps();
    updateEditSubmitLabel();
}

function editOpFieldChange(sel, idx) {
    _editOps[idx].field = sel.value;
    _editOps[idx].value = '';
    var row = document.querySelector('[data-eop="'+idx+'"]');
    if (row) row.querySelector('.eop-val-wrap').innerHTML = getValWidget(_editOps[idx].field, idx);
}

function editOpValChange(el, idx) { _editOps[idx].value = el.value; }

function getValWidget(field, idx) {
    var cls  = 'dt-input eop-val-el';
    var attr = 'data-idx="'+idx+'" oninput="editOpValChange(this,'+idx+')" onchange="editOpValChange(this,'+idx+')"';
    if (field === 'currency') {
        return '<select class="'+cls+'" '+attr+' style="font-size:12px;">'
            +'<option value="UAH">UAH ₴</option><option value="USD">USD $</option><option value="EUR">EUR €</option>'
            +'</select>';
    }
    if (field === 'platform') {
        return '<select class="'+cls+'" '+attr+' style="font-size:12px;">'
            +['instagram','facebook','telegram','whatsapp','viber','youtube','tiktok','twitter','linkedin']
                .map(function(p){return '<option value="'+p+'">'+p.charAt(0).toUpperCase()+p.slice(1)+'</option>';}).join('')
            +'</select>';
    }
    if (field === 'country_iso') {
        var opts = '<option value="">— не вказано —</option>';
        _editCountries.forEach(function(c){ opts += '<option value="'+c.iso+'">'+c.iso+' — '+c.name+'</option>'; });
        return '<select class="'+cls+'" '+attr+' style="font-size:12px;">'+opts+'</select>';
    }
    if (field === 'amount') {
        return '<input type="number" class="'+cls+'" '+attr+' step="0.01" min="0" placeholder="0.00" style="font-size:12px;">';
    }
    if (field === 'dial_code') {
        return '<input type="text" class="'+cls+'" '+attr+' placeholder="380" maxlength="8" style="font-size:12px;font-family:var(--font-mono);">';
    }
    if (field === 'number') {
        return '<input type="text" class="'+cls+'" '+attr+' placeholder="50 123 4567" style="font-size:12px;font-family:var(--font-mono);">';
    }
    if (field === 'url') {
        return '<input type="url" class="'+cls+'" '+attr+' placeholder="https://..." style="font-size:12px;">';
    }
    return '<input type="text" class="'+cls+'" '+attr+' placeholder="Нове значення…" style="font-size:12px;">';
}

function renderEditOps() {
    var fields = _editFields[_editType] || [];
    var list   = document.getElementById('gdb-edit-ops-list');
    list.innerHTML = '';

    _editOps.forEach(function(op, idx) {
        var opts = fields.map(function(f){
            return '<option value="'+f.v+'"'+(op.field===f.v?' selected':'')+'>'+f.l+'</option>';
        }).join('');

        var div = document.createElement('div');
        div.setAttribute('data-eop', idx);
        div.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 12px;background:var(--panel-2);border-radius:var(--radius-item);border:1px solid var(--border-2);';
        div.innerHTML =
            '<select class="dt-input eop-field" style="flex:0 0 130px;font-size:12px;" onchange="editOpFieldChange(this,'+idx+')">'
            + opts + '</select>'
            + '<svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" style="color:var(--text-3);flex-shrink:0;"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>'
            + '<div class="eop-val-wrap" style="flex:1;">' + getValWidget(op.field, idx) + '</div>'
            + '<button type="button" onclick="removeEditOp('+idx+')" title="Видалити поле"'
            + ' style="width:26px;height:26px;border:none;background:none;cursor:pointer;color:var(--text-3);display:flex;align-items:center;justify-content:center;border-radius:4px;flex-shrink:0;transition:background .12s,color .12s;"'
            + ' onmouseover="this.style.background=\'rgba(245,101,101,.12)\';this.style.color=\'var(--danger)\'"'
            + ' onmouseout="this.style.background=\'none\';this.style.color=\'var(--text-3)\'">'
            + '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
            + '</button>';
        list.appendChild(div);
    });

    var addBtn = document.getElementById('gdb-add-op-btn');
    if (addBtn) addBtn.style.display = (_editOps.length >= fields.length) ? 'none' : 'flex';
}

function updateEditSubmitLabel() {
    var label = document.getElementById('gdb-edit-submit-label');
    if (!label) return;
    var n = _editOps.length, r = gdbGetSelectedIds().length;
    if (n === 0) { label.textContent = 'Застосувати'; return; }
    var nWord = n === 1 ? 'зміну' : (n < 5 ? 'зміни' : 'змін');
    label.textContent = 'Застосувати ' + n + ' ' + nWord + (r ? ' → ' + r + ' зап.' : '');
}

async function bulkEditSubmit() {
    var ids = gdbGetSelectedIds();
    if (!ids.length) return;

    // Sync values from DOM before submit
    _editOps.forEach(function(op, idx) {
        var el = document.querySelector('[data-eop="'+idx+'"] .eop-val-el');
        if (el) op.value = el.value;
    });

    var ops = _editOps.filter(function(op) { return op.field; });
    if (!ops.length) { alert('Додайте хоча б одну зміну.'); return; }

    var btn = document.getElementById('gdb-edit-submit-btn');
    btn.disabled = true;
    document.getElementById('gdb-edit-submit-label').textContent = 'Застосовуємо…';

    var resultLog  = document.getElementById('gdb-edit-result-log');
    var resultRows = document.getElementById('gdb-edit-result-rows');
    resultLog.style.display = '';
    resultRows.innerHTML = '';

    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var url  = '{{ route("data.bulk-edit") }}';
    var allOk = true;

    for (var i = 0; i < ops.length; i++) {
        var op  = ops[i];
        var fDef = (_editFields[_editType] || []).find(function(f){ return f.v === op.field; });
        var lbl  = fDef ? fDef.l : op.field;
        var valDisplay = op.value || '(порожньо)';

        // Pending row with spinner
        var row = document.createElement('div');
        row.style.cssText = 'display:flex;align-items:center;gap:8px;font-size:12px;padding:7px 10px;border-radius:6px;background:var(--panel-2);color:var(--text-3);';
        row.innerHTML = '<span style="width:12px;height:12px;border:2px solid var(--accent);border-top-color:transparent;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0;display:inline-block;"></span>'
            + '<span><strong>' + lbl + '</strong> → <code style="font-size:11px;">' + valDisplay + '</code></span>';
        resultRows.appendChild(row);

        try {
            var body = new FormData();
            body.append('_token', csrf);
            body.append('type', _editType);
            body.append('field', op.field);
            body.append('value', op.value);
            ids.forEach(function(id){ body.append('ids[]', id); });

            var resp = await fetch(url, {method:'POST', headers:{'Accept':'application/json'}, body: body});
            var data = await resp.json();

            if (resp.ok && data.ok !== undefined) {
                row.style.background = 'rgba(72,187,120,.08)';
                row.style.color = '#48bb78';
                row.innerHTML = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0;"><path d="M20 6 9 17l-5-5"/></svg>'
                    + '<span><strong>' + lbl + '</strong> → <code style="font-size:11px;">' + valDisplay + '</code></span>'
                    + '<span style="margin-left:auto;font-size:10px;font-family:var(--font-mono);opacity:.7;">' + data.ok + ' зап.</span>';
            } else {
                throw new Error(data.error || 'Помилка');
            }
        } catch(e) {
            allOk = false;
            row.style.background = 'rgba(245,101,101,.08)';
            row.style.color = 'var(--danger)';
            row.innerHTML = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" style="flex-shrink:0;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
                + '<span><strong>' + lbl + '</strong> — ' + (e.message || 'помилка') + '</span>';
        }
    }

    btn.disabled = false;
    updateEditSubmitLabel();

    // Auto-reload after success to reflect changes in table
    if (allOk) {
        var notice = document.createElement('div');
        notice.style.cssText = 'font-size:11px;color:var(--text-3);text-align:center;margin-top:6px;';
        notice.textContent = 'Оновлення сторінки…';
        resultRows.appendChild(notice);
        setTimeout(function(){ window.location.reload(); }, 1200);
    }
}

// ─────────────────────────── Copy drawer ─────────────────────────────────────
function gdbOpenCopy() {
    var ids = gdbGetSelectedIds();
    if (!ids.length) return;
    gdbFillIds('gdb-copy-ids', ids);
    document.getElementById('gdb-copy-title').textContent = 'Копіювати ' + ids.length + ' записів';
    closeDrawer('drawer-gdb-edit');
    openDrawer('drawer-gdb-copy');
}

function gdbCopySelectAll(cb) {
    document.querySelectorAll('#form-gdb-copy input[name="target_ids[]"]').forEach(function(c) { c.checked = cb.checked; });
}

// ─────────────────────────── Delete ──────────────────────────────────────────
function gdbDeleteSelected() {
    var ids = gdbGetSelectedIds();
    if (!ids.length) return;
    if (!confirm('Видалити ' + ids.length + ' записів? Це незворотно.')) return;
    gdbFillIds('gdb-delete-ids', ids);
    document.getElementById('form-gdb-delete').submit();
}

// ─────────────────────────── Bulk Add drawer ──────────────────────────────────
var _bulkType = 'phones';

function bulkTypeChange(type) {
    _bulkType = type;
    document.querySelectorAll('.bulk-type-fields').forEach(function(el) { el.style.display = 'none'; });
    var f = document.getElementById('bulk-fields-' + type);
    if (f) f.style.display = '';
    document.querySelectorAll('[id^="bulk-type-lbl-"]').forEach(function(lbl) {
        lbl.style.borderColor = 'var(--border)';
        lbl.style.background = '';
        lbl.style.color = '';
    });
    var active = document.getElementById('bulk-type-lbl-' + type);
    if (active) { active.style.borderColor = 'var(--accent)'; active.style.background = 'rgba(99,179,237,.08)'; active.style.color = 'var(--accent)'; }
    document.getElementById('bulk-add-result').style.display = 'none';
}

function bulkTypeHover(lbl, type) {
    lbl.style.borderColor = _bulkType === type ? 'var(--accent)' : 'var(--border)';
}

function bulkGeoMode(mode) {
    document.querySelectorAll('[id^="bulk-gpill-"]').forEach(function(lbl) { lbl.classList.remove('is-on'); });
    document.getElementById('bulk-gpill-' + mode)?.classList.add('is-on');
    document.getElementById('bulk-geo-chips').style.display = mode !== 'all' ? 'flex' : 'none';
}

function bulkToggleAllSites() {
    var cbs = document.querySelectorAll('.bulk-site-cb');
    var anyUnchecked = Array.from(cbs).some(function(cb) { return !cb.checked; });
    cbs.forEach(function(cb) { cb.checked = anyUnchecked; });
    document.getElementById('bulk-site-toggle-btn').textContent = anyUnchecked ? 'Скасувати всі' : 'Вибрати всі';
}

function bulkAddSubmit() {
    var siteIds = Array.from(document.querySelectorAll('.bulk-site-cb:checked')).map(function(cb) { return cb.value; });
    if (!siteIds.length) { alert('Оберіть хоча б один сайт.'); return; }

    var urls = {
        phones:    '{{ route('bulk.phones') }}',
        prices:    '{{ route('bulk.prices') }}',
        addresses: '{{ route('bulk.addresses') }}',
        socials:   '{{ route('bulk.socials') }}'
    };
    var url = urls[_bulkType];
    if (!url) { alert('Тип не підтримується.'); return; }

    var fields = document.querySelectorAll('#bulk-fields-' + _bulkType + ' input, #bulk-fields-' + _bulkType + ' select, #bulk-fields-' + _bulkType + ' textarea');
    var body = new FormData();
    body.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    siteIds.forEach(function(id) { body.append('site_ids[]', id); });

    // Geo
    var geoMode = document.querySelector('input[name="geo_mode"]:checked');
    if (geoMode) body.append('geo_mode', geoMode.value);
    document.querySelectorAll('input[name="geo_countries[]"]:checked').forEach(function(cb) { body.append('geo_countries[]', cb.value); });

    // Type-specific fields
    fields.forEach(function(el) {
        if (el.name && el.name !== 'geo_mode' && el.value.trim()) body.append(el.name, el.value.trim());
    });

    var btn = document.getElementById('bulk-add-btn');
    btn.disabled = true; btn.textContent = 'Надсилаємо…';

    fetch(url, { method: 'POST', body: body })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var result = document.getElementById('bulk-add-result');
            result.style.display = '';
            if (data.ok > 0 && data.failed === 0) {
                result.style.background = 'rgba(72,187,120,.12)'; result.style.color = '#48bb78'; result.style.border = '1px solid rgba(72,187,120,.3)';
                result.textContent = '✓ Додано на ' + data.ok + ' сайт' + (data.ok === 1 ? '' : 'и') + '.';
            } else if (data.failed > 0) {
                result.style.background = 'rgba(245,101,101,.1)'; result.style.color = 'var(--danger)'; result.style.border = '1px solid rgba(245,101,101,.25)';
                result.textContent = '✓ ' + data.ok + ' успішно, ✗ ' + data.failed + ' помилок.';
            } else {
                result.style.background = 'rgba(245,101,101,.1)'; result.style.color = 'var(--danger)'; result.style.border = '1px solid rgba(245,101,101,.25)';
                result.textContent = 'Нічого не вдалось. Перевір обов\'язкові поля.';
            }
        })
        .catch(function() {
            var result = document.getElementById('bulk-add-result');
            result.style.display = ''; result.style.background = 'rgba(245,101,101,.1)'; result.style.color = 'var(--danger)'; result.style.border = '1px solid rgba(245,101,101,.25)';
            result.textContent = 'Помилка мережі. Спробуй ще раз.';
        })
        .finally(function() { btn.disabled = false; btn.textContent = 'Додати'; });
}

// Init
bulkTypeChange('{{ $type }}');

// Spinner keyframes (for edit op pending rows)
(function(){
    if (!document.getElementById('spin-kf')) {
        var s = document.createElement('style');
        s.id = 'spin-kf';
        s.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
        document.head.appendChild(s);
    }
})();
</script>

@endsection
