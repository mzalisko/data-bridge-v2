<!DOCTYPE html>
<html lang="uk" {{ request()->cookie('theme') === 'dark' ? 'data-theme="dark"' : '' }}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DataBridge CRM')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Inline theme bootstrap — runs before CSS to avoid flash --}}
    <script>
        (function(){
            try {
                var ck = document.cookie.split('; ').find(function(r){return r.indexOf('theme=')===0;});
                var theme = ck ? ck.split('=')[1] : (localStorage.getItem('theme') || 'light');
                if (theme === 'dark') document.documentElement.setAttribute('data-theme','dark');
            } catch(e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
    @stack('styles')
</head>
<body>

@php
    use App\Models\Site;
    use App\Models\SiteGroup;
    $sitesCount  = Site::count();
    $onlineCount = Site::where('is_active', true)->count();
    $userName    = auth()->user()->name ?? 'User';
    $userInitial = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');
@endphp

<div class="shell">

    {{-- ============ SIDEBAR ============ --}}
    <aside class="sidebar">

        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="sidebar-logo">
            <span class="sidebar-logo__icon">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 7h7a5 5 0 0 1 5 5v0a5 5 0 0 1-5 5H4"/>
                    <path d="M20 17h-7a5 5 0 0 1-5-5v0a5 5 0 0 1 5-5h7"/>
                </svg>
            </span>
            <div>
                <div class="sidebar-logo__name">DataBridge</div>
                <div class="sidebar-logo__sub">CRM · workspace</div>
            </div>
        </a>

        {{-- Nav --}}
        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}"
               class="sidebar-item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/>
                    <rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('sites.index') }}"
               class="sidebar-item {{ request()->routeIs('sites.*') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="3.5"/><path d="M5 20c1.2-3.3 4-5 7-5s5.8 1.7 7 5"/>
                </svg>
                <span>Sites</span>
                <span class="sidebar-item__count">{{ $sitesCount }}</span>
            </a>
            <a href="{{ route('site-groups.index') }}"
               class="sidebar-item {{ request()->routeIs('site-groups.*') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/>
                    <rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
                </svg>
                <span>Site groups</span>
            </a>
            <a href="{{ route('users.index') }}"
               class="sidebar-item {{ request()->routeIs('users.*') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Team</span>
            </a>
            <a href="{{ route('logs.system') }}"
               class="sidebar-item {{ request()->routeIs('logs.*') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 0 1 15.5-6.3L21 8"/><path d="M21 4v4h-4"/>
                    <path d="M21 12a9 9 0 0 1-15.5 6.3L3 16"/><path d="M3 20v-4h4"/>
                </svg>
                <span>Activity log</span>
            </a>
        </nav>

        <div class="sidebar-spacer"></div>

        {{-- Workspace block --}}
        <div class="sidebar-block">
            <div class="sidebar-block__label">Workspace</div>
            <div class="sidebar-block__name">DataBridge HQ</div>
            <div class="sidebar-block__avatars">
                @foreach(['A','S','D','K'] as $i => $letter)
                    <span class="avatar" style="width:22px;height:22px;font-size:9px;background:var(--accent-2);color:var(--accent-text);margin-left:{{ $i === 0 ? 0 : -6 }}px;">{{ $letter }}</span>
                @endforeach
                <button class="icon-btn" style="width:22px;height:22px;border:1px dashed var(--border);border-radius:99px;font-size:14px;color:var(--text-3);margin-left:2px;padding:0;">+</button>
            </div>
        </div>

        {{-- User row --}}
        <div class="sidebar-user">
            <span class="avatar" style="width:28px;height:28px;font-size:11px;background:var(--accent-2);color:var(--accent-text);">{{ $userInitial }}</span>
            <div class="sidebar-user__info">
                <div class="sidebar-user__name">{{ $userName }}</div>
                <div class="sidebar-user__role">Owner</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="icon-btn" title="Logout">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <div class="shell-body">

        {{-- Topbar --}}
        <header class="topbar">
            <div class="topbar__status">
                <span class="topbar__status-dot"></span>
                Workspace healthy · {{ $onlineCount }} of {{ $sitesCount }} sites online
            </div>
            <div style="flex:1"></div>
            <button class="btn btn--ghost btn--sm" type="button">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2H4.5L6 16z"/><path d="M10 20a2 2 0 0 0 4 0"/>
                </svg>
                <span>4</span>
            </button>
            <a class="btn btn--secondary btn--sm" href="#" target="_blank">
                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 4h6v6"/><path d="M20 4 10 14"/>
                    <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/>
                </svg>
                Docs
            </a>
        </header>

        <main class="page-content">
            @yield('content')
        </main>
    </div>
</div>

{{-- Theme toggle FAB --}}
<button class="theme-fab" onclick="toggleTheme()" title="Toggle theme">
    <span id="theme-icon">{{ request()->cookie('theme') === 'dark' ? '☀' : '☾' }}</span>
</button>

<script>
function toggleTheme() {
    var html = document.documentElement;
    var isDark = html.getAttribute('data-theme') === 'dark';
    var next = isDark ? 'light' : 'dark';
    if (next === 'dark') html.setAttribute('data-theme', 'dark');
    else                 html.removeAttribute('data-theme');
    document.cookie = 'theme=' + next + '; path=/; max-age=31536000; SameSite=Lax';
    try { localStorage.setItem('theme', next); } catch(e) {}
    var icon = document.getElementById('theme-icon');
    if (icon) icon.textContent = next === 'dark' ? '☀' : '☾';
}
function openDrawer(id) {
    var ov = document.getElementById(id + '-overlay');
    var dr = document.getElementById(id);
    if (ov) ov.classList.add('is-open');
    if (dr) dr.classList.add('is-open');
}
function closeDrawer(id) {
    var ov = document.getElementById(id + '-overlay');
    var dr = document.getElementById(id);
    if (ov) ov.classList.remove('is-open');
    if (dr) dr.classList.remove('is-open');
}
</script>
@stack('scripts')
</body>
</html>
