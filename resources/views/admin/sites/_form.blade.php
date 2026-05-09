<div class="form-group">
    <label class="form-label" for="group_id">Група *</label>
    @php $selGroupId = old('group_id', $site?->group_id); @endphp
    <div class="cselect" id="cs-form-group" style="width:100%;">
        <button type="button" class="cselect__trigger" onclick="csToggle('cs-form-group')" style="width:100%;">
            <span class="cselect__label">{{ $groups->firstWhere('id', $selGroupId)?->name ?? '— Оберіть групу —' }}</span>
            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="cselect__menu" style="min-width:100%;">
            @foreach($groups as $group)
                <div class="cselect__option {{ (string)$selGroupId === (string)$group->id ? 'is-active' : '' }}"
                     onclick="csFormSelect('cs-form-group','{{ $group->id }}','{{ addslashes($group->name) }}')">{{ $group->name }}</div>
            @endforeach
        </div>
        <input type="hidden" name="group_id" id="group_id" value="{{ $selGroupId ?? '' }}">
    </div>
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
