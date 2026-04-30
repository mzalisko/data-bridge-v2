@extends('layouts.auth')

@section('title', 'Sign in — DataBridge CRM')

@section('content')
<div class="auth-card">
    <div class="auth-card__head">
        <div class="auth-card__logo">
            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 7h7a5 5 0 0 1 5 5v0a5 5 0 0 1-5 5H4"/>
                <path d="M20 17h-7a5 5 0 0 1-5-5v0a5 5 0 0 1 5-5h7"/>
            </svg>
        </div>
        <h1 class="auth-card__title">DataBridge</h1>
        <p class="auth-card__sub">Sign in to your workspace</p>
    </div>

    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label class="field__label" for="email">Email</label>
            <input id="email" type="email" name="email" class="field__input"
                   value="{{ old('email') }}" autocomplete="email" autofocus
                   placeholder="admin@databridge.local">
            @error('email')<span style="font-size:12px;color:var(--danger);">{{ $message }}</span>@enderror
        </div>

        <div class="field">
            <label class="field__label" for="password">Password</label>
            <input id="password" type="password" name="password" class="field__input"
                   autocomplete="current-password" placeholder="••••••••">
            @error('password')<span style="font-size:12px;color:var(--danger);">{{ $message }}</span>@enderror
        </div>

        <div style="display:flex;align-items:center;gap:8px;margin-bottom:18px;">
            <input id="remember" type="checkbox" name="remember" style="accent-color:var(--accent);">
            <label for="remember" style="font-size:13px;color:var(--text-2);cursor:pointer;">Remember me</label>
        </div>

        <button type="submit" class="btn btn--primary btn--lg" style="width:100%;">Sign in</button>
    </form>
</div>
@endsection
