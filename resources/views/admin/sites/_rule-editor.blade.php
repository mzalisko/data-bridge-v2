{{--
  Geo visibility rule editor.
  Props:
    $rePrefix      — unique HTML ID prefix (e.g. 'ph-new', 'ph-42', 'pr-new')
    $reMode        — current geo_mode: 'all'|'include'|'exclude'
    $reCountries   — current geo_countries (array of ISO codes)
    $reOptions     — array of ISO codes to show as chips (site's active_geos)
--}}
@php
    $reMode      = $reMode      ?? 'all';
    $reCountries = (array) ($reCountries ?? []);
    $reOptions   = $reOptions   ?? [];
@endphp

<div class="rule-editor" style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border-2);">
    <div style="font-size:11px;color:var(--text-3);font-weight:500;text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">Правило видимості</div>

    {{-- Mode pills --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap;" id="{{ $rePrefix }}-modes">
        @foreach(['all' => 'Всім', 'include' => 'Тільки для', 'exclude' => 'Всім крім'] as $mVal => $mLabel)
            <label style="display:inline-flex;align-items:center;padding:4px 12px;border:1px solid var(--border);border-radius:99px;cursor:pointer;font-size:12px;transition:.12s;
                          {{ $reMode === $mVal ? 'background:var(--accent);color:#fff;border-color:var(--accent);font-weight:600;' : 'background:var(--panel-2);color:var(--text-2);' }}"
                  id="{{ $rePrefix }}-mode-lbl-{{ $mVal }}">
                <input type="radio" name="geo_mode" value="{{ $mVal }}"
                       {{ $reMode === $mVal ? 'checked' : '' }}
                       style="display:none;"
                       onchange="ruleEditorToggle('{{ $rePrefix }}','{{ $mVal }}')">
                {{ $mLabel }}
            </label>
        @endforeach
    </div>

    {{-- Country chips (visible for include/exclude) --}}
    @if(count($reOptions) > 0)
        <div id="{{ $rePrefix }}-countries"
             style="{{ in_array($reMode, ['include','exclude']) ? '' : 'display:none;' }}margin-top:10px;">
            <div style="font-size:11px;color:var(--text-3);margin-bottom:6px;">Країни</div>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                @foreach($reOptions as $iso)
                    <label id="{{ $rePrefix }}-chip-{{ $iso }}"
                           style="display:inline-flex;align-items:center;padding:3px 10px;border:1px solid var(--border);border-radius:99px;cursor:pointer;font-size:11px;font-family:var(--font-mono);font-weight:600;transition:.12s;
                                  {{ in_array($iso, $reCountries) ? 'background:var(--accent-2);color:var(--accent-text);border-color:var(--accent-2);' : 'background:var(--panel-2);color:var(--text-2);' }}">
                        <input type="checkbox" name="geo_countries[]" value="{{ $iso }}"
                               {{ in_array($iso, $reCountries) ? 'checked' : '' }}
                               style="display:none;"
                               onchange="ruleChipToggle('{{ $rePrefix }}','{{ $iso }}',this)">
                        {{ $iso }}
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>
