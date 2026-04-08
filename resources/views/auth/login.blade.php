@extends('layouts.auth')

@section('title', 'Вхід — DataBridge CRM')

@section('content')
<div class="form-card">
    <div class="form-card__header">
        <div class="form-card__logo">
            <span class="form-card__logo-icon">⬡</span>
        </div>
        <h1 class="form-card__title">DataBridge</h1>
        <p class="form-card__subtitle">CRM для мережі сайтів</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="form-stack">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input
                id="email"
                type="email"
                name="email"
                class="form-input @error('email') form-input--error @enderror"
                value="{{ old('email') }}"
                autocomplete="email"
                autofocus
                placeholder="admin@databridge.local"
            >
            @error('email')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Пароль</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-input @error('password') form-input--error @enderror"
                autocomplete="current-password"
                placeholder="••••••••"
            >
            @error('password')
                <span class="form-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-check">
            <input id="remember" type="checkbox" name="remember" class="form-checkbox">
            <label for="remember" class="form-check-label">Запам'ятати мене</label>
        </div>

        <button type="submit" class="btn-primary btn-primary--full">
            Увійти
        </button>
    </form>
</div>
@endsection
