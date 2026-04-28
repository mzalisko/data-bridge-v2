# vibeB Blade Redesign — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Apply the vibeB (Modern SaaS) design system to all existing Laravel/Blade pages — replacing Restrained Loft / TG Dark with vibeB tokens, 240px sidebar, Inter + JetBrains Mono fonts — without touching any business logic.

**Architecture:** Pure CSS + Blade template changes. No new routes, no controller changes, no migrations. The design reference prototype lives in `CRM.html` + `src/` (read-only). Dark mode is toggled via `data-theme="dark"` attribute on `<html>` (existing JS kept). Old CSS variable names (`--bg-page`, `--bg-card`, etc.) are kept as aliases pointing to vibeB values — so page-specific CSS continues to work without a full rename sweep.

**Tech Stack:** Vanilla CSS (no Tailwind/Bootstrap), Blade templates, Google Fonts CDN (Inter 400/500/600/700 + JetBrains Mono 400/500/600), vanilla JS (site-favicon.js new file).

**Branch:** `feature/crm-redesign` (already exists, already pushed)

---

## File Map

| File | Action | What changes |
|---|---|---|
| `public/assets/css/tokens.css` | Rewrite | vibeB tokens + backwards-compat aliases |
| `public/assets/css/app.css` | Update | Add JetBrains Mono import |
| `public/assets/css/layout/shell.css` | Rewrite | flexbox shell, sidebar in flow, topbar |
| `public/assets/css/layout/rail.css` | Rewrite | 240px sidebar styles (renamed classes) |
| `public/assets/css/components/cards.css` | Rewrite | vibeB card, stat-card |
| `public/assets/css/components/buttons.css` | Rewrite | vibeB buttons + pill + table base |
| `public/assets/css/components/forms.css` | Rewrite | vibeB inputs, labels, form-stack |
| `public/assets/css/components/drawer.css` | Update | vibeB drawer colors |
| `public/assets/css/pages/dashboard.css` | Rewrite | stat grid, timeline, mini-stats |
| `public/assets/css/pages/sites.css` | Rewrite | crm-table, site-favicon, status pills |
| `public/assets/css/pages/site-groups.css` | Rewrite | groups grid card |
| `public/assets/css/pages/users.css` | Rewrite | users table, role badges |
| `public/assets/css/pages/logs.css` | Rewrite | log table, status cells |
| `public/assets/css/pages/data-browser.css` | Update | vibeB colors |
| `public/assets/css/pages/settings.css` | Update | vibeB colors |
| `public/assets/css/pages/batch.css` | Update | vibeB colors |
| `resources/views/layouts/app.blade.php` | Update | Fonts CDN, html class, sidebar classes |
| `resources/views/layouts/auth.blade.php` | Update | Fonts CDN, html class |
| `public/assets/js/site-favicon.js` | Create | hash-to-oklch favicon generator |

---

## Task 1: CSS Tokens — vibeB foundations

**Files:**
- Rewrite: `public/assets/css/tokens.css`
- Modify: `public/assets/css/app.css`

- [ ] **Step 1: Replace tokens.css with vibeB tokens + backwards-compat aliases**

Replace the entire file content:

```css
/* DataBridge CRM — Design Tokens (vibeB — Modern SaaS) */

:root {
  /* ── vibeB Light (default) ── */
  --bg:          #fafafa;
  --panel:       #ffffff;
  --panel-2:     #f7f7f8;
  --border:      #ececf0;
  --border-2:    #f1f1f4;
  --text:        #0b0b12;
  --text-2:      #4a4a58;
  --text-3:      #71717a;
  --muted:       #a1a1aa;
  --accent:      #5b5bf5;
  --accent-2:    #eef0ff;
  --accent-text: #3a3ac9;
  --success:     #10b981;
  --success-bg:  #e8f9f1;
  --warning:     #d97706;
  --warning-bg:  #fef4e2;
  --danger:      #e11d48;
  --danger-bg:   #fdecef;
  --shadow-sm:   0 1px 2px rgba(17,17,26,.04);
  --shadow:      0 4px 14px rgba(17,17,26,.06), 0 1px 2px rgba(17,17,26,.04);
  --radius:      10px;
  --radius-lg:   14px;
  --radius-sm:   6px;

  /* ── Layout ── */
  --sidebar-w:  240px;
  --topbar-h:   56px;

  /* ── Typography ── */
  --font-sans: "Inter", ui-sans-serif, system-ui, -apple-system, sans-serif;
  --font-mono: "JetBrains Mono", ui-monospace, "SFMono-Regular", Menlo, monospace;
  --font-size-xs:   11px;
  --font-size-sm:   13px;
  --font-size-base: 14px;
  --font-size-md:   16px;
  --font-size-lg:   20px;
  --font-size-xl:   28px;

  /* ── Spacing ── */
  --space-xs: 4px;
  --space-sm: 8px;
  --space-md: 16px;
  --space-lg: 24px;
  --space-xl: 40px;

  /* ── Status ── */
  --dot-ok:    #10b981;
  --dot-pause: #d97706;
  --dot-off:   #e11d48;

  /* ── Drawer ── */
  --drawer-width:       440px;
  --drawer-width-batch: 600px;

  /* ── Animation ── */
  --ease-ui:       cubic-bezier(0.4, 0, 0.2, 1);
  --duration-fast: 150ms;
  --duration-base: 250ms;

  /* ── Backwards-compat aliases (used by existing page CSS) ── */
  --bg-page:      var(--bg);
  --bg-card:      var(--panel);
  --bg-card2:     var(--panel-2);
  --bg-input:     var(--panel-2);
  --bg-hover:     var(--panel-2);
  --text-primary:   var(--text);
  --text-secondary: var(--text-2);
  --text-muted:     var(--text-3);
  --border-color:   var(--border);
  --border-focus:   var(--accent);
  --accent-hover:   color-mix(in srgb, var(--accent) 85%, black);
  --shadow-card:    var(--shadow);
  --shadow-drawer:  -4px 0 32px rgba(17,17,26,.12);
  --radius-card:    var(--radius-lg);
  --radius-pill:    999px;
  --radius-item:    var(--radius);
  --radius-input:   var(--radius-sm);
}

/* ── vibeB Dark ── */
[data-theme="dark"], .dark {
  --bg:          #0a0a0c;
  --panel:       #131318;
  --panel-2:     #0f0f14;
  --border:      #22222b;
  --border-2:    #1c1c23;
  --text:        #f2f2f5;
  --text-2:      #b4b4bf;
  --text-3:      #8a8a95;
  --muted:       #6b6b75;
  --accent:      #7c7cff;
  --accent-2:    #1d1d3a;
  --accent-text: #c7c7ff;
  --success:     #34d399;
  --success-bg:  #052e22;
  --warning:     #fbbf24;
  --warning-bg:  #3a2a08;
  --danger:      #fb7185;
  --danger-bg:   #3a1220;
  --shadow-sm:   0 1px 2px rgba(0,0,0,.4);
  --shadow:      0 8px 24px rgba(0,0,0,.5), 0 1px 2px rgba(0,0,0,.3);
  --shadow-drawer: -4px 0 32px rgba(0,0,0,.5);

  /* dark overrides for backwards-compat aliases */
  --bg-page:  var(--bg);
  --bg-card:  var(--panel);
  --bg-card2: var(--panel-2);
  --bg-input: var(--panel-2);
  --bg-hover: var(--panel-2);
}
```

