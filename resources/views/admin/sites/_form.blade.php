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
    <label class="form-label" for="plugin_webhook_url">URL для авто-синхронізації <span style="font-weight:400;color:var(--text-muted)">(опціонально)</span></label>
    <input type="url"
           id="plugin_webhook_url"
           name="plugin_webhook_url"
           class="form-input @error('plugin_webhook_url') form-input--error @enderror"
           value="{{ old('plugin_webhook_url', $site?->plugin_webhook_url) }}"
           placeholder="http://wp1  (залиш порожнім якщо URL вище досяжний з CRM)">
    <span class="form-hint">Заповни лише якщо сайт недоступний за основним URL (напр. Docker dev: <code>http://wp1</code>). CRM пінгує цей URL після кожної зміни даних.</span>
    @error('plugin_webhook_url')
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
