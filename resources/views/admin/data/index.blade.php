@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/data-browser.css') }}?v={{ filemtime(public_path('assets/css/pages/data-browser.css')) }}">
@endpush

@section('title', 'Data Browser')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Data Browser</h1>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
@endif

<div class="db-layout">

    {{-- Type tabs --}}
    <div class="db-type-tabs">
        @php
            $counts = [
                'phones'    => \App\Models\SitePhone::count(),
                'prices'    => \App\Models\SitePrice::count(),
                'addresses' => \App\Models\SiteAddress::count(),
                'socials'   => \App\Models\SiteSocial::count(),
            ];
        @endphp
        @foreach(['phones' => 'Телефони', 'prices' => 'Ціни', 'addresses' => 'Адреси', 'socials' => 'Соцмережі'] as $key => $label)
        <a href="{{ route('data.index', ['type' => $key, 'q' => $q]) }}"
           class="db-type-tab {{ $type === $key ? 'is-active' : '' }}">
            {{ $label }}
            <span class="db-type-tab__count">{{ $counts[$key] }}</span>
        </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('data.index') }}" class="db-search-bar">
        <input type="hidden" name="type" value="{{ $type }}">
        <div class="db-search-wrap">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" name="q" value="{{ $q }}" class="db-search-input"
                   placeholder="Пошук по значенню, мітці, сайту…"
                   id="db-search" autocomplete="off">
        </div>
        <button type="submit" class="btn-primary">Шукати</button>
        @if($q)
        <a href="{{ route('data.index', ['type' => $type]) }}" class="btn-ghost">✕ Скинути</a>
        @endif
        <span class="db-result-count">{{ $rows->count() }} записів</span>
    </form>

    {{-- Table --}}
    <div class="db-table-wrap">
        @if($rows->isEmpty())
            <div class="db-empty">
                @if($q)
                    Нічого не знайдено за запитом «{{ $q }}»
                @else
                    Записів ще немає
                @endif
            </div>
        @else
        <table class="db-table">
            <thead>
                <tr>
                    <th><input type="checkbox" class="db-cb" id="db-cb-all" onchange="dbSelectAll(this)"></th>
                    <th>Сайт</th>
                    @if($type === 'phones')
                        <th>Номер</th>
                        <th>Країна</th>
                        <th class="db-col-optional">Мітка</th>
                        <th class="db-col-optional">Primary</th>
                    @elseif($type === 'prices')
                        <th>Ціна</th>
                        <th>Валюта</th>
                        <th class="db-col-optional">Мітка</th>
                        <th class="db-col-optional">Видимість</th>
                    @elseif($type === 'addresses')
                        <th>Місто</th>
                        <th>Країна</th>
                        <th class="db-col-optional">Вулиця</th>
                        <th class="db-col-optional">Мітка</th>
                    @elseif($type === 'socials')
                        <th>Платформа</th>
                        <th>URL / Handle</th>
                        <th class="db-col-optional">Мітка</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                @php $color = $row->site?->siteGroup?->color ?? '#708499'; @endphp
                <tr data-id="{{ $row->id }}" class="{{ '' }}">
                    <td><input type="checkbox" class="db-cb db-row-cb" value="{{ $row->id }}" onchange="dbUpdateSelection()"></td>
                    <td>
                        <div class="db-site-cell">
                            <span class="db-site-dot" style="background:{{ $color }}"></span>
                            <span class="db-site-name" title="{{ $row->site?->name }}">{{ $row->site?->name ?? '—' }}</span>
                        </div>
                    </td>
                    @if($type === 'phones')
                        <td><span class="db-val-main">{{ $row->number }}</span></td>
                        <td><span class="db-badge">{{ $row->country_iso }} +{{ $row->dial_code }}</span></td>
                        <td class="db-col-optional"><span class="db-val-sub">{{ $row->label ?: '—' }}</span></td>
                        <td class="db-col-optional">{{ $row->is_primary ? '✓' : '' }}</td>
                    @elseif($type === 'prices')
                        <td><span class="db-val-main">{{ number_format($row->amount, 2) }}</span></td>
                        <td><span class="db-badge db-badge--currency">{{ $row->currency }}</span></td>
                        <td class="db-col-optional"><span class="db-val-sub">{{ $row->label ?: '—' }}</span></td>
                        <td class="db-col-optional">{{ $row->is_visible ? 'Видима' : 'Прихована' }}</td>
                    @elseif($type === 'addresses')
                        <td><span class="db-val-main">{{ $row->city }}</span></td>
                        <td><span class="db-badge">{{ $row->country_iso }}</span></td>
                        <td class="db-col-optional"><span class="db-val-sub">{{ $row->street ?: '—' }}</span></td>
                        <td class="db-col-optional"><span class="db-val-sub">{{ $row->label ?: '—' }}</span></td>
                    @elseif($type === 'socials')
                        <td><span class="db-badge db-badge--platform">{{ $row->platform }}</span></td>
                        <td><span class="db-val-sub" style="max-width:200px;overflow:hidden;text-overflow:ellipsis;display:block;white-space:nowrap;">{{ $row->url }}</span></td>
                        <td class="db-col-optional"><span class="db-val-sub">{{ $row->handle ?: '—' }}</span></td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>