- [ ] **Step 2: Add JetBrains Mono to app.css font import**

In `public/assets/css/app.css`, replace line 3:
```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
```
with:
```css
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap');
```

- [ ] **Step 3: Commit**

```bash
git add public/assets/css/tokens.css public/assets/css/app.css
git commit -m "style(tokens): replace Restrained Loft with vibeB tokens + backwards-compat aliases"
git push
```

---

## Task 2: Layout — sidebar + topbar + Blade app.blade.php

**Files:**
- Rewrite: `public/assets/css/layout/shell.css`
- Rewrite: `public/assets/css/layout/rail.css`
- Modify: `resources/views/layouts/app.blade.php`

- [ ] **Step 1: Rewrite shell.css — flexbox shell with sidebar in flow**

Replace entire `public/assets/css/layout/shell.css`:

```css
/* DataBridge CRM — Shell Layout */

*, *::before, *::after { box-sizing: border-box; }

html, body {
  margin: 0;
  padding: 0;
  font-family: var(--font-sans);
  font-size: var(--font-size-base);
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
}

.shell {
  display: flex;
  min-height: 100vh;
}

.shell-body {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  background: var(--bg);
}

/* ── Topbar ── */
.topbar {
  height: var(--topbar-h);
  background: var(--panel);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  position: sticky;
  top: 0;
  z-index: 10;
  flex-shrink: 0;
}

.topbar__title {
  font-size: 15px;
  font-weight: 600;
  color: var(--text);
}

.topbar__user {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: default;
}

.topbar__user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: var(--accent-2);
  color: var(--accent);
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  flex-shrink: 0;
}

.topbar__user-status {
  position: absolute;
  bottom: 0;
  right: 0;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--success);
  border: 2px solid var(--panel);
}

.topbar__user-name {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-2);
  white-space: nowrap;
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ── Page content ── */
.page-content {
  flex: 1;
  padding: 28px 32px 60px;
  overflow-y: auto;
}

.page-title {
  font-size: var(--font-size-lg);
  font-weight: 700;
  color: var(--text);
  margin: 0;
}

/* ── Auth layout ── */
.auth-body {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  background: var(--bg);
}

.auth-center {
  width: 100%;
  max-width: 400px;
  padding: var(--space-md);
}

/* ── Pagination ── */
[role="navigation"] {
  display: flex;
  align-items: center;
  gap: 4px;
  margin: 0;
}
[role="navigation"] svg { width: 14px; height: 14px; }
[role="navigation"] .hidden.sm\:flex-1 { display: none !important; }
[role="navigation"] a,
[role="navigation"] span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 2px 8px;
  min-width: 28px;
  height: 28px;
  font-size: 11px;
  border-radius: var(--radius-sm);
  background: var(--panel);
  border: 1px solid var(--border);
  color: var(--text-3);
  text-decoration: none;
  transition: all var(--duration-fast);
}
[role="navigation"] a:hover { color: var(--accent); border-color: var(--accent); }
[role="navigation"] [aria-current="page"] span {
  background: var(--accent);
  color: #fff;
  border-color: var(--accent);
}
[role="navigation"] .cursor-default { opacity: 0.5; pointer-events: none; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .shell { flex-direction: column; }
  .topbar { padding: 0 var(--space-md); }
  .topbar__user-name { display: none; }
  .page-content { padding: var(--space-md); }
}
```

- [ ] **Step 2: Rewrite rail.css — 240px sidebar styles**

Replace entire `public/assets/css/layout/rail.css`:

