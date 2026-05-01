@php $g = $group ?? null; $gid = $g?->id ?? 'new'; $current = old('color', $g?->color ?? '#5b5bf5'); @endphp

<div class="field">
    <label class="field__label" for="g-name-{{ $gid }}">Group name</label>
    <input type="text" id="g-name-{{ $gid }}" name="name" class="field__input" required
           value="{{ old('name', $g?->name) }}" placeholder="Agency: Beacon">
    @error('name')<span style="font-size:12px;color:var(--danger);">{{ $message }}</span>@enderror
</div>

<div class="field">
    <label class="field__label" for="g-desc-{{ $gid }}">Description</label>
    <textarea id="g-desc-{{ $gid }}" name="description" class="field__input" rows="3"
              style="resize:vertical;font-family:inherit;">{{ old('description', $g?->description) }}</textarea>
    @error('description')<span style="font-size:12px;color:var(--danger);">{{ $message }}</span>@enderror
</div>

<div class="field">
    <label class="field__label">Accent color</label>
    <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;" id="g-palette-{{ $gid }}">
        @foreach(['#5b5bf5','#0ea5e9','#10b981','#f59e0b','#e11d48','#a855f7','#0891b2','#84cc16','#ec4899','#71717a'] as $c)
            <button type="button" data-color="{{ $c }}" data-gid="{{ $gid }}"
                    style="width:28px;height:28px;border-radius:8px;background:{{ $c }};border:2px solid {{ $current === $c ? 'var(--accent)' : 'transparent' }};box-sizing:border-box;cursor:pointer;padding:0;"
                    onclick="(function(btn){
                        var gid = btn.dataset.gid;
                        document.getElementById('g-color-input-'+gid).value = btn.dataset.color;
                        document.getElementById('g-color-preview-'+gid).style.background = btn.dataset.color;
                        document.getElementById('g-color-text-'+gid).textContent = btn.dataset.color;
                        document.querySelectorAll('#g-palette-'+gid+' button').forEach(function(b){ b.style.borderColor='transparent'; });
                        btn.style.borderColor='var(--accent)';
                    })(this)"></button>
        @endforeach
        <input type="color" id="g-color-input-{{ $gid }}" name="color" value="{{ $current }}"
               style="width:28px;height:28px;border-radius:8px;border:1px solid var(--border);cursor:pointer;padding:0;background:transparent;"
               onchange="document.getElementById('g-color-preview-{{ $gid }}').style.background=this.value;document.getElementById('g-color-text-{{ $gid }}').textContent=this.value;document.querySelectorAll('#g-palette-{{ $gid }} button').forEach(function(b){ b.style.borderColor='transparent'; });">
        <span style="display:inline-flex;align-items:center;gap:8px;margin-left:4px;font-size:12px;color:var(--text-3);font-family:var(--font-mono);">
            <span id="g-color-preview-{{ $gid }}" style="width:14px;height:14px;border-radius:3px;background:{{ $current }};border:1px solid var(--border);"></span>
            <span id="g-color-text-{{ $gid }}">{{ $current }}</span>
        </span>
    </div>
</div>

<div class="field">
    <label class="field__label" for="g-icon-{{ $gid }}">Icon (emoji, optional)</label>
    <input type="text" id="g-icon-{{ $gid }}" name="icon" class="field__input"
           value="{{ old('icon', $g?->icon) }}" placeholder="🏢" maxlength="8">
</div>
