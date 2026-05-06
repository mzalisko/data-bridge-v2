{{-- Phones tab — variables: $site, $phones, $sectionMode (optional) --}}
@unless($sectionMode ?? false)
<div class="data-tab-header">
    <h2 class="data-tab__title">Телефони <span class="data-tab__count">{{ $phones->count() }}</span></h2>
    <button class="btn-primary" onclick="openDrawer('drawer-phone-create')">+ Додати</button>
</div>
@endunless

@if(session('success') && request('tab') === 'phones')
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($phones->isEmpty())
    <div class="data-tab__empty">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.72-.72a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.5 16a2 2 0 0 1 .42.92z"/></svg>
        <p>Телефонів ще немає</p>
    </div>
@else
    <ul class="data-list">
        @foreach($phones as $phone)
        <li class="data-row">
            {{-- Col 1: country badge only --}}
            <div class="data-row__indicator">
                <span class="data-badge">{{ $phone->country_iso }} +{{ $phone->dial_code }}</span>
            </div>
            {{-- Col 2: number --}}
            <div class="data-row__main">
                <span class="data-row__val">{{ ltrim($phone->number, '+') }}</span>
            </div>
            {{-- Col 3: primary + label + geo --}}
            <div class="data-row__secondary">
                @if($phone->is_primary)
                    <span class="data-badge data-badge--primary">Primary</span>
                @endif
                @if($phone->label)
                    <span class="data-row__label">{{ $phone->label }}</span>
                @endif
                @if($phone->geo_mode === null || $phone->geo_mode === '')
                    <span class="geo-badge geo-badge--hidden geo-badge--sm">Прих.</span>
                @elseif($phone->geo_mode === 'all')
                    <span class="geo-badge geo-badge--all geo-badge--sm">Всі</span>
                @elseif($phone->geo_mode === 'include')
                    <span class="geo-badge geo-badge--include geo-badge--sm">{{ $phone->geo_countries ?: '…' }}</span>
                @elseif($phone->geo_mode === 'exclude')
                    <span class="geo-badge geo-badge--exclude geo-badge--sm">≠ {{ $phone->geo_countries ?: '…' }}</span>
                @endif
            </div>
            <div class="data-row__actions">
                <button class="btn-icon" title="Редагувати"
                        onclick="openDrawer('drawer-phone-{{ $phone->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <form method="POST" action="{{ route('phones.destroy', [$site, $phone]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon btn-icon--danger" title="Видалити"
                            onclick="return confirm('Видалити цей телефон?')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </form>
            </div>
        </li>
        @endforeach
    </ul>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-phone-create-overlay" onclick="closeDrawer('drawer-phone-create')"></div>
<div class="drawer" id="drawer-phone-create">
    <div class="drawer__header">
        <span class="drawer__title">Новий телефон</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-phone-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('phones.store', $site) }}" class="form-stack" id="form-phone-create">
            @csrf
            <div class="form-group">
                <label class="form-label">Мітка <span class="form-hint">(необов'язково)</span></label>
                <input type="text" name="label" class="form-input" placeholder="Основний, WhatsApp, Офіс…" maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Країна</label>
                @if(($countries ?? collect())->isNotEmpty())
                <select class="form-input form-select" onchange="selectCountry(this, 'create')" id="country-select-create">
                    <option value="">— Оберіть країну —</option>
                    @foreach($countries as $c)
                    <option value="{{ $c->iso }}" data-dial="{{ $c->dial_code }}">
                        {{ $c->iso }} — {{ $c->name ?? $c->iso }} (+{{ $c->dial_code }})
                    </option>
                    @endforeach
                    <option value="__other__">Інше (ввести вручну)</option>
                </select>
                @endif
                <div id="country-manual-create" style="{{ ($countries ?? collect())->isNotEmpty() ? 'display:none' : '' }}">
                    <div class="form-row" style="margin-top:6px;">
                        <input type="text" id="country-iso-manual-create" class="form-input" placeholder="UA" maxlength="2"
                               style="text-transform:uppercase;width:80px">
                        <input type="text" class="form-input" placeholder="+380" style="flex:1">
                    </div>
                </div>
                <input type="hidden" name="country_iso" id="country-iso-create" required>
                <input type="hidden" name="dial_code"   id="dial-code-create"   required>
            </div>
            <div id="country-selected-chip-create" class="phone-country-chip" style="display:none"></div>
            <div class="form-group">
                <label class="form-label">Номер <span class="form-hint">(без коду країни)</span></label>
                <input type="text" name="number" class="form-input" placeholder="(073) 900-80-01" maxlength="32" required>
            </div>
            <div class="form-group">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" name="is_primary" value="1"> Основний номер
                </label>
            </div>
            @include('admin.sites._geo-visibility', ['geoPrefix' => 'phone-create', 'geoModel' => null])
            <input type="hidden" name="sort_order" value="0">
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-phone-create')">Скасувати</button>
        <button type="submit" form="form-phone-create" class="btn-primary">Додати</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($phones as $phone)
<div class="drawer-overlay" id="drawer-phone-{{ $phone->id }}-overlay" onclick="closeDrawer('drawer-phone-{{ $phone->id }}')"></div>
<div class="drawer" id="drawer-phone-{{ $phone->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $phone->label ?: '+' . $phone->dial_code . ' ' . ltrim($phone->number, '+') }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-phone-{{ $phone->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        {{-- Info chip: ID + geo --}}
        <div class="drawer-id-chip">
            <span style="color:var(--text-muted)">#{{ $phone->id }}</span>
            <span style="color:var(--border-color)">·</span>
            <span>{{ $phone->country_iso }} +{{ $phone->dial_code }}</span>
            @if($phone->is_primary)
                <span style="color:var(--border-color)">·</span>
                <span style="color:var(--accent)">primary</span>
            @endif
        </div>
        <form method="POST" action="{{ route('phones.update', [$site, $phone]) }}" class="form-stack" id="form-phone-{{ $phone->id }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Мітка</label>
                <input type="text" name="label" class="form-input" value="{{ old('label', $phone->label) }}" maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Номер <span class="form-hint">(без коду країни)</span></label>
                <input type="text" name="number" class="form-input" value="{{ old('number', ltrim($phone->number, '+')) }}" maxlength="32" required>
            </div>
            {{-- Country select --}}
            @php
                $editIso  = old('country_iso', $phone->country_iso);
                $editDial = old('dial_code',   $phone->dial_code);
                $inList   = ($countries ?? collect())->contains('iso', $editIso);
            @endphp
            <div class="form-group">
                <label class="form-label">Країна</label>
                @if(($countries ?? collect())->isNotEmpty())
                <select class="form-input form-select" onchange="selectCountry(this, 'edit-{{ $phone->id }}')"
                        id="country-select-edit-{{ $phone->id }}">
                    <option value="">— Оберіть країну —</option>
                    @foreach($countries as $c)
                    <option value="{{ $c->iso }}" data-dial="{{ $c->dial_code }}"
                            {{ $editIso === $c->iso ? 'selected' : '' }}>
                        {{ $c->iso }} — {{ $c->name ?? $c->iso }} (+{{ $c->dial_code }})
                    </option>
                    @endforeach
                    <option value="__other__" {{ !$inList && $editIso ? 'selected' : '' }}>Інше (ввести вручну)</option>
                </select>
                @endif
                <div id="country-manual-edit-{{ $phone->id }}"
                     style="{{ (!$inList && $editIso) || ($countries ?? collect())->isEmpty() ? '' : 'display:none' }}">
                    <div class="form-row" style="margin-top:6px;">
                        <input type="text" id="country-iso-manual-edit-{{ $phone->id }}"
                               class="form-input" placeholder="UA" maxlength="2"
                               style="text-transform:uppercase;width:80px"
                               value="{{ !$inList ? $editIso : '' }}"
                               oninput="syncManualIso(this, 'edit-{{ $phone->id }}')">
                        <input type="text" id="dial-manual-edit-{{ $phone->id }}"
                               class="form-input" placeholder="380"
                               style="flex:1"
                               value="{{ !$inList ? $editDial : '' }}"
                               oninput="syncManualDial(this, 'edit-{{ $phone->id }}')">
                    </div>
                </div>
                <input type="hidden" name="country_iso" id="country-iso-edit-{{ $phone->id }}"
                       value="{{ $editIso }}" required>
                <input type="hidden" name="dial_code"   id="dial-code-edit-{{ $phone->id }}"
                       value="{{ $editDial }}" required>
            </div>
            <div class="form-group">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" name="is_primary" value="1" {{ $phone->is_primary ? 'checked' : '' }}> Основний номер
                </label>
            </div>
            @include('admin.sites._geo-visibility', ['geoPrefix' => 'phone-' . $phone->id, 'geoModel' => $phone])
            <input type="hidden" name="sort_order" value="{{ $phone->sort_order }}">
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-phone-{{ $phone->id }}')">Скасувати</button>
        <button type="submit" form="form-phone-{{ $phone->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

<script>
// Country select handler — fills hidden iso/dial_code inputs + shows manual row if "Інше"
function selectCountry(sel, prefix) {
    var val  = sel.value;
    var dial = sel.options[sel.selectedIndex]?.dataset?.dial || '';
    var isoEl  = document.getElementById('country-iso-'  + prefix);
    var dialEl = document.getElementById('dial-code-'    + prefix);
    var manual = document.getElementById('country-manual-' + prefix);

    if (val === '__other__') {
        if (isoEl)  isoEl.value  = '';
        if (dialEl) dialEl.value = '';
        if (manual) manual.style.display = '';
        // focus first manual input
        var firstManual = manual?.querySelector('input');
        if (firstManual) firstManual.focus();
    } else {
        if (isoEl)  isoEl.value  = val;
        if (dialEl) dialEl.value = dial;
        if (manual) manual.style.display = 'none';
    }
}

// Manual ISO input → sync hidden field
function syncManualIso(input, prefix) {
    var isoEl = document.getElementById('country-iso-' + prefix);
    if (isoEl) isoEl.value = input.value.toUpperCase();
}

// Manual dial input → sync hidden field
function syncManualDial(input, prefix) {
    var dialEl = document.getElementById('dial-code-' + prefix);
    if (dialEl) dialEl.value = input.value;
}
</script>