```css
/* DataBridge CRM — Sidebar (vibeB) */

.sidebar {
  width: var(--sidebar-w);
  background: var(--panel);
  border-right: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  flex-shrink: 0;
  z-index: 20;
}

.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 18px 20px 16px;
  font-weight: 700;
  font-size: 16px;
  color: var(--text);
  text-decoration: none;
  border-bottom: 1px solid var(--border);
  flex-shrink: 0;
}

.sidebar-logo svg { color: var(--accent); flex-shrink: 0; }

.sidebar-nav {
  flex: 1;
  padding: 10px 10px;
  list-style: none;
  margin: 0;
  overflow-y: auto;
}

.sidebar-nav li { margin-bottom: 2px; }

.sidebar-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 8px 12px;
  border-radius: var(--radius);
  color: var(--text-2);
  font-size: 14px;
  font-weight: 500;
  text-decoration: none;
  transition: background var(--duration-fast) var(--ease-ui),
              color var(--duration-fast) var(--ease-ui);
  cursor: pointer;
  border: none;
  background: none;
  width: 100%;
  text-align: left;
  font-family: var(--font-sans);
}

.sidebar-item:hover { background: var(--panel-2); color: var(--text); }

.sidebar-item.is-active {
  background: var(--accent-2);
  color: var(--accent);
}

.sidebar-item svg { flex-shrink: 0; }

.sidebar-bottom {
  padding: 10px;
  border-top: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex-shrink: 0;
}

.sidebar-bottom form { width: 100%; }

/* ── Responsive ── */
@media (max-width: 768px) {
  .sidebar {
    position: static;
    width: 100%;
    height: auto;
    flex-direction: row;
    overflow-x: auto;
    overflow-y: hidden;
    border-right: none;
    border-bottom: 1px solid var(--border);
  }
  .sidebar-logo { border-bottom: none; border-right: 1px solid var(--border); padding: 12px 16px; }
  .sidebar-nav  { display: flex; flex-direction: row; align-items: center; padding: 8px; gap: 2px; }
  .sidebar-nav li { margin-bottom: 0; }
  .sidebar-item { padding: 8px 10px; font-size: 0; gap: 0; }
  .sidebar-item svg { margin: 0 auto; }
  .sidebar-bottom { flex-direction: row; padding: 8px; border-top: none; border-left: 1px solid var(--border); margin-left: auto; }
}
```

- [ ] **Step 3: Update app.blade.php — fonts CDN, html class, sidebar markup**

Replace the entire file `resources/views/layouts/app.blade.php`:

```blade
<!DOCTYPE html>
<html lang="uk" class="vibeB vibe">
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
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 2.1l4 4-4 4"/>
                <path d="M3 12.2v-2a4 4 0 0 1 4-4h13.8"/>
                <path d="M7 21.9l-4-4 4-4"/>
                <path d="M21 11.8v2a4 4 0 0 1-4 4H3.2"/>
            </svg>
            DataBridge
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
            <li>
                <a href="{{ route('settings.index') }}"
                   class="sidebar-item {{ request()->routeIs('settings.*') ? 'is-active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                    Налаштування
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
<script src="{{ asset('assets/js/site-favicon.js') }}?v={{ filemtime(public_path('assets/js/site-favicon.js')) }}"></script>
@stack('scripts')
</body>
</html>
```

- [ ] **Step 4: Create site-favicon.js**

Create `public/assets/js/site-favicon.js`:

```js
/* DataBridge CRM — Site Favicon: hash(name) → oklch color */
(function () {
  function faviconStyle(name) {
    var h = 0;
    for (var i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) >>> 0;
    var hue = h % 360;
    return 'background:oklch(0.94 0.04 ' + hue + ');color:oklch(0.4 0.1 ' + hue + ');';
  }

  document.querySelectorAll('[data-site-favicon]').forEach(function (el) {
    el.setAttribute('style', faviconStyle(el.dataset.siteFavicon));
  });
})();
```

- [ ] **Step 5: Open http://localhost:8082 in browser and verify**

Expected: sidebar is 240px wide, white background, accent-colored active item, Inter font. Page background is `#fafafa` (light). No layout breakage.

- [ ] **Step 6: Commit**

```bash
git add public/assets/css/layout/shell.css public/assets/css/layout/rail.css resources/views/layouts/app.blade.php public/assets/js/site-favicon.js
git commit -m "style(layout): vibeB sidebar 240px + shell flexbox + topbar"
git push
```

---

## Task 3: Auth layout

**Files:**
- Modify: `resources/views/layouts/auth.blade.php`

- [ ] **Step 1: Update auth.blade.php — add fonts CDN + vibeB class**

Replace the entire file:

```blade
<!DOCTYPE html>
<html lang="uk" class="vibeB vibe">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DataBridge CRM')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="auth-body">

    <div class="auth-center">
        @yield('content')
    </div>

    <script src="{{ asset('assets/js/layout.js') }}"></script>
</body>
</html>
```

- [ ] **Step 2: Open http://localhost:8082/login and verify**

Expected: white card on `#fafafa` background, Inter font, no dark background.

- [ ] **Step 3: Commit**

```bash
git add resources/views/layouts/auth.blade.php
git commit -m "style(auth): vibeB class + Google Fonts in auth layout"
git push
```

---

## Task 4: Core components — cards, buttons, forms, drawer, pills, tables

**Files:**
- Rewrite: `public/assets/css/components/cards.css`
- Rewrite: `public/assets/css/components/buttons.css`
- Rewrite: `public/assets/css/components/forms.css`
- Update: `public/assets/css/components/drawer.css`

- [ ] **Step 1: Rewrite cards.css**

Replace entire `public/assets/css/components/cards.css`:

```css
/* DataBridge CRM — Cards */

/* ── Base card ── */
.card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: var(--space-lg);
}

/* ── Stat card ── */
.stat-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 20px 24px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.stat-card__label {
  font-size: 11px;
  font-weight: 600;
  color: var(--text-3);
  text-transform: uppercase;
  letter-spacing: 0.07em;
}

.stat-card__value {
  font-size: 28px;
  font-weight: 700;
  color: var(--text);
  line-height: 1;
}

.stat-card__delta {
  font-size: 12px;
  color: var(--text-3);
}

/* ── Pills / badges ── */
.pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
}

.pill-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: currentColor;
  flex-shrink: 0;
}

.pill-ok     { background: var(--success-bg); color: var(--success); }
.pill-pause  { background: var(--warning-bg); color: var(--warning); }
.pill-off    { background: var(--danger-bg);  color: var(--danger);  }
.pill-info   { background: var(--accent-2);   color: var(--accent);  }
.pill-neutral { background: var(--panel-2);   color: var(--text-2);  }

/* ── Site favicon avatar ── */
.site-favicon {
  width: 32px;
  height: 32px;
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
  font-family: var(--font-sans);
}

/* ── CRM Table ── */
.crm-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}

.crm-table th {
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-3);
  padding: 10px 16px;
  border-bottom: 1px solid var(--border);
  background: var(--panel-2);
  white-space: nowrap;
}

.crm-table td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border-2);
  color: var(--text);
  vertical-align: middle;
}

.crm-table tr:last-child td { border-bottom: none; }
.crm-table tbody tr:hover td { background: var(--panel-2); }

.crm-table__wrap {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

/* ── Page toolbar ── */
.page-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-md);
  margin-bottom: var(--space-lg);
}

/* ── Alert ── */
.alert {
  padding: 12px 16px;
  border-radius: var(--radius);
  font-size: 13px;
  margin-bottom: var(--space-md);
  border: 1px solid transparent;
}
.alert-success { background: var(--success-bg); color: var(--success); border-color: color-mix(in srgb, var(--success) 30%, transparent); }
.alert-error   { background: var(--danger-bg);  color: var(--danger);  border-color: color-mix(in srgb, var(--danger)  30%, transparent); }
```

