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
    $teamMembers = \App\Models\User::where('is_active', true)->orderBy('name')->get(['id','name','role']);
    // Online users: active in last 5 minutes via cache (one read per page load)
    $onlineData    = \Illuminate\Support\Facades\Cache::get('crm_online', []);
    $fiveMinAgo    = now()->subMinutes(5)->timestamp;
    $onlineUserIds = array_keys(array_filter($onlineData, fn($ts) => $ts >= $fiveMinAgo));
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
            <a href="{{ route('logs.activity') }}"
               class="sidebar-item {{ request()->routeIs('logs.*') ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
                </svg>
                <span>Журнал змін</span>
            </a>
        </nav>

        <div class="sidebar-spacer"></div>

        {{-- Workspace block --}}
        <div class="sidebar-block">
            <div class="sidebar-block__label">Workspace</div>
            <div class="sidebar-block__name">DataBridge HQ</div>
            <div class="sidebar-block__avatars">
                @foreach($teamMembers->take(5) as $i => $member)
                    @php
                        $initials  = collect(explode(' ', $member->name))->map(fn($w) => mb_strtoupper(mb_substr($w,0,1,'UTF-8'),'UTF-8'))->take(2)->implode('');
                        $isOnline  = in_array($member->id, $onlineUserIds);
                        $titleText = $member->name . ' (' . ucfirst($member->role) . ')' . ($isOnline ? ' · онлайн' : '');
                    @endphp
                    <span style="position:relative;display:inline-block;margin-left:{{ $i === 0 ? 0 : -7 }}px;">
                        <a href="{{ route('users.index') }}" title="{{ $titleText }}"
                           class="avatar sidebar-ws-avatar" style="width:24px;height:24px;font-size:9px;{{ auth()->id() === $member->id ? 'border:2px solid var(--accent);' : '' }}">{{ $initials }}</a>
                        @if($isOnline)
                            <span style="position:absolute;bottom:0;right:0;width:7px;height:7px;background:var(--success);border:1.5px solid var(--bg-card);border-radius:50%;"></span>
                        @endif
                    </span>
                @endforeach
                @if($teamMembers->count() > 5)
                    <span class="avatar" style="width:24px;height:24px;font-size:8px;background:var(--panel-2);color:var(--text-3);margin-left:-7px;">+{{ $teamMembers->count()-5 }}</span>
                @endif
            </div>
            @php $onlineNow = $teamMembers->filter(fn($m) => in_array($m->id, $onlineUserIds))->count(); @endphp
            @if($onlineNow > 0)
                <div style="font-size:10px;color:var(--success);margin-top:6px;display:flex;align-items:center;gap:4px;">
                    <span style="width:5px;height:5px;background:var(--success);border-radius:50%;display:inline-block;"></span>
                    {{ $onlineNow }} онлайн зараз
                </div>
            @endif
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
            <a href="{{ route('logs.activity') }}" class="btn btn--ghost btn--sm" title="Журнал змін">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 16V11a6 6 0 0 1 12 0v5l1.5 2H4.5L6 16z"/><path d="M10 20a2 2 0 0 0 4 0"/>
                </svg>
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
// ── Activity diff toggle ─────────────────────────────────────
function actToggle(row) {
    var diff = row.querySelector('.act-diff');
    var chev = row.querySelector('.act-chevron');
    if (!diff) return;
    var open = row.classList.toggle('is-open');
    if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
}
// ── Custom select (cselect) ─────────────────────────────────
function csToggle(id) {
    var cs = document.getElementById(id);
    var isOpen = cs.classList.contains('is-open');
    document.querySelectorAll('.cselect.is-open').forEach(function(el) { el.classList.remove('is-open'); });
    if (!isOpen) cs.classList.add('is-open');
}
function csSelect(id, value, label) {
    var cs = document.getElementById(id);
    cs.querySelector('input[type=hidden]').value = value;
    cs.querySelector('.cselect__label').textContent = label;
    cs.querySelectorAll('.cselect__option').forEach(function(o) { o.classList.remove('is-active'); });
    if (event && event.target) event.target.classList.add('is-active');
    cs.classList.remove('is-open');
    var form = cs.closest('form');
    if (form) form.submit();
}
function csFormSelect(id, value, label) {
    var cs = document.getElementById(id);
    cs.querySelector('input[type=hidden]').value = value;
    cs.querySelector('.cselect__label').textContent = label;
    cs.querySelectorAll('.cselect__option').forEach(function(o) { o.classList.remove('is-active'); });
    if (event && event.target) event.target.classList.add('is-active');
    cs.classList.remove('is-open');
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.cselect')) {
        document.querySelectorAll('.cselect.is-open').forEach(function(el) { el.classList.remove('is-open'); });
    }
});
</script>
@stack('scripts')
</body>
</html>
