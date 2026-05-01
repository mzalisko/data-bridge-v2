@php $a = $address ?? null; $aid = $a?->id ?? 'new'; @endphp

<input type="hidden" name="sort_order" value="{{ old('sort_order', $a?->sort_order ?? 0) }}">

<div class="field">
    <label class="field__label" for="ad-label-{{ $aid }}">Label (optional)</label>
    <input type="text" id="ad-label-{{ $aid }}" name="label" class="field__input"
           value="{{ old('label', $a?->label) }}" placeholder="HQ, Warehouse, …">
</div>

<div class="field">
    <label class="field__label" for="ad-iso-{{ $aid }}">Country</label>
    <select id="ad-iso-{{ $aid }}" name="country_iso" class="field__input" required>
        <option value="">—</option>
        @foreach($countries as $c)
            <option value="{{ $c->iso }}" {{ old('country_iso', $a?->country_iso) === $c->iso ? 'selected' : '' }}>
                {{ $c->iso }} {{ ($c->name && strcasecmp($c->name, $c->iso) !== 0) ? '— '.$c->name : '' }}
            </option>
        @endforeach
    </select>
</div>

<div class="field">
    <label class="field__label" for="ad-city-{{ $aid }}">City</label>
    <input type="text" id="ad-city-{{ $aid }}" name="city" class="field__input" required
           value="{{ old('city', $a?->city) }}">
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:10px;">
    <div class="field">
        <label class="field__label" for="ad-street-{{ $aid }}">Street</label>
        <input type="text" id="ad-street-{{ $aid }}" name="street" class="field__input"
               value="{{ old('street', $a?->street) }}">
    </div>
    <div class="field">
        <label class="field__label" for="ad-bld-{{ $aid }}">Building</label>
        <input type="text" id="ad-bld-{{ $aid }}" name="building" class="field__input"
               value="{{ old('building', $a?->building) }}">
    </div>
</div>

<div class="field">
    <label class="field__label" for="ad-zip-{{ $aid }}">Postal code</label>
    <input type="text" id="ad-zip-{{ $aid }}" name="postal_code" class="field__input"
           value="{{ old('postal_code', $a?->postal_code) }}">
</div>

<div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
    <input type="hidden" name="is_primary" value="0">
    <input id="ad-prim-{{ $aid }}" type="checkbox" name="is_primary" value="1"
           {{ old('is_primary', $a?->is_primary ?? false) ? 'checked' : '' }}
           style="accent-color:var(--accent);width:16px;height:16px;">
    <label for="ad-prim-{{ $aid }}" style="font-size:13px;color:var(--text-2);cursor:pointer;">Primary address</label>
</div>