- [ ] **Step 2: Rewrite buttons.css**

Replace entire `public/assets/css/components/buttons.css`:

```css
/* DataBridge CRM — Buttons */

.btn-primary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  height: 36px;
  padding: 0 16px;
  border-radius: var(--radius);
  background: var(--accent);
  color: #ffffff;
  font-size: 13px;
  font-weight: 600;
  font-family: var(--font-sans);
  border: none;
  cursor: pointer;
  text-decoration: none;
  white-space: nowrap;
  transition: opacity var(--duration-fast) var(--ease-ui);
}

.btn-primary:hover   { opacity: .88; }
.btn-primary:active  { opacity: .75; }
.btn-primary--full   { width: 100%; height: 40px; font-size: 14px; }

.btn-ghost {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  height: 36px;
  padding: 0 14px;
  border-radius: var(--radius);
  background: transparent;
  color: var(--text-2);
  font-size: 13px;
  font-weight: 500;
  font-family: var(--font-sans);
  border: 1px solid var(--border);
  cursor: pointer;
  text-decoration: none;
  white-space: nowrap;
  transition: background var(--duration-fast) var(--ease-ui),
              color var(--duration-fast) var(--ease-ui);
}

.btn-ghost:hover { background: var(--panel-2); color: var(--text); }

.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: var(--radius);
  background: transparent;
  color: var(--text-3);
  border: none;
  cursor: pointer;
  transition: background var(--duration-fast) var(--ease-ui),
              color var(--duration-fast) var(--ease-ui);
}

.btn-icon:hover { background: var(--panel-2); color: var(--text); }

.btn-danger {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  height: 36px;
  padding: 0 14px;
  border-radius: var(--radius);
  background: var(--danger-bg);
  color: var(--danger);
  font-size: 13px;
  font-weight: 500;
  font-family: var(--font-sans);
  border: none;
  cursor: pointer;
  white-space: nowrap;
  transition: background var(--duration-fast) var(--ease-ui);
}

.btn-danger:hover { background: var(--danger); color: #fff; }

/* ── Segment / tab buttons ── */
.btn-group {
  display: inline-flex;
  background: var(--panel-2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  overflow: hidden;
  flex-shrink: 0;
}

.btn-group__btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0 12px;
  height: 32px;
  font-size: 12px;
  font-weight: 500;
  color: var(--text-2);
  background: transparent;
  border: none;
  border-right: 1px solid var(--border);
  cursor: pointer;
  white-space: nowrap;
  text-decoration: none;
  font-family: var(--font-sans);
  transition: background var(--duration-fast), color var(--duration-fast);
}

.btn-group__btn:last-child { border-right: none; }
.btn-group__btn:hover { background: var(--panel); color: var(--text); }
.btn-group__btn.is-active { background: var(--accent); color: #fff; }

/* ── View toggle ── */
.view-toggle { display: inline-flex; }
.view-toggle__btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px;
  background: var(--panel); border: 1px solid var(--border);
  color: var(--text-3); cursor: pointer;
  transition: background var(--duration-fast), color var(--duration-fast);
}
.view-toggle__btn:first-child { border-radius: var(--radius) 0 0 var(--radius); }
.view-toggle__btn:last-child  { border-radius: 0 var(--radius) var(--radius) 0; border-left: none; }
.view-toggle__btn.is-active   { background: var(--accent); color: #fff; border-color: var(--accent); }

/* ── Batch toggle ── */
.btn-batch-toggle {
  display: inline-flex; align-items: center; gap: 6px;
  height: 32px; padding: 0 12px;
  border-radius: var(--radius);
  background: var(--panel); border: 1px solid var(--border);
  color: var(--text-2); font-size: 13px; font-weight: 500;
  cursor: pointer; font-family: var(--font-sans);
  transition: background var(--duration-fast), color var(--duration-fast);
}
.btn-batch-toggle:hover { background: var(--panel-2); color: var(--text); }
.btn-batch-toggle.is-active { background: var(--accent-2); color: var(--accent); border-color: var(--accent); }

/* ── Responsive ── */
@media (max-width: 768px) {
  .btn-primary, .btn-ghost, .btn-danger { min-height: 44px; }
  .btn-primary--full { width: 100%; }
}
```

- [ ] **Step 3: Rewrite forms.css**

Replace entire `public/assets/css/components/forms.css` — check its current content first with Read, then replace:

