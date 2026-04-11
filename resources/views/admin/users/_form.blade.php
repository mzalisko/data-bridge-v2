<div class="form-group">
    <label class="form-label" for="name">Ім'я *</label>
    <input type="text"
           id="name"
           name="name"
           class="form-input @error('name') form-input--error @enderror"
           value="{{ old('name', $user?->name) }}"
           required>
    @error('name')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label" for="email">Email *</label>
    <input type="email"
           id="email"
           name="email"
           class="form-input @error('email') form-input--error @enderror"
           value="{{ old('email', $user?->email) }}"
           required>
    @error('email')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label" for="password{{ $user ? '-'.$user->id : '-new' }}">
        Пароль {{ $user ? '(залишити порожнім — без змін)' : '*' }}
    </label>
    <div class="input-group @error('password') input-group--error @enderror">
        <input type="password"
               id="password{{ $user ? '-'.$user->id : '-new' }}"
               name="password"
               class="form-input"
               {{ $user ? '' : 'required' }}
               autocomplete="new-password"
               placeholder="мін. 8 символів">
        <div class="input-group__actions">
            <button type="button"
                    class="input-group__btn"
                    title="Показати пароль"
                    onclick="togglePasswordVisibility('password{{ $user ? '-'.$user->id : '-new' }}', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
            <button type="button"
                    class="input-group__btn"
                    title="Копіювати пароль"
                    onclick="copyPassword('password{{ $user ? '-'.$user->id : '-new' }}', this)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
            </button>
            <button type="button"
                    class="input-group__btn"
                    title="Згенерувати пароль"
                    onclick="generatePassword('password{{ $user ? '-'.$user->id : '-new' }}')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
            </button>
        </div>
    </div>
    @error('password')
        <span class="form-error">{{ $message }}</span>
    @enderror
    @if(!$user)
        <span class="form-hint">Натисніть ↻ для авто-генерації пароля</span>
    @endif
</div>

<div class="form-group">
    <label class="form-label" for="role">Роль *</label>
    <select id="role" name="role"
            class="form-input form-select @error('role') form-input--error @enderror"
            required>
        @foreach(['admin' => 'Admin', 'manager' => 'Manager', 'editor' => 'Editor', 'viewer' => 'Viewer'] as $value => $label)
            <option value="{{ $value }}" {{ old('role', $user?->role) === $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('role')
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
           {{ old('is_active', $user?->is_active ?? true) ? 'checked' : '' }}>
    <label for="is_active" class="form-check-label">Активний</label>
</div>
