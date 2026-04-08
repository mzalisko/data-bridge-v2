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
    <label class="form-label" for="password">
        Пароль {{ $user ? '(залишити порожнім — без змін)' : '*' }}
    </label>
    <input type="password"
           id="password"
           name="password"
           class="form-input @error('password') form-input--error @enderror"
           {{ $user ? '' : 'required' }}
           placeholder="мін. 8 символів">
    @error('password')
        <span class="form-error">{{ $message }}</span>
    @enderror
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
