{{-- Prices tab — variables: $site, $prices --}}
<div class="data-tab-header">
    <h2 class="data-tab__title">Ціни <span class="data-tab__count">{{ $prices->count() }}</span></h2>
    <button class="btn-primary" onclick="openDrawer('drawer-price-create')">+ Додати</button>
</div>

@if(session('success') && request('tab') === 'prices')
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($prices->isEmpty())
    <div class="data-tab__empty">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        <p>Цін ще немає</p>
    </div>
@else
    <ul class="data-list">
        @foreach($prices as $price)
        <li class="data-row {{ !$price->is_visible ? 'data-row--muted' : '' }}">
            <div class="data-row__main">
                <span class="data-row__val">{{ number_format($price->amount, 2) }} {{ $price->currency }}</span>
                <span class="data-row__label">{{ $price->label }}</span>
                @if($price->period)
                    <span class="data-row__meta">/ {{ $price->period }}</span>
                @endif
                @if(!$price->is_visible)
                    <span class="data-badge data-badge--hidden">Прихована</span>
                @endif
                @if($price->geo_mode === null || $price->geo_mode === '')
                    <span class="geo-badge geo-badge--hidden geo-badge--sm">Прих.</span>
                @elseif($price->geo_mode === 'all')
                    <span class="geo-badge geo-badge--all geo-badge--sm">Всі</span>
                @elseif($price->geo_mode === 'include')
                    <span class="geo-badge geo-badge--include geo-badge--sm">{{ $price->geo_countries ?: '…' }}</span>
                @elseif($price->geo_mode === 'exclude')
                    <span class="geo-badge geo-badge--exclude geo-badge--sm">≠ {{ $price->geo_countries ?: '…' }}</span>
                @endif
            </div>
            <div class="data-row__actions">
                <button class="btn-icon" title="Редагувати" onclick="openDrawer('drawer-price-{{ $price->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <form method="POST" action="{{ route('prices.destroy', [$site, $price]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon btn-icon--danger" title="Видалити"
                            onclick="return confirm('Видалити цю ціну?')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </form>
            </div>
        </li>
        @endforeach
    </ul>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-price-create-overlay" onclick="closeDrawer('drawer-price-create')"></div>
<div class="drawer" id="drawer-price-create">
    <div class="drawer__header">
        <span class="drawer__title">Нова ціна</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-price-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('prices.store', $site) }}" class="form-stack" id="form-price-create">
            @csrf
            <div class="form-group">
                <label class="form-label">Назва послуги / тарифу</label>
                <input type="text" name="label" class="form-input" placeholder="Базовий пакет" maxlength="255" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Сума</label>
                    <input type="number" name="amount" class="form-input" placeholder="1500.00" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Валюта</label>
                    <select name="currency" class="form-input form-select">
                        <option value="UAH">UAH</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Період <span class="form-hint">(необов'язково)</span></label>
                <input type="text" name="period" class="form-input" placeholder="month, year, visit…" maxlength="32">
            </div>
            <div class="form-group">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" name="is_visible" value="1" checked> Відображати
                </label>
            </div>
            @include('admin.sites._geo-visibility', ['geoPrefix' => 'price-create', 'geoModel' => null])
            <input type="hidden" name="sort_order" value="0">
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-price-create')">Скасувати</button>
        <button type="submit" form="form-price-create" class="btn-primary">Додати</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($prices as $price)
<div class="drawer-overlay" id="drawer-price-{{ $price->id }}-overlay" onclick="closeDrawer('drawer-price-{{ $price->id }}')"></div>
<div class="drawer" id="drawer-price-{{ $price->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $price->label }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-price-{{ $price->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <div class="drawer-id-chip">
            <span style="color:var(--text-muted)">#{{ $price->id }}</span>
            <span style="color:var(--border-color)">·</span>
            <span>{{ $price->currency }}</span>
        </div>
        <form method="POST" action="{{ route('prices.update', [$site, $price]) }}" class="form-stack" id="form-price-{{ $price->id }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Назва</label>
                <input type="text" name="label" class="form-input" value="{{ old('label', $price->label) }}" maxlength="255" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Сума</label>
                    <input type="number" name="amount" class="form-input" value="{{ old('amount', $price->amount) }}" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Валюта</label>
                    <select name="currency" class="form-input form-select">
                        @foreach(['UAH','USD','EUR'] as $cur)
                        <option value="{{ $cur }}" {{ old('currency', $price->currency) === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Період</label>
                <input type="text" name="period" class="form-input" value="{{ old('period', $price->period) }}" maxlength="32">
            </div>
            <div class="form-group">
                <label class="form-label form-label--checkbox">
                    <input type="checkbox" name="is_visible" value="1" {{ old('is_visible', $price->is_visible) ? 'checked' : '' }}> Відображати
                </label>
            </div>
            @include('admin.sites._geo-visibility', ['geoPrefix' => 'price-' . $price->id, 'geoModel' => $price])
            <input type="hidden" name="sort_order" value="{{ $price->sort_order }}">
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-price-{{ $price->id }}')">Скасувати</button>
        <button type="submit" form="form-price-{{ $price->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach
