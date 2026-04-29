<!DOCTYPE html>
<html lang="uk" class="vibeB vibe" {{ request()->cookie('theme') === 'dark' ? 'data-theme="dark"' : '' }}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DataBridge CRM')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
    @stack('styles')
</head>
<body>

<div class="shell">
    {{-- Sidebar --}}
    <nav class="sidebar">
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <span class="sidebar-logo__icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7h7a5 5 0 0 1 5 5v0a5 5 0 0 1-5 5H4"/>
                    <path d="M20 17h-7a5 5 0 0 1-5-5v0a5 5 0 0 1 5-5h7"/>
                </svg>
            </span>
            <div>
                <div class="sidebar-logo__name">DataBridge</div>
                <div class="sidebar-logo__sub">CRM · workspace</div>
            </div>
        </a>

        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}"
                   class="sidebar-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('site-groups.index') }}"
                   class="sidebar-item {{ request()->routeIs('site-groups.*') ? 'is-active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                        <polyline points="2 17 12 22 22 17"/>
                        <polyline points="2 12 12 17 22 12"/>
                    </svg>
                    Групи
                </a>
            </li>
            <li>
                <a href="{{ route('sites.index') }}"
                   class="sidebar-item {{ request()->routeIs('sites.*') ? 'is-active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    Сайти
                </a>
            </li>
            <li>
                <a href="{{ route('data.index') }}"
                   class="sidebar-item {{ request()->routeIs('data.*') ? 'is-active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                    </svg>
                    Дані
                </a>
            </li>
            <li>
                <a href="{{ route('users.index') }}"
                   class="sidebar-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    Користувачі
                </a>
            </li>
            <li>
                <a href="{{ route('logs.system') }}"
                   class="sidebar-item {{ request()->routeIs('logs.*') ? 'is-active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                    Логи
                </a>
            </li>
        </ul>

        <div class="sidebar-bottom">
            <button class="sidebar-item" onclick="toggleTheme()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                Тема
            </button>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Вийти
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

<script src="{{ asset('assets/js/layout.js') }}?v={{ filemtime(public_path('assets/js/layout.js')) }}"></script>
<script src="{{ asset('assets/js/site-favicon.js') }}?v=1"></script>
@stack('scripts')
</body>
</html>
