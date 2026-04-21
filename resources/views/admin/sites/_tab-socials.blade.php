{{-- Socials tab — variables: $site, $socials --}}
@php
$platformLabels = [
    'instagram' => 'Instagram', 'facebook' => 'Facebook', 'telegram' => 'Telegram',
    'youtube' => 'YouTube', 'tiktok' => 'TikTok', 'linkedin' => 'LinkedIn',
    'twitter' => 'Twitter / X', 'viber' => 'Viber', 'whatsapp' => 'WhatsApp', 'other' => 'Інше',
];
@endphp

<div class="data-tab-header">
    <h2 class="data-tab__title">Соцмережі <span class="data-tab__count">{{ $socials->count() }}</span></h2>
    <button class="btn-primary" onclick="openDrawer('drawer-social-create')">+ Додати</button>
</div>

@if(session('success') && request('tab') === 'socials')
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($socials->isEmpty())
    <div class="data-tab__empty">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        <p>Соцмереж ще немає</p>
    </div>
@else
    <ul class="data-list">
        @foreach($socials as $social)
        <li class="data-row">
            {{-- Col 1: platform badge --}}
            <div class="data-row__indicator">
                <span class="data-badge data-badge--platform">{{ $platformLabels[$social->platform] ?? $social->platform }}</span>
            </div>
            {{-- Col 2: handle --}}
            <div class="data-row__main">
                <span class="data-row__val">{{ $social->handle ?: $social->url }}</span>
                <span class="data-row__id">#{{ $social->id }}</span>
            </div>
            {{-- Col 3: url link + geo --}}
            <div class="data-row__secondary">
                <a href="{{ $social->url }}" target="_blank" class="data-row__link" title="Відкрити">↗ посилання</a>
                @if($social->geo_mode === null || $social->geo_mode === '')
                    <span class="geo-badge geo-badge--hidden geo-badge--sm">Прих.</span>
                @elseif($social->geo_mode === 'all')
                    <span class="geo-badge geo-badge--all geo-badge--sm">Всі</span>
                @elseif($social->geo_mode === 'include')
                    <span class="geo-badge geo-badge--include geo-badge--sm">{{ $social->geo_countries ?: '…' }}</span>
                @elseif($social->geo_mode === 'exclude')
                    <span class="geo-badge geo-badge--exclude geo-badge--sm">≠ {{ $social->geo_countries ?: '…' }}</span>
                @endif
            </div>
            <div class="data-row__actions">
                <button class="btn-icon" title="Редагувати" onclick="openDrawer('drawer-social-{{ $social->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <form method="POST" action="{{ route('socials.destroy', [$site, $social]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon btn-icon--danger" title="Видалити"
                            onclick="return confirm('Видалити?')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </form>
            </div>
        </li>
        @endforeach
    </ul>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-social-create-overlay" onclick="closeDrawer('drawer-social-create')"></div>
<div class="drawer" id="drawer-social-create">
    <div class="drawer__header">
        <span class="drawer__title">Нова соцмережа</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-social-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('socials.store', $site) }}" class="form-stack" id="form-social-create">
            @csrf
            <div class="form-group">
                <label class="form-label">Платформа</label>
                <select name="platform" class="form-input form-select" required>
                    @foreach($platformLabels as $val => $lbl)
                    <option value="{{ $val }}">{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Хендл / нікнейм</label>
                <input type="text" name="handle" class="form-input" placeholder="@mycompany" maxlength="255" required>
            </div>
            <div class="form-group">
                <label class="form-label">URL</label>
                <input type="url" name="url" class="form-input" placeholder="https://instagram.com/mycompany" maxlength="512" required>
            </div>
            @include('admin.sites._geo-visibility', ['geoPrefix' => 'social-create', 'geoModel' => null])
            <input type="hidden" name="sort_order" value="0">
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-social-create')">Скасувати</button>
        <button type="submit" form="form-social-create" class="btn-primary">Додати</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($socials as $social)
<div class="drawer-overlay" id="drawer-social-{{ $social->id }}-overlay" onclick="closeDrawer('drawer-social-{{ $social->id }}')"></div>
<div class="drawer" id="drawer-social-{{ $social->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $platformLabels[$social->platform] ?? $social->platform }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-social-{{ $social->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <div class="drawer-id-chip">
            <span style="color:var(--text-muted)">#{{ $social->id }}</span>
            <span style="color:var(--border-color)">·</span>
            <span>{{ $social->handle }}</span>
        </div>
        <form method="POST" action="{{ route('socials.update', [$site, $social]) }}" class="form-stack" id="form-social-{{ $social->id }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Платформа</label>
                <select name="platform" class="form-input form-select">
                    @foreach($platformLabels as $val => $lbl)
                    <option value="{{ $val }}" {{ old('platform', $social->platform) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Хендл</label>
                <input type="text" name="handle" class="form-input" value="{{ old('handle', $social->handle) }}" maxlength="255" required>
            </div>
            <div class="form-group">
                <label class="form-label">URL</label>
                <input type="url" name="url" class="form-input" value="{{ old('url', $social->url) }}" maxlength="512" required>
            </div>
            @include('admin.sites._geo-visibility', ['geoPrefix' => 'social-' . $social->id, 'geoModel' => $social])
            <input type="hidden" name="sort_order" value="{{ $social->sort_order }}">
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-social-{{ $social->id }}')">Скасувати</button>
        <button type="submit" form="form-social-{{ $social->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach
