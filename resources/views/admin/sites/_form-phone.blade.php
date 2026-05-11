@php $p = $phone ?? null; $pid = $p?->id ?? 'new'; @endphp

<input type="hidden" name="sort_order" value="{{ old('sort_order', $p?->sort_order ?? 0) }}">

<div class="field">
    <label class="field__label" for="ph-label-{{ $pid }}">Мітка (необов'язково)</label>
    <input type="text" id="ph-label-{{ $pid }}" name="label" class="field__input"
           value="{{ old('label', $p?->label) }}" placeholder="Головний, Продажі, …">
</div>

<div class="field">
    <label class="field__label" for="ph-num-{{ $pid }}">Номер телефону</label>
    <input type="text" id="ph-num-{{ $pid }}" name="number" class="field__input" required
           placeholder="50 123 4567" value="{{ old('number', $p?->number) }}">
</div>

<div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
    <input type="hidden" name="is_primary" value="0">
    <input id="ph-prim-{{ $pid }}" type="checkbox" name="is_primary" value="1"
           {{ old('is_primary', $p?->is_primary ?? false) ? 'checked' : '' }}
           style="accent-color:var(--accent);width:16px;height:16px;">
    <label for="ph-prim-{{ $pid }}" style="font-size:13px;color:var(--text-2);cursor:pointer;">Основний номер</label>
</div>

@include('admin.sites._rule-editor', [
    'rePrefix'    => 'ph-' . $pid,
    'reMode'      => old('geo_mode', $p?->geo_mode ?? 'all'),
    'reCountries' => old('geo_countries', (array) ($p?->geo_countries ?? [])),
    'reOptions'   => $visRuleOptions ?? [],
])
