@php $p = $price ?? null; $pid = $p?->id ?? 'new'; @endphp

<input type="hidden" name="sort_order" value="{{ old('sort_order', $p?->sort_order ?? 0) }}">

<div class="field">
    <label class="field__label" for="pr-label-{{ $pid }}">Мітка</label>
    <input type="text" id="pr-label-{{ $pid }}" name="label" class="field__input" required
           placeholder="Стандартний пакет" value="{{ old('label', $p?->label) }}">
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:10px;">
    <div class="field">
        <label class="field__label" for="pr-amt-{{ $pid }}">Сума</label>
        <input type="number" step="0.01" min="0" id="pr-amt-{{ $pid }}" name="amount"
               class="field__input" required value="{{ old('amount', $p?->amount) }}">
    </div>
    <div class="field">
        <label class="field__label" for="pr-cur-{{ $pid }}">Валюта</label>
        <input type="text" maxlength="3" id="pr-cur-{{ $pid }}" name="currency"
               class="field__input" required style="text-transform:uppercase;"
               placeholder="UAH" value="{{ old('currency', $p?->currency) }}">
    </div>
</div>

<div class="field">
    <label class="field__label" for="pr-per-{{ $pid }}">Період (необов'язково)</label>
    <input type="text" id="pr-per-{{ $pid }}" name="period" class="field__input"
           placeholder="місяць, рік, одноразово…" value="{{ old('period', $p?->period) }}">
</div>

<div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
    <input type="hidden" name="is_visible" value="0">
    <input id="pr-vis-{{ $pid }}" type="checkbox" name="is_visible" value="1"
           {{ old('is_visible', $p?->is_visible ?? true) ? 'checked' : '' }}
           style="accent-color:var(--accent);width:16px;height:16px;">
    <label for="pr-vis-{{ $pid }}" style="font-size:13px;color:var(--text-2);cursor:pointer;">Відображати на сайті</label>
</div>
