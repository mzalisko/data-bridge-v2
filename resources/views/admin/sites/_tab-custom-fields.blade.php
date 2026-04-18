{{-- Custom Fields tab — variables: $site, $customFields --}}
<div class="data-tab-header">
    <h2 class="data-tab__title">Кастомні поля <span class="data-tab__count">{{ $customFields->count() }}</span></h2>
    <button class="btn-primary" onclick="openDrawer('drawer-cf-create')">+ Додати</button>
</div>

@if(session('success') && request('tab') === 'custom_fields')
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($errors->any() && request('tab') === 'custom_fields')
    <div class="alert alert--error">
        @foreach($errors->all() as $err)<div>{{ $err }}</div>@endforeach
    </div>
@endif

@if($customFields->isEmpty())
    <div class="data-tab__empty">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
        <p>Кастомних полів ще немає</p>
    </div>
@else
    <ul class="data-list">
        @foreach($customFields as $cf)
        <li class="data-row">
            <div class="data-row__indicator">
                <span class="data-badge">{{ strtoupper($cf->field_type) }}</span>
            </div>
            <div class="data-row__main">
                <code style="font-size:13px">{{ $cf->field_key }}</code>
            </div>
            <div class="data-row__secondary">
                <span class="data-row__label" style="color:var(--text-muted);max-width:340px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:inline-block;vertical-align:middle;">
                    {{ \Illuminate\Support\Str::limit(strip_tags($cf->field_value ?? ''), 80) }}
                </span>
            </div>
            <div class="data-row__actions">
                <button class="btn-icon" title="Редагувати" onclick="openDrawer('drawer-cf-{{ $cf->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <form method="POST" action="{{ route('custom-fields.destroy', [$site, $cf]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon btn-icon--danger" title="Видалити"
                            onclick="return confirm('Видалити кастомне поле «{{ $cf->field_key }}»?')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </form>
            </div>
        </li>
        @endforeach
    </ul>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-cf-create-overlay" onclick="closeDrawer('drawer-cf-create')"></div>
<div class="drawer" id="drawer-cf-create">
    <div class="drawer__header">
        <span class="drawer__title">Нове кастомне поле</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-cf-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('custom-fields.store', $site) }}" class="form-stack" id="form-cf-create">
            @csrf
            <div class="form-group">
                <label class="form-label">Ключ <span class="form-hint">(snake_case, напр. working_hours)</span></label>
                <input type="text" name="field_key" class="form-input"
                       value="{{ old('field_key') }}"
                       placeholder="working_hours" maxlength="64" required
                       pattern="^[a-z][a-z0-9_]*$"
                       style="font-family:monospace">
            </div>
            <div class="form-group">
                <label class="form-label">Тип</label>
                <select name="field_type" class="form-input form-select">
                    <option value="text">Text</option>
                    <option value="number">Number</option>
                    <option value="url">URL</option>
                    <option value="email">Email</option>
                    <option value="json">JSON</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Значення</label>
                <textarea name="field_value" class="form-input" rows="5"
                          placeholder="Пн–Пт 9:00–18:00">{{ old('field_value') }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Порядок</label>
                <input type="number" name="sort_order" class="form-input" value="0" min="0" style="max-width:120px">
            </div>
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-cf-create')">Скасувати</button>
        <button type="submit" form="form-cf-create" class="btn-primary">Додати</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($customFields as $cf)
<div class="drawer-overlay" id="drawer-cf-{{ $cf->id }}-overlay" onclick="closeDrawer('drawer-cf-{{ $cf->id }}')"></div>
<div class="drawer" id="drawer-cf-{{ $cf->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $cf->field_key }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-cf-{{ $cf->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <div class="drawer-id-chip">
            <span style="color:var(--text-muted)">#{{ $cf->id }}</span>
            <span style="color:var(--border-color)">·</span>
            <span>{{ strtoupper($cf->field_type) }}</span>
        </div>
        <form method="POST" action="{{ route('custom-fields.update', [$site, $cf]) }}" class="form-stack" id="form-cf-{{ $cf->id }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Ключ</label>
                <input type="text" name="field_key" class="form-input"
                       value="{{ old('field_key', $cf->field_key) }}"
                       maxlength="64" required
                       pattern="^[a-z][a-z0-9_]*$"
                       style="font-family:monospace">
            </div>
            <div class="form-group">
                <label class="form-label">Тип</label>
                <select name="field_type" class="form-input form-select">
                    @foreach(['text','number','url','email','json'] as $t)
                        <option value="{{ $t }}" {{ $cf->field_type === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Значення</label>
                <textarea name="field_value" class="form-input" rows="6">{{ old('field_value', $cf->field_value) }}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Порядок</label>
                <input type="number" name="sort_order" class="form-input"
                       value="{{ $cf->sort_order }}" min="0" style="max-width:120px">
            </div>
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-cf-{{ $cf->id }}')">Скасувати</button>
        <button type="submit" form="form-cf-{{ $cf->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach
