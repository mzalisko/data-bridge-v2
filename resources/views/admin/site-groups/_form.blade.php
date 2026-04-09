<div class="form-group">
    <label class="form-label" for="name">Назва *</label>
    <input type="text"
           id="name"
           name="name"
           class="form-input @error('name') form-input--error @enderror"
           value="{{ old('name', $group?->name) }}"
           required>
    @error('name')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-group">
    <label class="form-label" for="description">Опис</label>
    <textarea id="description"
              name="description"
              class="form-input @error('description') form-input--error @enderror"
              rows="3">{{ old('description', $group?->description) }}</textarea>
    @error('description')
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>

<div class="form-row">
    <div class="form-group form-group--flex">
        <label class="form-label" for="color">Колір</label>
        <div class="color-row">
            <input type="color"
                   id="color"
                   name="color"
                   class="form-color"
                   value="{{ old('color', $group?->color ?? '#706f70') }}">
            <span class="form-color-value" id="color-value">{{ old('color', $group?->color ?? '#706f70') }}</span>
        </div>
    </div>

    <div class="form-group form-group--flex">
        <label class="form-label" for="icon">Іконка</label>
        <input type="text"
               id="icon"
               name="icon"
               class="form-input @error('icon') form-input--error @enderror"
               value="{{ old('icon', $group?->icon) }}"
               placeholder="🏢"
               maxlength="32">
    </div>
</div>

<script>
(function() {
    var colorInput = document.getElementById('color');
    var colorValue = document.getElementById('color-value');
    if (colorInput && colorValue) {
        colorInput.addEventListener('input', function() {
            colorValue.textContent = this.value;
        });
    }
})();
</script>