```css
/* DataBridge CRM — Forms */

.form-stack {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-label {
  font-size: 13px;
  font-weight: 500;
  color: var(--text-2);
}

.form-input,
.form-select,
.form-textarea {
  background: var(--panel-2);
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  padding: 8px 12px;
  font-size: 14px;
  font-family: var(--font-sans);
  color: var(--text);
  transition: border-color var(--duration-fast), box-shadow var(--duration-fast);
  outline: none;
  width: 100%;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-2);
}

.form-input::placeholder { color: var(--muted); }

.form-input--error,
.form-input--error:focus { border-color: var(--danger); box-shadow: 0 0 0 3px var(--danger-bg); }

.form-error { font-size: 12px; color: var(--danger); }
.form-hint  { font-size: 12px; color: var(--text-3); }

/* ── Checkbox / radio ── */
.form-check {
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-checkbox,
.form-radio {
  width: 16px;
  height: 16px;
  accent-color: var(--accent);
  cursor: pointer;
}

.form-check-label {
  font-size: 13px;
  color: var(--text-2);
  cursor: pointer;
}

/* ── Input group (suffix/prefix button) ── */
.input-group {
  display: flex;
  gap: 0;
}

.input-group .form-input {
  border-radius: var(--radius-sm) 0 0 var(--radius-sm);
  flex: 1;
}

.input-group__btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 12px;
  border: 1px solid var(--border);
  border-left: none;
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
  background: var(--panel-2);
  color: var(--text-3);
  cursor: pointer;
  transition: background var(--duration-fast), color var(--duration-fast);
}

.input-group__btn:hover { background: var(--panel); color: var(--text); }
.input-group__btn--copied { color: var(--success); }

/* ── Login card ── */
.form-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 36px 32px;
  box-shadow: var(--shadow);
}

.form-card__header { text-align: center; margin-bottom: 28px; }

.form-card__logo {
  width: 48px;
  height: 48px;
  border-radius: var(--radius);
  background: var(--accent-2);
  color: var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 16px;
  font-size: 24px;
}

.form-card__title {
  font-size: 22px;
  font-weight: 700;
  color: var(--text);
  margin: 0 0 4px;
}

.form-card__subtitle {
  font-size: 13px;
  color: var(--text-3);
  margin: 0;
}
```

- [ ] **Step 4: Update drawer.css — vibeB colors**

Read `public/assets/css/components/drawer.css` first, then replace color variables:

In `drawer.css`, make these replacements across the file using Edit:
- `var(--bg-card)` → `var(--panel)`
- `var(--border-color)` → `var(--border)`
- `var(--text-primary)` → `var(--text)`
- `var(--text-secondary)` → `var(--text-2)`
- `var(--bg-page)` → `var(--bg)`
- `var(--shadow-drawer)` → `var(--shadow-drawer)` (already aliases — no change needed)

Since backwards-compat aliases cover these, `drawer.css` will visually work immediately without changes. Verify visually in browser after Task 2.

- [ ] **Step 5: Commit**

```bash
git add public/assets/css/components/cards.css public/assets/css/components/buttons.css public/assets/css/components/forms.css
git commit -m "style(components): vibeB cards, buttons, forms, pills, crm-table"
git push
```

---

## Task 5: Dashboard page

**Files:**
- Rewrite: `public/assets/css/pages/dashboard.css`

- [ ] **Step 1: Read current dashboard.css to understand existing classes**

Before rewriting, run: `Read public/assets/css/pages/dashboard.css` to see which classes are used.

- [ ] **Step 2: Rewrite dashboard.css**

Replace entire `public/assets/css/pages/dashboard.css`:

```css
/* DataBridge CRM — Dashboard */

/* ── Layout ── */
.db-layout {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 24px;
  align-items: start;
}

@media (max-width: 1100px) { .db-layout { grid-template-columns: 1fr; } }

/* ── Stat grid ── */
.db-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}

@media (max-width: 900px) { .db-stats { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .db-stats { grid-template-columns: 1fr; } }

/* ── Card shared ── */
.db-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.db-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
}

.db-card__title {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.db-card__count {
  font-size: 12px;
  color: var(--text-3);
}

/* ── Empty state ── */
.db-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  padding: 40px 20px;
  color: var(--text-3);
  font-size: 13px;
}

.db-empty svg { opacity: .4; }

/* ── Timeline ── */
.db-timeline {
  list-style: none;
  margin: 0;
  padding: 0;
}

.db-event {
  display: flex;
  gap: 12px;
  padding: 12px 20px;
  border-bottom: 1px solid var(--border-2);
  position: relative;
}

.db-event:last-child { border-bottom: none; }

.db-event__dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
  margin-top: 4px;
  background: var(--text-3);
}

.db-event--ok .db-event__dot    { background: var(--success); }
.db-event--error .db-event__dot { background: var(--danger); }

.db-event__body { flex: 1; min-width: 0; }

.db-event__top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 2px;
}

.db-event__site {
  font-size: 13px;
  font-weight: 500;
  color: var(--text);
  text-decoration: none;
}

.db-event__site:hover { color: var(--accent); text-decoration: underline; }

.db-event__time {
  font-size: 11px;
  color: var(--text-3);
  white-space: nowrap;
  font-family: var(--font-mono);
}

.db-event__desc {
  font-size: 12px;
  color: var(--text-3);
}

.db-event__desc--err { color: var(--danger); }

/* ── Status badge ── */
.db-status {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 500;
}

.db-status--err { background: var(--danger-bg); color: var(--danger); }

/* ── Sidebar card (favorites, top sites) ── */
.db-side { display: flex; flex-direction: column; gap: 16px; }

.db-side-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.db-side-card__header {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--text-3);
}

.db-site-list { list-style: none; margin: 0; padding: 0; }

.db-site-row {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  border-bottom: 1px solid var(--border-2);
  text-decoration: none;
  color: var(--text);
  transition: background var(--duration-fast);
}

.db-site-row:last-child { border-bottom: none; }
.db-site-row:hover { background: var(--panel-2); }

.db-site-row__name {
  flex: 1;
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.db-site-row__meta { font-size: 11px; color: var(--text-3); font-family: var(--font-mono); }

/* ── Pagination section ── */
.db-pager {
  display: flex;
  justify-content: center;
  padding: 16px;
  border-top: 1px solid var(--border);
}

/* ── Fav button ── */
.db-fav-btn {
  flex-shrink: 0;
  border: none;
  background: none;
  cursor: pointer;
  font-size: 16px;
  color: var(--text-3);
  padding: 4px;
  border-radius: var(--radius-sm);
  transition: color var(--duration-fast), background var(--duration-fast);
}
.db-fav-btn:hover { color: #f59e0b; background: rgba(245,158,11,.1); }
.db-fav-btn.is-fav { color: #f59e0b; }
```

