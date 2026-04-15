<div class="form-group">
    <label class="form-label" for="group_id">Група *</label>
    <select id="group_id" name="group_id"
            class="form-input form-select @error('group_id') form-input--error @enderror"
            required>
        <option value="">— Оберіть групу —</option>
        @foreach($groups as $group)
            <option value="{{ $group->id }}"
                {{ old('group_id', $site?->group_id) == $group->id ? 'selected' : '' }}>
                {{ $group->name }}
            </option>
        @endforeach
    </select>
    @error('group_id')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label" for="name">Назва *</label>
    <input type="text"
           id="name"
           name="name"
           class="form-input @error('name') form-input--error @enderror"
           value="{{ old('name', $site?->name) }}"
           required>
    @error('name')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label" for="url">URL *</label>
    <input type="url"
           id="url"
           name="url"
           class="form-input @error('url') form-input--error @enderror"
           value="{{ old('url', $site?->url) }}"
           placeholder="https://example.com"
           required>
    @error('url')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label" for="description">Опис</label>
    <textarea id="description"
              name="description"
              class="form-input @error('description') form-input--error @enderror"
              rows="3">{{ old('description', $site?->description) }}</textarea>
    @error('description')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-check">
    <input type="hidden" name="is_active" value="0">
    <input id="is_active"
           type="checkbox"
           name="is_active"
           class="form-checkbox"
           value="1"
           {{ old('is_active', $site?->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Активний</label>
</div>

{{-- Countries --}}
<details class="form-details">
    <summary class="form-details__summary">
        Країни сайту
        @php
            $parsedCountries = collect(array_filter(array_map('trim', explode("\n", old('site_countries', $site?->site_countries ?? '')))));
        @endphp
        @if($parsedCountries->isNotEmpty())
            <span class="site-countries-count">{{ $parsedCountries->count() }}</span>
        @endif
    </summary>
    <div class="form-details__body">
        <p class="form-hint" style="margin-bottom:8px;">
            Кожен рядок: <code>ISO,dialcode</code> — наприклад <code>UA,380</code>
        </p>
        @if($parsedCountries->isNotEmpty())
        <div class="site-countries-chips">
            @foreach($parsedCountries as $entry)
                @php $parts = array_map('trim', explode(',', $entry)); @endphp
                @if(count($parts) >= 2)
                <span class="site-country-chip">
                    <strong>{{ strtoupper($parts[0]) }}</strong>
                    <span>+{{ $parts[1] }}</span>
                </span>
                @endif
            @endforeach
        </div>
        @endif
        <textarea name="site_countries"
                  class="form-input site-countries-textarea"
                  rows="5"
                  placeholder="UA,380&#10;PL,48&#10;DE,49&#10;GB,44">{{ old('site_countries', $site?->site_countries) }}</textarea>
    </div>
</details>