{{-- ── Inline edit panel ── --}}
<div class="db-inline-panel" id="db-edit-panel">
    <div class="db-inline-panel__header">
        <span id="db-edit-panel-title">Редагувати</span>
        <button class="btn-icon" onclick="dbCloseEdit()">✕</button>
    </div>
    <form method="POST" action="{{ route('data.bulk-edit') }}" id="form-db-edit">
        @csrf
        <input type="hidden" name="type"  value="{{ $type }}">
        <input type="hidden" name="q"     value="{{ $q }}">
        <div id="db-edit-ids"></div>
        <div class="db-inline-panel__body">
            <div class="form-group">
                <label class="form-label">Поле</label>
                <select name="field" class="form-input form-select" id="db-edit-field" onchange="dbEditFieldChange(this)">
                    @if($type === 'phones')
                        <option value="number">Номер</option>
                        <option value="label">Мітка</option>
                        <option value="country_iso">Країна ISO</option>
                        <option value="dial_code">Код (+)</option>
                    @elseif($type === 'prices')
                        <option value="amount">Сума</option>
                        <option value="currency">Валюта</option>
                        <option value="label">Мітка</option>
                    @elseif($type === 'addresses')
                        <option value="city">Місто</option>
                        <option value="country_iso">Країна ISO</option>
                        <option value="street">Вулиця</option>
                        <option value="label">Мітка</option>
                    @elseif($type === 'socials')
                        <option value="url">URL</option>
                        <option value="platform">Платформа</option>
                        <option value="handle">Handle</option>
                    @endif
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Нове значення</label>
                {{-- currency select --}}
                <select name="value" class="form-input form-select" id="db-edit-val-currency" style="display:none">
                    <option value="UAH">UAH ₴</option>
                    <option value="USD">USD $</option>
                    <option value="EUR">EUR €</option>
                </select>
                {{-- platform select --}}
                <select name="value" class="form-input form-select" id="db-edit-val-platform" style="display:none">
                    <option value="instagram">Instagram</option>
                    <option value="facebook">Facebook</option>
                    <option value="telegram">Telegram</option>
                    <option value="youtube">YouTube</option>
                    <option value="tiktok">TikTok</option>
                    <option value="twitter">Twitter / X</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="viber">Viber</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="other">Інше</option>
                </select>
                {{-- text input (default) --}}
                <input type="text" name="value" class="form-input" id="db-edit-val-text" placeholder="Нове значення…">
            </div>
        </div>
        <div class="db-inline-panel__footer">
            <button type="button" class="btn-ghost" onclick="dbCloseEdit()">Скасувати</button>
            <button type="submit" class="btn-primary" id="db-edit-submit">Застосувати</button>
        </div>
    </form>
</div>