- [ ] **Step 3: Open http://localhost:8082 and verify dashboard**

Expected: stat cards in 4-col grid, sync timeline in card with white background, favorites sidebar on right.

- [ ] **Step 4: Commit**

```bash
git add public/assets/css/pages/dashboard.css
git commit -m "style(dashboard): vibeB layout — stat grid, timeline, side panel"
git push
```

---

## Task 6: Sites index + SiteFavicon

**Files:**
- Rewrite: `public/assets/css/pages/sites.css`

- [ ] **Step 1: Read current sites.css to capture needed classes**

Read `public/assets/css/pages/sites.css` (first 80 lines) to identify existing class names like `.site-card`, `.site-card--list`, `.sites-list`, etc.

- [ ] **Step 2: Rewrite sites.css**

Replace entire `public/assets/css/pages/sites.css`:

```css
/* DataBridge CRM — Sites */

/* ── Page controls ── */
.page-controls {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.page-controls__search-row {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.page-controls__search {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 7px 12px;
  flex: 1;
  min-width: 200px;
}

.page-controls__search svg { color: var(--text-3); flex-shrink: 0; }

.page-controls__search-input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 13px;
  color: var(--text);
  font-family: var(--font-sans);
  width: 100%;
}

.page-controls__search-input::placeholder { color: var(--muted); }

.page-controls__filters {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

/* ── Sites list (table view) ── */
.sites-list { margin-bottom: 20px; }

.site-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  background: var(--panel);
  border-bottom: 1px solid var(--border-2);
  cursor: pointer;
  text-decoration: none;
  color: var(--text);
  transition: background var(--duration-fast);
  position: relative;
}

.site-card:first-child { border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
.site-card:last-child  { border-radius: 0 0 var(--radius-lg) var(--radius-lg); border-bottom: none; }
.site-card:only-child  { border-radius: var(--radius-lg); }

.sites-list { background: var(--panel); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; }

.site-card:hover { background: var(--panel-2); }
.site-card.is-batch-selected { background: var(--accent-2); }

/* ── Favicon ── */
.site-card__favicon {
  width: 34px;
  height: 34px;
  border-radius: var(--radius);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  flex-shrink: 0;
  font-family: var(--font-sans);
}

/* ── Card body ── */
.site-card__body { flex: 1; min-width: 0; }
.site-card__name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.site-card__domain {
  font-size: 12px;
  color: var(--text-3);
  font-family: var(--font-mono);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ── Right meta ── */
.site-card__meta {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.site-card__group {
  font-size: 12px;
  color: var(--text-3);
  padding: 2px 8px;
  background: var(--panel-2);
  border-radius: 999px;
  white-space: nowrap;
}

/* ── Mini stats ── */
.mini-stats {
  display: flex;
  align-items: center;
  gap: 14px;
}

.mini-stat {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: var(--text-3);
  font-family: var(--font-mono);
}

.mini-stat svg { color: var(--text-3); opacity: .7; }

/* ── Actions ── */
.site-card__actions {
  display: flex;
  align-items: center;
  gap: 4px;
  opacity: 0;
  transition: opacity var(--duration-fast);
}

.site-card:hover .site-card__actions { opacity: 1; }

/* ── Batch checkbox ── */
.batch-cb {
  width: 16px;
  height: 16px;
  accent-color: var(--accent);
  cursor: pointer;
  flex-shrink: 0;
  display: none;
}

.batch-mode .batch-cb { display: flex; }

/* ── Batch bar ── */
.batch-bar {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%) translateY(80px);
  opacity: 0;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 999px;
  box-shadow: var(--shadow);
  font-size: 13px;
  font-weight: 500;
  color: var(--text);
  z-index: 50;
  transition: transform var(--duration-base) var(--ease-ui),
              opacity var(--duration-base) var(--ease-ui);
  white-space: nowrap;
}

.batch-bar.is-visible {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
}

/* ── Grid view ── */
.sites-list--grid {
  display: grid !important;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 14px;
  background: transparent;
  border: none;
  border-radius: 0;
}

.sites-list--grid .site-card {
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  flex-direction: column;
  align-items: flex-start;
  padding: 16px;
}

.sites-list--grid .site-card__meta { margin-top: 8px; width: 100%; }
.sites-list--grid .mini-stats { flex-wrap: wrap; }

/* ── Site show — tabs ── */
.site-tabs {
  display: flex;
  gap: 4px;
  margin-bottom: 24px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 0;
}

.site-tab {
  padding: 8px 16px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-2);
  text-decoration: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: color var(--duration-fast), border-color var(--duration-fast);
  white-space: nowrap;
}

.site-tab:hover { color: var(--text); }
.site-tab.is-active { color: var(--accent); border-bottom-color: var(--accent); }

/* ── Site detail layout ── */
.site-show {
  display: grid;
  grid-template-columns: 1fr 280px;
  gap: 24px;
  align-items: start;
}

@media (max-width: 960px) { .site-show { grid-template-columns: 1fr; } }

/* ── API key display ── */
.api-key-display {
  font-family: var(--font-mono);
  font-size: 12px;
  color: var(--text-3);
  background: var(--panel-2);
  padding: 4px 8px;
  border-radius: var(--radius-sm);
}
```

- [ ] **Step 3: Add `data-site-favicon` to site cards in sites/index.blade.php**

In `resources/views/admin/sites/index.blade.php`, find the site card favicon element. It currently uses a CSS class for the favicon square. Update it to use `data-site-favicon`:

Find the `.site-card__favicon` or equivalent element and add `data-site-favicon="{{ $site->name }}"`. The `site-favicon.js` script will then apply the hash color automatically.

