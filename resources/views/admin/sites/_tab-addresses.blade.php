{{-- Addresses tab — variables: $site, $addresses --}}
<div class="data-tab-header">
    <h2 class="data-tab__title">Адреси <span class="data-tab__count">{{ $addresses->count() }}</span></h2>
    <button class="btn-primary" onclick="openDrawer('drawer-address-create')">+ Додати</button>
</div>

@if(session('success') && request('tab') === 'addresses')
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($addresses->isEmpty())
    <div class="data-tab__empty">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <p>Адрес ще немає</p>
    </div>
@else
    <ul class="data-list">
        @foreach($addresses as $address)
        <li class="data-row">
            {{-- Col 1: country + primary --}}
            <div class="data-row__indicator">
                <span class="data-badge">{{ $address->country_iso }}{{ $address->postal_code ? ' '.$address->postal_code : '' }}</span>
                @if($address->is_primary)
                    <span class="data-badge data-badge--primary">Primary</span>
                @endif
            </div>
            {{-- Col 2: city + street --}}
            <div class="data-row__main">
                <span class="data-row__val">{{ $address->city }}{{ $address->street ? ', '.$address->street : '' }}{{ $address->building ? ' '.$address->building : '' }}</span>
            </div>
            {{-- Col 3: label + geo --}}
            <div class="data-row__secondary">
                @if($address->label)
                    <span class="data-row__label">{{ $address->label }}</span>
                @endif
                @if($address->geo_mode === null || $address->geo_mode === '')
                    <span class="geo-badge geo-badge--hidden geo-badge--sm">Прих.</span>
                @elseif($address->geo_mode === 'all')
                    <span class="geo-badge geo-badge--all geo-badge--sm">Всі</span>
                @elseif($address->geo_mode === 'include')
                    <span class="geo-badge geo-badge--include geo-badge--sm">{{ $address->geo_countries ?: '…' }}</span>
                @elseif($address->geo_mode === 'exclude')
                    <span class="geo-badge geo-badge--exclude geo-badge--sm">≠ {{ $address->geo_countries ?: '…' }}</span>
                @endif
            </div>
            <div class="data-row__actions">
                <button class="btn-icon" title="Редагувати" onclick="openDrawer('drawer-address-{{ $address->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <form method="POST" action="{{ route('addresses.destroy', [$site, $address]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon btn-icon--danger" title="Видалити"
                            onclick="return confirm('Видалити цю адресу?')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </form>
            </div>
        </li>
        @endforeach
    </ul>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-address-create-overlay" onclick="closeDrawer('drawer-address-create')"></div>
<div class="drawer" id="drawer-address-create">
    <div class="drawer__header">
        <span class="drawer__title">Нова адреса</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-address-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('addresses.store', $site) }}" class="form-stack" id="form-address-create">
            @csrf
            <div class="form-group">
                <label class="form-label">Мітка <span class="form-hint">(необов'язково)</span></label>
                <input type="text" name="label" class="form-input" placeholder="Головний офіс" maxlength="100">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Країна (ISO)</label>
                    <input type="text" name="country_iso" class="form-input" placeholder="UA" maxlength="2" style="text-transform:uppercase" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Поштовий індекс</label>
                    <input type="text" name="postal_code" class="form-input" placeholder="01001" maxlength="20">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Місто</label>
                <input type="text" name="city" class="form-input" placeholder="Kyiv" maxlength="255" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Вулиця</label>
                    <input type="text" name="street" class="form-input" placeholder="Хрещатик" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-label">Будинок</label>
                    <input type="text" name="building" class="form-input" placeholder="1А" maxlength="50">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" name="is_primary" value="1"> Основна адреса
                </label>
            </div>
            @include('admin.sites._geo-visibility', ['geoPrefix' => 'address-create', 'geoModel' => null])
            <input type="hidden" name="sort_order" value="0">
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-address-create')">Скасувати</button>
        <button type="submit" form="form-address-create" class="btn-primary">Додати</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($addresses as $address)
<div class="drawer-overlay" id="drawer-address-{{ $address->id }}-overlay" onclick="closeDrawer('drawer-address-{{ $address->id }}')"></div>
<div class="drawer" id="drawer-address-{{ $address->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $address->city }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-address-{{ $address->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <div class="drawer-id-chip">
            <span style="color:var(--text-muted)">#{{ $address->id }}</span>
            <span style="color:var(--border-color)">·</span>
            <span>{{ $address->country_iso }}</span>
            @if($address->is_primary)
                <span style="color:var(--border-color)">·</span>
                <span style="color:var(--accent)">primary</span>
            @endif
        </div>
        <form method="POST" action="{{ route('addresses.update', [$site, $address]) }}" class="form-stack" id="form-address-{{ $address->id }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Мітка</label>
                <input type="text" name="label" class="form-input" value="{{ old('label', $address->label) }}" maxlength="100">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Країна (ISO)</label>
                    <input type="text" name="country_iso" class="form-input" value="{{ old('country_iso', $address->country_iso) }}" maxlength="2" style="text-transform:uppercase" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Поштовий індекс</label>
                    <input type="text" name="postal_code" class="form-input" value="{{ old('postal_code', $address->postal_code) }}" maxlength="20">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Місто</label>
                <input type="text" name="city" class="form-input" value="{{ old('city', $address->city) }}" maxlength="255" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Вулиця</label>
                    <input type="text" name="street" class="form-input" value="{{ old('street', $address->street) }}" maxlength="255">
                </div>
                <div class="form-group">
                    <label class="form-label">Будинок</label>
                    <input type="text" name="building" class="form-input" value="{{ old('building', $address->building) }}" maxlength="50">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" name="is_primary" value="1" {{ old('is_primary', $address->is_primary) ? 'checked' : '' }}> Основна адреса
                </label>
            </div>
            @include('admin.sites._geo-visibility', ['geoPrefix' => 'address-' . $address->id, 'geoModel' => $address])
            <input type="hidden" name="sort_order" value="{{ $address->sort_order }}">
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-address-{{ $address->id }}')">Скасувати</button>
        <button type="submit" form="form-address-{{ $address->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach
