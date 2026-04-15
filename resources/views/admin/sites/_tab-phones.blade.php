{{-- Phones tab — variables: $site, $phones --}}
<div class="data-tab-header">
    <h2 class="data-tab__title">Телефони <span class="data-tab__count">{{ $phones->count() }}</span></h2>
    <button class="btn-primary" onclick="openDrawer('drawer-phone-create')">+ Додати</button>
</div>

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
            <div class="data-row__main" style="flex-direction:column;align-items:flex-start;gap:3px;">
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                    @if($phone->is_primary)
                        <span class="data-badge data-badge--primary">Primary</span>
                    @endif
                    <span class="phone-row__number">{{ ltrim($phone->number, '+') }}</span>
                </div>
                <div class="phone-row__sub">
                    <span class="phone-row__id">#{{ $phone->id }}</span>
                    @if($phone->label)
                        <span class="phone-row__label">{{ $phone->label }}</span>
                    @endif
                    <span class="phone-row__geo">{{ $phone->country_iso }} +{{ $phone->dial_code }}</span>
                </div>
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
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Країна (ISO)</label>
                    <input type="text" name="country_iso" class="form-input" placeholder="UA" maxlength="2"
                           style="text-transform:uppercase" oninput="syncDialCode(this)" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Код (+)</label>
                    <input type="text" name="dial_code" class="form-input" placeholder="380" maxlength="8" id="dial-code-create" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Номер <span class="form-hint">(без коду країни)</span></label>
                <input type="text" name="number" class="form-input" placeholder="(073) 900-80-01" maxlength="32" required>
            </div>
            <div class="form-group">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" name="is_primary" value="1"> Основний номер
                </label>
            </div>
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
            {{-- Geo section --}}
            <details class="form-details">
                <summary class="form-details__summary">Геодані</summary>
                <div class="form-details__body">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Країна (ISO)</label>
                            <input type="text" name="country_iso" class="form-input"
                                   value="{{ old('country_iso', $phone->country_iso) }}" maxlength="2"
                                   style="text-transform:uppercase" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Код (+)</label>
                            <input type="text" name="dial_code" class="form-input"
                                   value="{{ old('dial_code', $phone->dial_code) }}" maxlength="8" required>
                        </div>
                    </div>
                </div>
            </details>
            <div class="form-group">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" name="is_primary" value="1" {{ $phone->is_primary ? 'checked' : '' }}> Основний номер
                </label>
            </div>
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
// Auto-fill dial code from country ISO (common ones)
var dialCodes = {
    'UA': '380', 'PL': '48', 'DE': '49', 'US': '1', 'GB': '44',
    'FR': '33', 'IT': '39', 'ES': '34', 'CZ': '420', 'RO': '40',
    'SK': '421', 'HU': '36', 'BY': '375', 'MD': '373', 'GE': '995',
};
function syncDialCode(input) {
    var iso = input.value.toUpperCase();
    var codeEl = document.getElementById('dial-code-create');
    if (codeEl && dialCodes[iso]) codeEl.value = dialCodes[iso];
}
</script>