Example: if the current markup is:
```blade
<div class="site-card__favicon">{{ mb_strtoupper(mb_substr($site->name, 0, 1)) }}</div>
```
Change to:
```blade
<div class="site-card__favicon" data-site-favicon="{{ $site->name }}">{{ mb_strtoupper(mb_substr($site->name, 0, 1)) }}</div>
```

- [ ] **Step 4: Open http://localhost:8082/sites and verify**

Expected: sites appear as rows in a white card panel, favicon squares with unique oklch colors per site, status pill (pill-ok/pause/off), mini-stats with monospace font.

- [ ] **Step 5: Commit**

```bash
git add public/assets/css/pages/sites.css resources/views/admin/sites/index.blade.php
git commit -m "style(sites): vibeB site list, favicon hash colors, site detail tabs"
git push
```

---

## Task 7: Site Groups page

**Files:**
- Rewrite: `public/assets/css/pages/site-groups.css`

- [ ] **Step 1: Read current site-groups.css to see existing class names**

Read first 80 lines of `public/assets/css/pages/site-groups.css`.

- [ ] **Step 2: Rewrite site-groups.css**

Replace entire `public/assets/css/pages/site-groups.css`:

```css
/* DataBridge CRM — Site Groups */

/* ── Groups grid ── */
.groups-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

/* ── Group card ── */
.group-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 20px;
  cursor: pointer;
  text-decoration: none;
  color: var(--text);
  display: flex;
  flex-direction: column;
  gap: 14px;
  transition: box-shadow var(--duration-fast) var(--ease-ui),
              border-color var(--duration-fast) var(--ease-ui);
}

.group-card:hover {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px var(--accent-2);
}

/* ── Color square ── */
.group-card__color {
  width: 40px;
  height: 40px;
  border-radius: var(--radius);
  flex-shrink: 0;
}

.group-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.group-card__name {
  font-size: 15px;
  font-weight: 600;
  color: var(--text);
}

.group-card__count {
  font-size: 12px;
  color: var(--text-3);
  background: var(--panel-2);
  border-radius: 999px;
  padding: 2px 8px;
}

.group-card__desc {
  font-size: 13px;
  color: var(--text-2);
  line-height: 1.5;
}

/* ── Site chips inside card ── */
.group-card__sites {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.group-site-chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  background: var(--panel-2);
  border-radius: 999px;
  font-size: 11px;
  font-weight: 500;
  color: var(--text-2);
  text-decoration: none;
  border: 1px solid var(--border);
}

.group-site-chip:hover { background: var(--accent-2); color: var(--accent); border-color: var(--accent); }

/* ── Actions ── */
.group-card__actions {
  display: flex;
  gap: 6px;
  justify-content: flex-end;
}

/* ── Empty state ── */
.groups-empty {
  grid-column: 1 / -1;
  text-align: center;
  padding: 60px 20px;
  color: var(--text-3);
  font-size: 14px;
}
```

- [ ] **Step 3: Open http://localhost:8082/site-groups and verify**

Expected: groups as cards in responsive grid, color square, site chips.

- [ ] **Step 4: Commit**

```bash
git add public/assets/css/pages/site-groups.css
git commit -m "style(groups): vibeB groups grid cards with site chips"
git push
```

---

## Task 8: Users page

**Files:**
- Rewrite: `public/assets/css/pages/users.css`

- [ ] **Step 1: Rewrite users.css**

Replace entire `public/assets/css/pages/users.css`:

```css
/* DataBridge CRM — Users */

/* ── Users list ── */
.users-list {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
  margin-bottom: 20px;
}

.user-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border-bottom: 1px solid var(--border-2);
  transition: background var(--duration-fast);
}

.user-row:last-child { border-bottom: none; }
.user-row:hover { background: var(--panel-2); }

/* ── Avatar ── */
.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: var(--accent-2);
  color: var(--accent);
  font-size: 14px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-row__body { flex: 1; min-width: 0; }

.user-row__name {
  font-size: 14px;
  font-weight: 500;
  color: var(--text);
}

.user-row__email {
  font-size: 12px;
  color: var(--text-3);
  font-family: var(--font-mono);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ── Role badge ── */
.role-badge {
  display: inline-flex;
  align-items: center;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.role-badge--admin   { background: var(--accent-2);   color: var(--accent);   }
.role-badge--manager { background: var(--success-bg);  color: var(--success);  }
.role-badge--viewer  { background: var(--panel-2);     color: var(--text-3);   }

/* ── Grid view ── */
.users-list--grid {
  display: grid !important;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 14px;
  background: transparent;
  border: none;
  border-radius: 0;
}

.users-list--grid .user-row {
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 20px;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
}

.users-list--grid .user-row:hover { background: var(--panel); }
.users-list--grid .user-avatar { width: 48px; height: 48px; font-size: 18px; margin-bottom: 4px; }
.users-list--grid .user-row__email { justify-content: center; }

/* ── Status ── */
.user-status {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--success);
  flex-shrink: 0;
}
```

- [ ] **Step 2: Open http://localhost:8082/users and verify**

Expected: users as rows in card panel, avatar with accent color, role badge with correct color per role.

- [ ] **Step 3: Commit**

```bash
git add public/assets/css/pages/users.css
git commit -m "style(users): vibeB user list with role badges"
git push
```

---

## Task 9: Logs pages

**Files:**
- Rewrite: `public/assets/css/pages/logs.css`

- [ ] **Step 1: Rewrite logs.css**

Replace entire `public/assets/css/pages/logs.css`:

