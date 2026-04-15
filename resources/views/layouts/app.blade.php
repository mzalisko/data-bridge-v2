<!DOCTYPE html>
<html lang="uk" data-theme="{{ $_COOKIE['theme'] ?? 'dark' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DataBridge CRM')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
    @stack('styles')
</head>
<body>

<div class="shell">
    {{-- CRM Rail --}}
    <nav class="crm-rail">
        <div class="rail-logo" title="DataBridge CRM">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 2.1l4 4-4 4"/>
                <path d="M3 12.2v-2a4 4 0 0 1 4-4h13.8"/>
                <path d="M7 21.9l-4-4 4-4"/>
                <path d="M21 11.8v2a4 4 0 0 1-4 4H3.2"/>
            </svg>
            <span class="rail-logo__text">DataBridge</span>
        </div>

        <ul class="rail-nav">
            <li>
                <a href="{{ route('dashboard') }}"
                   class="rail-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                    </svg>
                    <span class="rail-label">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('site-groups.index') }}"
                   class="rail-item {{ request()->routeIs('site-groups.*') ? 'is-active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    <span class="rail-label">Групи</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sites.index') }}"
                   class="rail-item {{ request()->routeIs('sites.*') ? 'is-active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    <span class="rail-label">Сайти</span>
                </a>
            </li>
            <li>
                <a href="{{ route('users.index') }}"
                   class="rail-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="rail-label">Користувачі</span>
                </a>
            </li>
            <li>
                <a href="{{ route('logs.system') }}"
                   class="rail-item {{ request()->routeIs('logs.*') ? 'is-active' : '' }}">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <span class="rail-label">Логи</span>
                </a>
            </li>
        </ul>

        <div class="rail-bottom">
            <button class="rail-item" onclick="toggleTheme()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <span class="rail-label">Тема</span>
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rail-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span class="rail-label">Вийти</span>
                </button>
            </form>
        </div>
    </nav>

    {{-- Main body --}}
    <div class="shell-body">
        <header class="topbar">
            <div class="topbar__title">@yield('title', 'DataBridge CRM')</div>
            <div class="topbar__user">
                @php $n = auth()->user()->name ?? (auth()->user()->email ?? 'User'); @endphp
                <div class="topbar__user-avatar">
                    {{ mb_strtoupper(mb_substr($n, 0, 1, 'UTF-8'), 'UTF-8') }}
                    <span class="topbar__user-status"></span>
                </div>
                <span class="topbar__user-name">{{ $n }}</span>
            </div>
        </header>

        <main class="page-content">
            @yield('content')
        </main>
    </div>
</div>

<script src="{{ asset('assets/js/layout.js') }}"></script>
@stack('scripts')
</body>
</html>
