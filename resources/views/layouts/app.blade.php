<!DOCTYPE html>
<html lang="uk" data-theme="{{ cookie('theme', 'light') }}">
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

    {{-- ── Sidebar ── --}}
    <aside class="crm-sidebar">

        {{-- Logo --}}
        <div class="sidebar-logo">
            <span class="sidebar-logo__icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7h7a5 5 0 0 1 5 5v0a5 5 0 0 1-5 5H4"/>
                    <path d="M20 17h-7a5 5 0 0 1-5-5v0a5 5 0 0 1 5-5h7"/>
                </svg>
            </span>
            <div>
                <div class="sidebar-logo__name">DataBridge</div>
                <div class="sidebar-logo__sub">CRM · workspace</div>
            </div>
        </div>

        {{-- Nav --}}
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}"
                   class="sidebar-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                    <span class="sidebar-item__label">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('sites.index') }}"
                   class="sidebar-item {{ request()->routeIs('sites.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="3.5"/>
                        <path d="M5 20c1.2-3.3 4-5 7-5s5.8 1.7 7 5"/>
                    </svg>
                    <span class="sidebar-item__label">Sites</span>
                </a>
            </li>
            <li>
                <a href="{{ route('site-groups.index') }}"
                   class="sidebar-item {{ request()->routeIs('site-groups.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1.5"/>
                        <rect x="14" y="3" width="7" height="5" rx="1.5"/>
                        <rect x="14" y="12" width="7" height="9" rx="1.5"/>
                        <rect x="3" y="16" width="7" height="5" rx="1.5"/>
                    </svg>
                    <span class="sidebar-item__label">Site groups</span>
                </a>
            </li>
            <li>
                <a href="{{ route('data.index') }}"
                   class="sidebar-item {{ request()->routeIs('data.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="5" rx="9" ry="3"/>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                    </svg>
                    <span class="sidebar-item__label">Data browser</span>
                </a>
            </li>
            <li>
                <a href="{{ route('users.index') }}"
                   class="sidebar-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span class="sidebar-item__label">Users</span>
                </a>
            </li>
            <li>
                <a href="{{ route('logs.system') }}"
                   class="sidebar-item {{ request()->routeIs('logs.*') ? 'is-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 0 1 15.5-6.3L21 8"/>
                        <path d="M21 4v4h-4"/>
                        <path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"/>
                        <path d="M3 20v-4h4"/>
                    </svg>
                    <span class="sidebar-item__label">Activity log</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-spacer"></div>

        {{-- Theme + Logout actions --}}
        <div class="sidebar-actions">
            <button class="sidebar-action-btn" onclick="toggleTheme()" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
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
                <span>Toggle theme</span>
            </button>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="sidebar-action-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    <span>Sign out</span>
                </button>
            </form>
        </div>

        {{-- User --}}
        <div class="sidebar-user">
            @php $n = auth()->user()->name ?? (auth()->user()->email ?? 'User'); @endphp
            <div class="sidebar-user__avatar">
                {{ mb_strtoupper(mb_substr($n, 0, 1, 'UTF-8'), 'UTF-8') }}
            </div>
            <div style="min-width:0;flex:1">
                <div class="sidebar-user__name">{{ $n }}</div>
                <div class="sidebar-user__role">{{ ucfirst(auth()->user()->role ?? 'admin') }}</div>
            </div>
        </div>

    </aside>

    {{-- ── Main body ── --}}
    <div class="shell-body">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar__status">
                <span class="topbar__status-dot"></span>
                Workspace · DataBridge CRM
            </div>
            <div class="topbar__spacer"></div>
            <div class="topbar__actions">
                <div class="topbar__user">
                    <div class="topbar__user-avatar">
                        {{ mb_strtoupper(mb_substr($n, 0, 1, 'UTF-8'), 'UTF-8') }}
                        <span class="topbar__user-status"></span>
                    </div>
                    <span class="topbar__user-name">{{ $n }}</span>
                </div>
            </div>
        </header>

        <main class="page-content">
            @yield('content')
        </main>
    </div>

</div>

<script src="{{ asset('assets/js/layout.js') }}?v={{ filemtime(public_path('assets/js/layout.js')) }}"></script>
@stack('scripts')
</body>
</html>