```css
/* DataBridge CRM — Logs */

/* ── Log tabs ── */
.log-tabs {
  display: flex;
  gap: 0;
  margin-bottom: 20px;
  border-bottom: 1px solid var(--border);
}

.log-tab {
  padding: 8px 20px;
  font-size: 13px;
  font-weight: 500;
  color: var(--text-2);
  text-decoration: none;
  border-bottom: 2px solid transparent;
  margin-bottom: -1px;
  transition: color var(--duration-fast), border-color var(--duration-fast);
  display: flex;
  align-items: center;
  gap: 6px;
}

.log-tab:hover { color: var(--text); }
.log-tab.is-active { color: var(--accent); border-bottom-color: var(--accent); }

/* ── Log table ── */
.log-table-wrap {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.log-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.log-table th {
  text-align: left;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-3);
  padding: 10px 16px;
  border-bottom: 1px solid var(--border);
  background: var(--panel-2);
  white-space: nowrap;
}

.log-table td {
  padding: 10px 16px;
  border-bottom: 1px solid var(--border-2);
  color: var(--text);
  vertical-align: top;
}

.log-table tr:last-child td { border-bottom: none; }
.log-table tbody tr:hover td { background: var(--panel-2); }

/* ── Status cells ── */
.log-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.log-status--ok      { background: var(--success-bg); color: var(--success); }
.log-status--error   { background: var(--danger-bg);  color: var(--danger); }
.log-status--warning { background: var(--warning-bg); color: var(--warning); }
.log-status--info    { background: var(--accent-2);   color: var(--accent); }

/* ── Timestamp ── */
.log-ts {
  font-size: 11px;
  color: var(--text-3);
  font-family: var(--font-mono);
  white-space: nowrap;
}

/* ── Message / payload ── */
.log-msg {
  font-size: 12px;
  color: var(--text-2);
  font-family: var(--font-mono);
  max-width: 400px;
  white-space: pre-wrap;
  word-break: break-all;
}

/* ── Empty ── */
.log-empty {
  text-align: center;
  padding: 40px 20px;
  color: var(--text-3);
  font-size: 13px;
}
```

- [ ] **Step 2: Open http://localhost:8082/logs/system and verify**

Expected: log rows in card panel, status pills, monospace timestamps.

- [ ] **Step 3: Commit**

```bash
git add public/assets/css/pages/logs.css
git commit -m "style(logs): vibeB log table with status pills"
git push
```

---

## Task 10: Data Browser, Settings, Batch

**Files:**
- Update: `public/assets/css/pages/data-browser.css`
- Update: `public/assets/css/pages/settings.css`
- Update: `public/assets/css/pages/batch.css`

Because backwards-compat aliases already map old variable names to vibeB values, these pages will already look mostly correct after Task 1. This task does a visual check and fixes any remaining color/radius inconsistencies.

- [ ] **Step 1: Open Data Browser http://localhost:8082/data and note visual issues**

Look for: old dark colors showing through, incorrect border-radius, missing table styles.

- [ ] **Step 2: Open Batch edit http://localhost:8082/sites/batch?ids[]=1 and note issues**

(Use a real site ID from your seeded data.)

- [ ] **Step 3: Open Settings http://localhost:8082/settings and note issues**

- [ ] **Step 4: In data-browser.css — update panel/table classes to use vibeB**

Read `public/assets/css/pages/data-browser.css`. Replace any remaining hardcoded dark colors with CSS variables. Specifically look for `background: #` or `color: #` that aren't using variables and replace with appropriate `var(--*)`.

- [ ] **Step 5: In batch.css and settings.css — same approach**

Read each file and replace hardcoded colors with CSS variables.

- [ ] **Step 6: Commit**

```bash
git add public/assets/css/pages/data-browser.css public/assets/css/pages/settings.css public/assets/css/pages/batch.css
git commit -m "style(pages): vibeB colors for data browser, settings, batch"
git push
```

---

## Task 11: Final visual polish + dark mode verify

**Files:** Various — minor fixes based on browser review.

- [ ] **Step 1: Toggle to dark mode in browser (click Тема in sidebar)**

Verify: background goes dark (`#0a0a0c`), panels dark (`#131318`), accent turns to `#7c7cff`, text turns to `#f2f2f5`.

- [ ] **Step 2: Walk through all pages in dark mode**

Check: Dashboard, Sites, Site detail (all tabs), Groups, Users, Logs, Data Browser. Note any pages with broken dark mode.

- [ ] **Step 3: Fix any broken dark mode pages**

The backwards-compat aliases should handle most issues automatically. Any hardcoded `#hex` colors in page CSS need to be replaced with CSS variables.

- [ ] **Step 4: Verify login page dark mode**

Navigate to `/login` in dark mode. Expected: dark background (`var(--bg)` = `#0a0a0c`), dark card.

Note: The login page uses `layouts/auth.blade.php`. Dark mode is toggled via `data-theme` attribute on `<html>`. The auth layout doesn't have the theme toggle button, so dark mode on login depends on cookie. This is correct existing behavior — no change needed.

- [ ] **Step 5: Test at 768px viewport width (mobile)**

Verify: sidebar collapses to horizontal nav bar, content is readable, buttons have 44px touch targets.

- [ ] **Step 6: Commit any fixes**

```bash
git add public/assets/css/
git commit -m "style(polish): dark mode fixes and mobile responsive verification"
git push
```

---

## Self-Review Checklist

- [x] **Spec coverage**: Tasks 1-11 cover all pages listed in TASK-CRM-REDESIGN (Dashboard, Sites, Groups, Site detail, Users, Logs, Data Browser, Batch, Settings, Auth).
- [x] **SiteFavicon**: Task 2 creates `site-favicon.js`, Task 6 adds `data-site-favicon` to Blade template.
- [x] **vibeB tokens**: Task 1 — full vibeB light/dark token set with backwards-compat aliases.
- [x] **240px sidebar**: Task 2 — `layout/rail.css` rewritten, `app.blade.php` updated.
- [x] **Fonts**: Task 2 (app.blade.php + app.css) and Task 3 (auth.blade.php) — Inter + JetBrains Mono.
- [x] **No logic changes**: Every step modifies only CSS and Blade template structure (no controller/model/migration changes).
- [x] **Dark mode**: Task 1 defines `[data-theme="dark"]` dark tokens; existing `toggleTheme()` JS in `layout.js` continues to work.
- [x] **No placeholders**: All CSS code blocks are complete and exact.
- [x] **Type consistency**: CSS classes are consistent across tasks (`.crm-table` defined in Task 4 cards.css, used in Task 5+).
