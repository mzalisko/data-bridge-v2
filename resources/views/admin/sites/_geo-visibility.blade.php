{{--
  Geo visibility section — include inside a form.
  Variables: $geoPrefix (string, unique per drawer), $geoModel (model or null)
  $countries is inherited from parent scope (passed from SiteController@show)
--}}
@php
    $geoMode      = old('geo_mode',      isset($geoModel) ? $geoModel->geo_mode      : null);
    $geoCountries = old('geo_countries', isset($geoModel) ? $geoModel->geo_countries  : '');
    $selectedIsos = collect(array_filter(array_map('trim', explode(',', $geoCountries))))
                        ->map(fn($v) => strtoupper($v))
                        ->toArray();
    $showCountries = in_array($geoMode, ['include', 'exclude']);
    $geoList = $countries ?? collect();
@endphp
<div class="geo-section">
    <div class="geo-section__label">Геозалежність</div>
    <div class="geo-mode-grid" id="geo-grid-{{ $geoPrefix }}">

        <label class="geo-option {{ ($geoMode === null || $geoMode === '') ? 'is-active' : '' }}">
            <input type="radio" name="geo_mode" value=""
                   {{ ($geoMode === null || $geoMode === '') ? 'checked' : '' }}
                   onclick="geoToggle('{{ $geoPrefix }}', '')">
            <span class="geo-option__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <span class="geo-option__body">
                <span class="geo-option__label">Прихований</span>
                <span class="geo-option__hint">Нікому</span>
            </span>
        </label>

        <label class="geo-option {{ $geoMode === 'all' ? 'is-active' : '' }}">
            <input type="radio" name="geo_mode" value="all"
                   {{ $geoMode === 'all' ? 'checked' : '' }}
                   onclick="geoToggle('{{ $geoPrefix }}', 'all')">
            <span class="geo-option__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </span>
            <span class="geo-option__body">
                <span class="geo-option__label">Всі регіони</span>
                <span class="geo-option__hint">Всім</span>
            </span>
        </label>

        <label class="geo-option {{ $geoMode === 'include' ? 'is-active' : '' }}">
            <input type="radio" name="geo_mode" value="include"
                   {{ $geoMode === 'include' ? 'checked' : '' }}
                   onclick="geoToggle('{{ $geoPrefix }}', 'include')">
            <span class="geo-option__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </span>
            <span class="geo-option__body">
                <span class="geo-option__label">Тільки для</span>
                <span class="geo-option__hint">Вибрані</span>
            </span>
        </label>

        <label class="geo-option {{ $geoMode === 'exclude' ? 'is-active' : '' }}">
            <input type="radio" name="geo_mode" value="exclude"
                   {{ $geoMode === 'exclude' ? 'checked' : '' }}
                   onclick="geoToggle('{{ $geoPrefix }}', 'exclude')">
            <span class="geo-option__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </span>
            <span class="geo-option__body">
                <span class="geo-option__label">Всі, крім</span>
                <span class="geo-option__hint">Виключити</span>
            </span>
        </label>

    </div>

    {{-- Country chips — shown only for include/exclude --}}
    <div class="geo-countries-wrap" id="geo-countries-{{ $geoPrefix }}"
         style="{{ $showCountries ? '' : 'display:none' }}">
        @if($geoList->isNotEmpty())
            <div class="geo-country-chips" id="geo-chips-{{ $geoPrefix }}">
                @foreach($geoList as $c)
                <label class="geo-country-tag {{ in_array($c->iso, $selectedIsos) ? 'is-selected' : '' }}"
                       title="{{ $c->name ?? $c->iso }}">
                    <input type="checkbox"
                           class="geo-chip-cb"
                           value="{{ $c->iso }}"
                           {{ in_array($c->iso, $selectedIsos) ? 'checked' : '' }}
                           onchange="geoUpdateCountries('{{ $geoPrefix }}')">
                    {{ $c->iso }}
                </label>
                @endforeach
            </div>
        @else
            <p class="form-hint" style="margin:6px 0;">
                Немає країн у <a href="{{ route('settings.index') }}" target="_blank">Налаштуваннях</a>
            </p>
        @endif
        <input type="hidden" name="geo_countries"
               id="geo-countries-input-{{ $geoPrefix }}"
               value="{{ $geoCountries }}">
    </div>
</div>
