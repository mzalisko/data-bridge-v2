@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/sites.css') }}?v={{ filemtime(public_path('assets/css/pages/sites.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/pages/batch.css') }}?v={{ filemtime(public_path('assets/css/pages/batch.css')) }}">
@endpush

@section('title', 'Batch Edit')

@section('content')

<div class="page-toolbar">
    <div style="display:flex;align-items:center;gap:var(--space-md);">
        <a href="{{ route('sites.index') }}" class="btn-ghost">← Сайти</a>
        <h1 class="page-title">Batch Edit</h1>
        <span class="batch-page-count">{{ $sites->count() }} {{ $sites->count() === 1 ? 'сайт' : ($sites->count() < 5 ? 'сайти' : 'сайтів') }}</span>
    </div>
</div>

<div class="batch-layout">

    {{-- Left: selected sites --}}
    <div class="batch-sites-col">
        <div class="batch-col-title">Обрані сайти</div>
        <div class="batch-sites-list">
            @foreach($sites as $site)
            @php
                $color  = $site->siteGroup?->color ?? '#708499';
                $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
            @endphp
            <div class="batch-site-row">
                <div class="batch-site-favicon" style="background:{{ $color }}26;color:{{ $color }};">{{ $letter }}</div>
                <div class="batch-site-info">
                    <span class="batch-site-name">{{ $site->name }}</span>
                    <span class="batch-site-url">{{ $site->url }}</span>
                </div>
                <span class="batch-site-status batch-site-status--{{ $site->is_active ? 'active' : 'off' }}">
                    {{ $site->is_active ? 'Active' : 'Off' }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Right: action form --}}
    <div class="batch-form-col">
        <div class="batch-col-title">Дія</div>

        <form method="POST" action="{{ route('sites.batch') }}" id="form-batch" class="form-stack">
            @csrf

            {{-- Hidden IDs --}}
            @foreach($sites as $site)
            <input type="hidden" name="ids[]" value="{{ $site->id }}">
            @endforeach

            {{-- Action tabs --}}
            <div class="batch-action-tabs">
                <button type="button" class="batch-tab batch-tab--active" data-action="status" onclick="batchTab('status')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Статус
                </button>
                <button type="button" class="batch-tab" data-action="group" onclick="batchTab('group')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    Група
                </button>
                <button type="button" class="batch-tab" data-action="phone" onclick="batchTab('phone')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.72-.72a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Телефон
                </button>
                <button type="button" class="batch-tab" data-action="price" onclick="batchTab('price')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Ціна
                </button>
                <button type="button" class="batch-tab" data-action="address" onclick="batchTab('address')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Адреса
                </button>
                <button type="button" class="batch-tab" data-action="social" onclick="batchTab('social')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                    Соцмережа
                </button>
                <button type="button" class="batch-tab batch-tab--danger" data-action="delete" onclick="batchTab('delete')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                    Видалити
                </button>
            </div>

            <input type="hidden" name="action" id="batch-action-input" value="status">

            {{-- Panel: Status --}}
            <div class="batch-panel" id="panel-status">
                <div class="form-group">
                    <label class="form-label">Новий статус для всіх {{ $sites->count() }} сайтів</label>
                    <div class="batch-status-grid">
                        <label class="batch-status-tile batch-status-tile--active" id="tile-active">
                            <input type="radio" name="value" value="active" checked onchange="batchStatusSync(this)">
                            <span class="batch-status-dot" style="background:var(--dot-ok)"></span>
                            <span>Active</span>
                        </label>
                        <label class="batch-status-tile" id="tile-inactive">
                            <input type="radio" name="value" value="inactive" onchange="batchStatusSync(this)">
                            <span class="batch-status-dot" style="background:var(--dot-off)"></span>
                            <span>Disabled</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Panel: Group --}}
            <div class="batch-panel" id="panel-group" style="display:none">
                <div class="form-group">
                    <label class="form-label">Нова група для всіх {{ $sites->count() }} сайтів</label>
                    <select name="value" class="form-input form-select" id="batch-group-sel">
                        <option value="none">— Без групи —</option>
                        @foreach($groups as $group)
                        @php $c = $group->color ?? '#708499'; @endphp
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Panel: Phone --}}
            <div class="batch-panel" id="panel-phone" style="display:none">
                <p class="form-hint" style="margin-bottom:var(--space-md)">
                    Буде <strong>додано</strong> до кожного з {{ $sites->count() }} сайтів.
                </p>
                <div class="form-group">
                    <label class="form-label">Країна</label>
                    @if($countries->isNotEmpty())
                    <select class="form-input form-select" onchange="batchPhoneCountry(this)">
                        <option value="">— Оберіть —</option>
                        @foreach($countries as $c)
                        <option value="{{ $c->iso }}" data-dial="{{ $c->dial_code }}">
                            {{ $c->iso }} — {{ $c->name ?? $c->iso }} (+{{ $c->dial_code }})
                        </option>
                        @endforeach
                    </select>
                    @endif
                    <input type="hidden" name="phone_country_iso" id="phone-iso">
                    <input type="hidden" name="phone_dial_code"   id="phone-dial">
                </div>
                <div class="form-group">
                    <label class="form-label">Номер <span class="form-hint">(без коду країни)</span></label>
                    <input type="text" name="phone_number" class="form-input" placeholder="(073) 900-80-01" maxlength="32">
                </div>
                <div class="form-group">
                    <label class="form-label">Мітка <span class="form-hint">(необов'язково)</span></label>
                    <input type="text" name="phone_label" class="form-input" placeholder="Основний, WhatsApp…" maxlength="100">
                </div>
            </div>

            {{-- Panel: Price --}}
            <div class="batch-panel" id="panel-price" style="display:none">
                <p class="form-hint" style="margin-bottom:var(--space-md)">
                    Буде <strong>додано</strong> до кожного з {{ $sites->count() }} сайтів.
                </p>
                <div class="form-row">
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Сума</label>
                        <input type="number" name="price_amount" class="form-input" placeholder="1500" min="0" step="0.01">
                    </div>
                    <div class="form-group" style="width:110px">
                        <label class="form-label">Валюта</label>
                        <select name="price_currency" class="form-input form-select">
                            <option value="UAH">UAH ₴</option>
                            <option value="USD">USD $</option>
                            <option value="EUR">EUR €</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Мітка <span class="form-hint">(необов'язково)</span></label>
                    <input type="text" name="price_label" class="form-input" placeholder="Базова, VIP…" maxlength="100">
                </div>
            </div>

            {{-- Panel: Address --}}
            <div class="batch-panel" id="panel-address" style="display:none">
                <p class="form-hint" style="margin-bottom:var(--space-md)">
                    Буде <strong>додано</strong> до кожного з {{ $sites->count() }} сайтів.
                </p>
                <div class="form-group">
                    <label class="form-label">Країна ISO</label>
                    <input type="text" name="address_country_iso" class="form-input"
                           placeholder="UA" maxlength="2"
                           style="text-transform:uppercase;width:90px">
                </div>
                <div class="form-group">
                    <label class="form-label">Місто</label>
                    <input type="text" name="address_city" class="form-input" placeholder="Київ" maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Вулиця <span class="form-hint">(необов'язково)</span></label>
                    <input type="text" name="address_street" class="form-input"
                           placeholder="вул. Хрещатик, 1" maxlength="255">
                </div>
            </div>

            {{-- Panel: Social --}}
            <div class="batch-panel" id="panel-social" style="display:none">
                <p class="form-hint" style="margin-bottom:var(--space-md)">
                    Буде <strong>додано</strong> до кожного з {{ $sites->count() }} сайтів.
                </p>
                <div class="form-group">
                    <label class="form-label">Платформа</label>
                    <select name="social_platform" class="form-input form-select">
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
                </div>
                <div class="form-group">
                    <label class="form-label">URL / посилання</label>
                    <input type="text" name="social_url" class="form-input"
                           placeholder="https://instagram.com/..." maxlength="255">
                </div>
            </div>

            {{-- Panel: Delete --}}
            <div class="batch-panel" id="panel-delete" style="display:none">
                <div class="batch-delete-confirm">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--dot-off)" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p>Буде <strong>безповоротно видалено</strong> {{ $sites->count() }} {{ $sites->count() === 1 ? 'сайт' : ($sites->count() < 5 ? 'сайти' : 'сайтів') }} разом із усіма їхніми даними.</p>
                </div>
            </div>

            <div class="batch-form-footer">
                <a href="{{ route('sites.index') }}" class="btn-ghost">Скасувати</a>
                <button type="submit" class="btn-primary" id="batch-submit-btn">
                    Застосувати до {{ $sites->count() }} сайтів
                </button>
            </div>
        </form>
    </div>

</div>

@push('scripts')
<script>
var batchPanels = ['status','group','phone','price','address','social','delete'];

function batchTab(action) {
    document.getElementById('batch-action-input').value = action;

    batchPanels.forEach(function(a) {
        document.getElementById('panel-' + a).style.display = a === action ? '' : 'none';
    });

    document.querySelectorAll('.batch-tab').forEach(function(tab) {
        tab.classList.toggle('batch-tab--active', tab.dataset.action === action);
    });

    var groupSel = document.getElementById('batch-group-sel');
    if (groupSel) groupSel.disabled = action !== 'group';

    // Submit button: danger style for delete
    var btn = document.getElementById('batch-submit-btn');
    if (action === 'delete') {
        btn.className = 'btn-danger';
        btn.textContent = 'Видалити {{ $sites->count() }} сайтів';
        btn.onclick = function(e) {
            if (!confirm('Видалити {{ $sites->count() }} сайтів? Це незворотно.')) e.preventDefault();
        };
    } else {
        btn.className = 'btn-primary';
        btn.textContent = 'Застосувати до {{ $sites->count() }} сайтів';
        btn.onclick = null;
    }
}

function batchStatusSync(input) {
    document.querySelectorAll('.batch-status-tile').forEach(function(t) {
        t.classList.toggle('batch-status-tile--active', t.querySelector('input')?.checked);
    });
}

function batchPhoneCountry(sel) {
    var iso  = sel.value;
    var dial = sel.options[sel.selectedIndex]?.dataset?.dial || '';
    document.getElementById('phone-iso').value  = iso;
    document.getElementById('phone-dial').value = dial;
}

// Init: disable group select
document.getElementById('batch-group-sel').disabled = true;
</script>
@endpush

@endsection
