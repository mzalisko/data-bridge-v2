@php $s = $social ?? null; $sid = $s?->id ?? 'new'; @endphp

<input type="hidden" name="sort_order" value="{{ old('sort_order', $s?->sort_order ?? 0) }}">

<div class="field">
    <label class="field__label" for="so-plat-{{ $sid }}">Платформа</label>
    <select id="so-plat-{{ $sid }}" name="platform" class="field__input" required>
        @foreach(['instagram'=>'Instagram','facebook'=>'Facebook','telegram'=>'Telegram','linkedin'=>'LinkedIn','x'=>'X / Twitter','whatsapp'=>'WhatsApp','viber'=>'Viber','youtube'=>'YouTube'] as $val => $label)
            <option value="{{ $val }}" {{ old('platform', $s?->platform) === $val ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label class="field__label" for="so-handle-{{ $sid }}">Нікнейм</label>
    <input type="text" id="so-handle-{{ $sid }}" name="handle" class="field__input" required
           placeholder="@нікнейм або /сторінка" value="{{ old('handle', $s?->handle) }}">
</div>

<div class="field">
    <label class="field__label" for="so-url-{{ $sid }}">Повний URL</label>
    <input type="url" id="so-url-{{ $sid }}" name="url" class="field__input" required
           placeholder="https://instagram.com/username" value="{{ old('url', $s?->url) }}">
</div>
