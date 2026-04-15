@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/settings.css') }}?v={{ filemtime(public_path('assets/css/pages/settings.css')) }}">
@endpush

@section('title', 'Налаштування')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Налаштування CRM</h1>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

<div class="settings-layout">

    {{-- Sidebar nav --}}
    <nav class="settings-nav">
        <a href="#countries" class="settings-nav__item is-active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            Коди країн
        </a>
        {{-- Future sections:
        <a href="#integrations" class="settings-nav__item">
            <svg .../>
            Інтеграції
        </a>
        --}}
    </nav>

    {{-- Content --}}
    <div class="settings-content">

        {{-- Section: Countries --}}
        <section class="settings-section" id="countries">
            <div class="settings-section__header">
                <div>
                    <h2 class="settings-section__title">Коди країн</h2>
                    <p class="settings-section__desc">Глобальний список ISO + телефонних кодів. Використовується для автозаповнення телефонів та вибору геозалежності.</p>
                </div>
                <span class="settings-section__count">{{ $countries->count() }}</span>
            </div>

            {{-- Add form --}}
            <form method="POST" action="{{ route('settings.countries.store') }}" class="settings-add-form">
                @csrf
                <div class="settings-add-row">
                    <div class="form-group">
                        <label class="form-label">ISO <span class="form-hint">2 літери</span></label>
                        <input type="text" name="iso"
                               class="form-input settings-add-iso @error('iso') form-input--error @enderror"
                               value="{{ old('iso') }}"
                               placeholder="UA" maxlength="2"
                               style="text-transform:uppercase"
                               autocomplete="off">
                        @error('iso')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Код <span class="form-hint">без +</span></label>
                        <input type="text" name="dial_code"
                               class="form-input settings-add-dial @error('dial_code') form-input--error @enderror"
                               value="{{ old('dial_code') }}"
                               placeholder="380" maxlength="8"
                               autocomplete="off">
                        @error('dial_code')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group settings-add-name-group">
                        <label class="form-label">Назва <span class="form-hint">(необов'язково)</span></label>
                        <input type="text" name="name"
                               class="form-input"
                               value="{{ old('name') }}"
                               placeholder="Україна" maxlength="100"
                               autocomplete="off">
                    </div>
                    <div class="settings-add-btn-wrap">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn-primary">+ Додати</button>
                    </div>
                </div>
            </form>

            {{-- List --}}
            @if($countries->isEmpty())
                <div class="data-tab__empty" style="padding:var(--space-xl) 0;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    <p>Список порожній. Запустіть seeder або додайте вручну.</p>
                </div>
            @else
                <ul class="countries-list">
                    @foreach($countries as $country)
                    <li class="countries-row">
                        <span class="country-iso">{{ $country->iso }}</span>
                        <span class="country-dial">+{{ $country->dial_code }}</span>
                        <span class="country-name">{{ $country->name ?: '—' }}</span>
                        <form method="POST" action="{{ route('settings.countries.destroy', $country) }}" class="countries-row__del">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon btn-icon--danger" title="Видалити"
                                    onclick="return confirm('Видалити {{ $country->iso }}?')">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            @endif
        </section>

    </div>{{-- /settings-content --}}

</div>

@endsection