{{-- ── Copy panel ── --}}
<div class="db-inline-panel" id="db-copy-panel">
    <div class="db-inline-panel__header">
        <span>Копіювати до сайтів</span>
        <button class="btn-icon" onclick="dbCloseCopy()">✕</button>
    </div>
    <form method="POST" action="{{ route('data.bulk-copy') }}" id="form-db-copy">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="q"    value="{{ $q }}">
        <div id="db-copy-ids"></div>
        <div class="db-inline-panel__body">
            <div class="form-group">
                <label class="form-label">Оберіть сайти-цілі</label>
                <div class="db-site-picker" id="db-site-picker">
                    @foreach($sites as $site)
                    <label class="db-site-picker-item">
                        <input type="checkbox" name="target_ids[]" value="{{ $site->id }}">
                        <span>{{ $site->name }}</span>
                        <span class="db-val-sub" style="margin-left:auto">{{ parse_url($site->url, PHP_URL_HOST) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="db-inline-panel__footer">
            <button type="button" class="btn-ghost" onclick="dbCloseCopy()">Скасувати</button>
            <button type="submit" class="btn-primary">Скопіювати</button>
        </div>
    </form>
</div>

{{-- ── Delete form (hidden, submitted via JS) ── --}}
<form method="POST" action="{{ route('data.bulk-delete') }}" id="form-db-delete">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="q"    value="{{ $q }}">
    <div id="db-delete-ids"></div>
</form>

{{-- ── Floating action bar ── --}}
<div class="db-action-bar" id="db-action-bar">
    <span class="db-action-bar__count" id="db-sel-count">0 обрано</span>
    <div class="db-action-bar__btns">
        <button class="btn-primary" onclick="dbOpenEdit()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Редагувати
        </button>
        <button class="btn-ghost" onclick="dbOpenCopy()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
            </svg>
            Копіювати
        </button>
        <button class="btn-danger" onclick="dbDeleteSelected()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                <path d="M10 11v6"/><path d="M14 11v6"/>
            </svg>
            Видалити
        </button>
        <button class="btn-ghost" onclick="dbClearSelection()">Скасувати</button>
    </div>
</div>

@push('scripts')
<script>
// ── Selection ──
function dbUpdateSelection() {
    var checked = document.querySelectorAll('.db-row-cb:checked');
    var count = checked.length;
    var bar = document.getElementById('db-action-bar');
    bar.classList.toggle('is-visible', count > 0);
    document.getElementById('db-sel-count').textContent = count + ' обрано';

    // Highlight rows
    document.querySelectorAll('tr[data-id]').forEach(function(tr) {
        var cb = tr.querySelector('.db-row-cb');
        tr.classList.toggle('is-selected', !!(cb && cb.checked));
    });

    // Sync "select all" checkbox state
    var all = document.querySelectorAll('.db-row-cb').length;
    var cbAll = document.getElementById('db-cb-all');
    if (cbAll) {
        cbAll.indeterminate = count > 0 && count < all;
        cbAll.checked = count === all && all > 0;
    }
}

function dbSelectAll(cbAll) {
    document.querySelectorAll('.db-row-cb').forEach(function(cb) {
        cb.checked = cbAll.checked;
    });
    dbUpdateSelection();
}

function dbClearSelection() {
    document.querySelectorAll('.db-row-cb, #db-cb-all').forEach(function(cb) { cb.checked = false; });
    dbUpdateSelection();
    dbCloseEdit();
    dbCloseCopy();
}

function dbGetSelectedIds() {
    return Array.from(document.querySelectorAll('.db-row-cb:checked')).map(function(cb) { return cb.value; });
}

function dbFillIdInputs(containerId, ids) {
    var c = document.getElementById(containerId);
    if (!c) return;
    c.innerHTML = '';
    ids.forEach(function(id) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'ids[]';
        inp.value = id;
        c.appendChild(inp);
    });
}

// ── Edit panel ──
function dbOpenEdit() {
    var ids = dbGetSelectedIds();
    if (!ids.length) return;
    dbFillIdInputs('db-edit-ids', ids);
    document.getElementById('db-edit-panel-title').textContent = 'Редагувати ' + ids.length + ' записів';
    document.getElementById('db-edit-submit').textContent = 'Застосувати до ' + ids.length;
    dbCloseCopy();
    document.getElementById('db-edit-panel').classList.add('is-visible');
    dbEditFieldChange(document.getElementById('db-edit-field'));
}

function dbCloseEdit() {
    document.getElementById('db-edit-panel').classList.remove('is-visible');
}

function dbEditFieldChange(sel) {
    var field = sel.value;
    var showText     = !['currency','platform'].includes(field);
    var showCurrency = field === 'currency';
    var showPlatform = field === 'platform';

    document.getElementById('db-edit-val-text').style.display     = showText     ? '' : 'none';
    document.getElementById('db-edit-val-currency').style.display = showCurrency ? '' : 'none';
    document.getElementById('db-edit-val-platform').style.display = showPlatform ? '' : 'none';

    // Disable non-active selects so only active one submits
    document.getElementById('db-edit-val-text').disabled     = !showText;
    document.getElementById('db-edit-val-currency').disabled = !showCurrency;
    document.getElementById('db-edit-val-platform').disabled = !showPlatform;
}

// ── Copy panel ──
function dbOpenCopy() {
    var ids = dbGetSelectedIds();
    if (!ids.length) return;
    dbFillIdInputs('db-copy-ids', ids);
    dbCloseEdit();
    document.getElementById('db-copy-panel').classList.add('is-visible');
}

function dbCloseCopy() {
    document.getElementById('db-copy-panel').classList.remove('is-visible');
}

// ── Delete ──
function dbDeleteSelected() {
    var ids = dbGetSelectedIds();
    if (!ids.length) return;
    if (!confirm('Видалити ' + ids.length + ' записів? Це незворотно.')) return;
    dbFillIdInputs('db-delete-ids', ids);
    document.getElementById('form-db-delete').submit();
}

// Init disabled state for value inputs
dbEditFieldChange(document.getElementById('db-edit-field'));
</script>
@endpush

@endsection
