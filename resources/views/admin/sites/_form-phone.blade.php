@php $p = $phone ?? null; $pid = $p?->id ?? 'new'; @endphp

<input type="hidden" name="sort_order" value="{{ old('sort_order', $p?->sort_order ?? 0) }}">

<div class="field">
    <label class="field__label" for="ph-label-{{ $pid }}">Label (optional)</label>
    <input type="text" id="ph-label-{{ $pid }}" name="label" class="field__input"
           value="{{ old('label', $p?->label) }}" placeholder="Main reception, Sales, …">
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
    <div class="field">
        <label class="field__label" for="ph-iso-{{ $pid }}">Country</label>
        <select id="ph-iso-{{ $pid }}" name="country_iso" class="field__input" required onchange="(function(sel){
            var opt = sel.options[sel.selectedIndex];
            var dial = opt.getAttribute('data-dial');
            var dialInput = document.getElementById('ph-dial-{{ $pid }}');
            if (dial && !dialInput.value) dialInput.value = dial;
        })(this)">
            <option value="">—</option>
            @foreach($countries as $c)
                <option value="{{ $c->iso }}" data-dial="{{ $c->dial_code }}"
                    {{ old('country_iso', $p?->country_iso) === $c->iso ? 'selected' : '' }}>
                    {{ $c->iso }} {{ ($c->name && strcasecmp($c->name, $c->iso) !== 0) ? '— '.$c->name : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label class="field__label" for="ph-dial-{{ $pid }}">Dial code</label>
        <input type="text" id="ph-dial-{{ $pid }}" name="dial_code" class="field__input" required
               placeholder="380" value="{{ old('dial_code', $p?->dial_code) }}">
    </div>
</div>

<div class="field">
    <label class="field__label" for="ph-num-{{ $pid }}">Phone number</label>
    <input type="text" id="ph-num-{{ $pid }}" name="number" class="field__input" required
           placeholder="50 123 4567" value="{{ old('number', $p?->number) }}">
</div>

<div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
    <input type="hidden" name="is_primary" value="0">
    <input id="ph-prim-{{ $pid }}" type="checkbox" name="is_primary" value="1"
           {{ old('is_primary', $p?->is_primary ?? false) ? 'checked' : '' }}
           style="accent-color:var(--accent);width:16px;height:16px;">
    <label for="ph-prim-{{ $pid }}" style="font-size:13px;color:var(--text-2);cursor:pointer;">Primary phone</label>
</div>
