# UI Redesign — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Замінити кольорову схему на TG Dark, переробити page-controls на 2-рядкові pills, оновити картки груп (list+grid), картки сайтів (favicon-блок + sync-dot), сторінку сайту (sidebar-layout), і виправити баг втрати фокусу в пошуку.

**Architecture:** Всі зміни — CSS + Blade + vanilla JS. Жодних нових залежностей. Client-side пошук через `data-searchable` атрибут і функцію `initClientSearch()` в `layout.js`. View-toggle для груп через той самий `initViewToggle` що вже є для сайтів.

**Tech Stack:** Laravel 13, Blade templates, vanilla CSS (custom design system), vanilla JS. Docker: `docker-compose exec php php artisan ...`. Dev URL: http://localhost:8082.

**Spec:** `docs/superpowers/specs/2026-04-11-ui-redesign-design.md`

**Branch:** продовжуємо на `feature/task-l009-logs` або створюємо `feature/task-ui-redesign`.

---

## File Map

| Файл | Дія | Що змінюється |
|---|---|---|
| `public/assets/css/tokens.css` | Modify | TG Dark токени (замінити всі dark-theme змінні) |
| `public/assets/css/pages/site-groups.css` | Modify | `.page-controls` 2-рядковий, `.filter-pill`, group-card → list-row |
| `public/assets/css/pages/sites.css` | Modify | `.site-card` → favicon-блок, `.group-nav` видалити, sync-dot рядок |
| `public/assets/css/components/cards.css` | Modify | `.stat-card` перевірити під нові токени |
| `public/assets/js/layout.js` | Modify | Додати `initClientSearch()`, адаптувати `initViewToggle` для груп |
| `resources/views/admin/site-groups/index.blade.php` | Modify | Нові controls + list/grid markup |
| `resources/views/admin/sites/index.blade.php` | Modify | Нові controls + видалити group-nav + нові рядки + client search |
| `resources/views/admin/sites/show.blade.php` | Modify | Sidebar layout |
| `resources/views/admin/site-groups/show.blade.php` | Modify | Toolbar оновити (site-groups show має toolbar — перевірити) |

---

## Task 1: Git — нова гілка

**Files:** (git only)

- [ ] **Створити гілку**

```bash
git checkout main
git pull origin main
git checkout -b feature/task-ui-redesign
```

- [ ] **Перевірити стан**

```bash
git status
docker-compose ps
```

Очікувано: чистий стан, контейнери `php`, `nginx`, `mysql` — Up.

---

## Task 2: TG Dark — оновити tokens.css

**Files:**
- Modify: `public/assets/css/tokens.css`

- [ ] **Відкрити файл і замінити блок dark-theme токенів**

Замінити весь блок `:root { ... }` (dark-theme частину) на:

```css
:root {
    /* Radius */
    --radius-card: 24px;
    --radius-pill: 12px;
    --radius-item: 10px;
    --radius-input: 8px;

    /* Spacing */
    --space-xs:  4px;
    --space-sm:  8px;
    --space-md: 16px;
    --space-lg: 24px;
    --space-xl: 40px;

    /* Typography */
    --font-sans: 'Inter', system-ui, -apple-system, sans-serif;
    --font-size-xs:   11px;
    --font-size-sm:   13px;
    --font-size-base: 14px;
    --font-size-md:   16px;
    --font-size-lg:   20px;
    --font-size-xl:   28px;

    /* Status dots — незмінні між темами */
    --dot-ok:    #48bb78;
    --dot-pause: #ed8936;
    --dot-off:   #f56565;

    /* Transitions */
    --ease-ui: cubic-bezier(0.4, 0, 0.2, 1);
    --duration-fast: 150ms;
    --duration-base: 250ms;

    /* Drawer */
    --drawer-width:       440px;
    --drawer-width-batch: 600px;

    /* Rail — завжди темний */
    --rail-width: 60px;
    --rail-bg:    #0e1621;

    /* Dark theme (default) — TG Dark */
    --bg-page:        #17212b;
    --bg-card:        #242f3d;
    --bg-card2:       #1e2a38;
    --bg-input:       #1e2a38;
    --bg-hover:       #2a3a4d;

    --text-primary:   #ffffff;
    --text-secondary: #a0b4c4;
    --text-muted:     #708499;
    --text-inverse:   #ffffff;

    --border-color:   #2b3c4e;
    --border-focus:   #5288c1;

    --accent:         #5288c1;
    --accent-hover:   #3d6fa8;

    --shadow-card:   0 1px 3px rgba(0,0,0,.4), 0 4px 16px rgba(0,0,0,.3);
    --shadow-drawer: -4px 0 32px rgba(0,0,0,.5);
}

[data-theme="light"] {
    --bg-page:        #ebedf1;
    --bg-card:        #ffffff;
    --bg-card2:       #f5f6f8;
    --bg-input:       #f5f6f8;
    --bg-hover:       #f0f1f4;

    --text-primary:   #111827;
    --text-secondary: #6b7280;
    --text-muted:     #9ca3af;
    --text-inverse:   #ffffff;

    --border-color:   #e5e7eb;
    --border-focus:   #3d6fa8;

    --accent:         #3d6fa8;
    --accent-hover:   #2d5a8a;

    --shadow-card:   0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.04);
    --shadow-drawer: -4px 0 32px rgba(0,0,0,.12);
}
```

- [ ] **Перевірити візуально**

Відкрити http://localhost:8082 → логін → дашборд.  
Очікувано: фон сторінки `#17212b` (темно-синій), картки `#242f3d`, акцент синій `#5288c1`.

- [ ] **Commit**

```bash
git add public/assets/css/tokens.css
git commit -m "style(tokens): TG Dark color scheme — #17212b bg, #5288c1 accent"
git push origin feature/task-ui-redesign
```

---

## Task 3: Client-side пошук — додати initClientSearch() до layout.js

**Files:**
- Modify: `public/assets/js/layout.js`

**Проблема:** поточний пошук викликає `applyQueryParam('search', v)` → `window.location` → reload → фокус зникає.  
**Рішення:** нова функція `initClientSearch(inputId, itemsSelector, searchAttr)` — фільтрує DOM без навігації.

- [ ] **Додати функцію в кінець layout.js**

```js
// Client-side search — filters DOM rows without page reload
// inputId:       id of the <input> element
// itemsSelector: CSS selector for filterable rows (e.g. '.site-card', '.group-row')
// searchAttr:    data-attribute that holds searchable text (default: 'data-searchable')
function initClientSearch(inputId, itemsSelector, searchAttr) {
    searchAttr = searchAttr || 'data-searchable';
    var input = document.getElementById(inputId);
    if (!input) return;

    // Restore focus if search value came from URL (back-navigation)
    if (input.value) input.focus();

    input.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll(itemsSelector).forEach(function (el) {
            var text = (el.getAttribute(searchAttr) || el.textContent).toLowerCase();
            el.style.display = text.includes(q) ? '' : 'none';
        });
    });
}
```

- [ ] **Перевірити синтаксис** (відкрити http://localhost:8082, перевірити консоль браузера — помилок немає)

- [ ] **Commit**

```bash
git add public/assets/js/layout.js
git commit -m "feat(js): initClientSearch — client-side DOM filtering, fixes search focus loss"
git push origin feature/task-ui-redesign
```

---

## Task 4: Page Controls CSS — новий стиль (shared, site-groups.css)

**Files:**
- Modify: `public/assets/css/pages/site-groups.css`

Замінити блок `/* ─── Page controls bar ─── */` (рядки 1–55 приблизно) на новий 2-рядковий стиль.

- [ ] **Замінити старі `.page-controls` стилі на нові**

Знайти і замінити весь блок від `.page-controls {` до `.page-controls__select:focus { ... }` включно:

```css
/* ─── Page controls bar — shared across all list pages ─── */
.page-controls {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    margin-bottom: var(--space-md);
}

/* Row 1: search + count */
.page-controls__search-row {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.page-controls__search {
    position: relative;
    flex: 1;
}

.page-controls__search svg {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    pointer-events: none;
}

.page-controls__search-input {
    width: 100%;
    height: 36px;
    border-radius: var(--radius-item);
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    font-size: var(--font-size-sm);
    padding: 0 var(--space-md) 0 34px;
    outline: none;
    transition: border-color var(--duration-fast) var(--ease-ui);
}

.page-controls__search-input:focus {
    border-color: var(--border-focus);
}

.page-controls__count {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    white-space: nowrap;
    flex-shrink: 0;
}

/* Row 2: filter pills + sort */
.page-controls__pills {
    display: flex;
    align-items: center;
    gap: 5px;
    flex-wrap: wrap;
}

.filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: var(--font-size-xs);
    font-weight: 500;
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    background: transparent;
    white-space: nowrap;
    cursor: pointer;
    text-decoration: none;
    transition: background var(--duration-fast) var(--ease-ui),
                color var(--duration-fast) var(--ease-ui),
                border-color var(--duration-fast) var(--ease-ui);
}

.filter-pill:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.filter-pill.is-active {
    background: var(--accent);
    color: #fff;
    border-color: var(--accent);
}

.filter-pill__dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

.filter-pill__count {
    font-size: 9px;
    opacity: .7;
    background: rgba(255,255,255,.12);
    border-radius: 8px;
    padding: 1px 5px;
}

.filter-pill.is-active .filter-pill__count {
    background: rgba(255,255,255,.2);
}

.filter-pill-sep {
    width: 1px;
    height: 16px;
    background: var(--border-color);
    margin: 0 3px;
    flex-shrink: 0;
    align-self: center;
}

.page-controls__sort {
    margin-left: auto;
    height: 28px;
    border-radius: var(--radius-input);
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    font-size: var(--font-size-xs);
    padding: 0 24px 0 var(--space-sm);
    appearance: none;
    cursor: pointer;
    flex-shrink: 0;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%23708499' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 6px center;
    outline: none;
}

.page-controls__sort:focus {
    border-color: var(--border-focus);
}
```

- [ ] **Перевірити візуально** — відкрити http://localhost:8082/site-groups.  
Очікувано: 2-рядкові controls з'являються (поки без pills — вони додаються в Task 5).

- [ ] **Commit**

```bash
git add public/assets/css/pages/site-groups.css
git commit -m "style(controls): 2-row page-controls — search row + filter pills row"
git push origin feature/task-ui-redesign
```

---

## Task 5: Групи — list-рядки + view toggle

**Files:**
- Modify: `public/assets/css/pages/site-groups.css` (додати стилі group-row)
- Modify: `resources/views/admin/site-groups/index.blade.php`

### 5a — CSS для group-row та групового view-toggle

- [ ] **Додати в кінець site-groups.css** (після існуючих стилів):

```css
/* ─── Group list row ─── */
.group-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    margin-bottom: var(--space-lg);
}

.group-row {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    background: var(--bg-card);
    border-radius: var(--radius-pill);
    padding: var(--space-sm) var(--space-md);
    box-shadow: var(--shadow-card);
    cursor: pointer;
    transition: box-shadow var(--duration-fast) var(--ease-ui);
}

.group-row:hover {
    box-shadow: var(--shadow-card), 0 0 0 1px var(--accent);
}

.group-row__icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-item);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-md);
    font-weight: 700;
    flex-shrink: 0;
}

.group-row__info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.group-row__name {
    font-size: var(--font-size-base);
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.group-row__desc {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.group-row__sites {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-shrink: 0;
}

.group-row__site-chip {
    font-size: 9px;
    padding: 2px 7px;
    border-radius: 6px;
    background: rgba(255,255,255,.06);
    color: var(--text-muted);
    white-space: nowrap;
}

.group-row__count {
    font-size: var(--font-size-lg);
    font-weight: 700;
    color: var(--text-primary);
    flex-shrink: 0;
    min-width: 32px;
    text-align: right;
}

.group-row__actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
    opacity: 0;
    transition: opacity var(--duration-fast) var(--ease-ui);
}

.group-row:hover .group-row__actions { opacity: 1; }

/* Grid mode — toggle */
.group-list--grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: var(--space-md);
}

.group-list--grid .group-row {
    flex-direction: column;
    align-items: flex-start;
    border-radius: var(--radius-card);
    padding: var(--space-lg);
    gap: var(--space-sm);
}

.group-list--grid .group-row__actions { opacity: 1; }
.group-list--grid .group-row__sites { flex-wrap: wrap; }
.group-list--grid .group-row__count {
    font-size: var(--font-size-xl);
    text-align: left;
}
```

### 5b — Оновити Blade шаблон груп

- [ ] **Замінити вміст `resources/views/admin/site-groups/index.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Групи сайтів')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Групи сайтів</h1>
    <div style="display:flex;align-items:center;gap:var(--space-sm);">
        <div class="view-toggle">
            <button id="btn-view-list" class="view-toggle__btn is-active" title="Список">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
            <button id="btn-view-grid" class="view-toggle__btn" title="Сітка">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </button>
        </div>
        <button class="btn-primary" onclick="openDrawer('drawer-group-create')">+ Нова група</button>
    </div>
</div>

{{-- Controls bar --}}
<div class="page-controls">
    <div class="page-controls__search-row">
        <div class="page-controls__search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="page-controls__search-input"
                   placeholder="Пошук груп…"
                   value="{{ request('search') }}" id="group-search">
        </div>
        <span class="page-controls__count">{{ $groups->total() }} груп</span>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($groups->isEmpty())
    <div class="empty-page"><p>Груп ще немає. Натисніть «+ Нова група» щоб розпочати.</p></div>
@else
    <div class="group-list" id="groups-list">
        @foreach($groups as $group)
        @php
            $sites = $group->sites->take(3);
            $extra = $group->sites_count - $sites->count();
            $colorHex = $group->color ?? '#708499';
            $letter = strtoupper(substr($group->name, 0, 1));
        @endphp
        <div class="group-row"
             data-searchable="{{ $group->name }} {{ $group->description }}"
             onclick="window.location='{{ route('site-groups.show', $group) }}'">
            <div class="group-row__icon"
                 style="background:{{ $colorHex }}26;color:{{ $colorHex }};">
                {{ $letter }}
            </div>
            <div class="group-row__info">
                <span class="group-row__name">{{ $group->name }}</span>
                @if($group->description)
                    <span class="group-row__desc">{{ Str::limit($group->description, 60) }}</span>
                @endif
            </div>
            <div class="group-row__sites" onclick="event.stopPropagation()">
                @foreach($sites as $site)
                    <span class="group-row__site-chip">{{ parse_url($site->url, PHP_URL_HOST) ?: $site->url }}</span>
                @endforeach
                @if($extra > 0)
                    <span class="group-row__site-chip">+{{ $extra }}</span>
                @endif
            </div>
            <span class="group-row__count">{{ $group->sites_count }}</span>
            <div class="group-row__actions" onclick="event.stopPropagation()">
                <button class="btn-icon" title="Редагувати"
                        onclick="openDrawer('drawer-group-{{ $group->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>
    <div class="pagination-wrap">{{ $groups->links() }}</div>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-group-create-overlay" onclick="closeDrawer('drawer-group-create')"></div>
<div class="drawer" id="drawer-group-create">
    <div class="drawer__header">
        <span class="drawer__title">Нова група</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-group-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('site-groups.store') }}" class="form-stack" id="form-group-create">
            @csrf
            @include('admin.site-groups._form', ['group' => null])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-create')">Скасувати</button>
        <button type="submit" form="form-group-create" class="btn-primary">Створити</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($groups as $group)
<div class="drawer-overlay" id="drawer-group-{{ $group->id }}-overlay" onclick="closeDrawer('drawer-group-{{ $group->id }}')"></div>
<div class="drawer" id="drawer-group-{{ $group->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $group->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-group-{{ $group->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST"
              action="{{ route('site-groups.update', $group) }}"
              class="form-stack"
              id="form-group-{{ $group->id }}">
            @csrf @method('PUT')
            @include('admin.site-groups._form', ['group' => $group])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('site-groups.destroy', $group) }}" class="drawer__footer-left">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити групу «{{ $group->name }}»?')">Видалити</button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-{{ $group->id }}')">Скасувати</button>
        <button type="submit" form="form-group-{{ $group->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initViewToggle('groups-view', 'groups-list', 'btn-view-list', 'btn-view-grid');
    // Замінюємо клас для груп (grid використовує group-list--grid)
    (function() {
        var list = document.getElementById('groups-list');
        if (!list) return;
        var saved = localStorage.getItem('groups-view') || 'list';
        if (saved === 'grid') list.classList.add('group-list--grid');
        document.getElementById('btn-view-list').addEventListener('click', function() {
            list.classList.remove('group-list--grid');
        });
        document.getElementById('btn-view-grid').addEventListener('click', function() {
            list.classList.add('group-list--grid');
        });
    })();

    initClientSearch('group-search', '.group-row');
</script>
@endpush

@endsection
```

**Увага:** контролер `SiteGroupController@index` має eager-load `sites` (перші 3) і `sites_count`. Перевірити:

```bash
docker-compose exec php php artisan tinker
# App\Models\SiteGroup::with(['sites'])->withCount('sites')->first()
```

Якщо `sites` не eager-loaded — додати в контролер:

```php
// app/Http/Controllers/Admin/SiteGroupController.php — метод index()
$groups = SiteGroup::withCount('sites')
    ->with('sites:id,site_group_id,url')  // додати цей рядок
    ->orderBy('name')
    ->paginate(20);
```

- [ ] **Перевірити візуально** — http://localhost:8082/site-groups.  
Очікувано: список рядків з іконка-блоком, назвою, URL chips, лічильником. Toggle list↔grid працює.

- [ ] **Перевірити пошук** — набрати текст в пошуку.  
Очікувано: рядки фільтруються, фокус НЕ губиться.

- [ ] **Commit**

```bash
git add public/assets/css/pages/site-groups.css
git add resources/views/admin/site-groups/index.blade.php
git add app/Http/Controllers/Admin/SiteGroupController.php  # якщо змінювалось
git commit -m "feat(groups): list-row layout + view toggle + client-side search"
git push origin feature/task-ui-redesign
```

---

## Task 6: Сайти — нові рядки з favicon-блоком + видалити group-nav

**Files:**
- Modify: `public/assets/css/pages/sites.css`
- Modify: `resources/views/admin/sites/index.blade.php`

### 6a — Оновити sites.css

- [ ] **Замінити `.site-card` і видалити `.group-nav` стилі**

Знайти блок `/* Group nav bar */` і весь `.group-nav*` CSS — **видалити**.

Замінити блок `/* Site card */` на:

```css
/* ─── Sites list ─── */
.sites-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    margin-bottom: var(--space-lg);
}

.sites-list--grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: var(--space-md);
}

/* Site card — list row */
.site-card {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    background: var(--bg-card);
    border-radius: var(--radius-pill);
    box-shadow: var(--shadow-card);
    padding: var(--space-sm) var(--space-md);
    cursor: pointer;
    transition: box-shadow var(--duration-fast) var(--ease-ui);
}

.site-card:hover {
    box-shadow: var(--shadow-card), 0 0 0 1px var(--accent);
}

.site-card--disabled {
    opacity: 0.6;
}

/* Favicon block */
.site-card__favicon {
    width: 36px;
    height: 36px;
    border-radius: var(--radius-item);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-md);
    font-weight: 700;
    flex-shrink: 0;
    border: 1px solid transparent;
}

/* Info: 2 рядки */
.site-card__info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.site-card__name-row {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.site-card__name {
    font-weight: 600;
    font-size: var(--font-size-base);
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Second line: URL + sync dot + sync time */
.site-card__meta-row {
    display: flex;
    align-items: center;
    gap: 5px;
}

.site-card__url {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.site-card__meta-sep {
    color: var(--border-color);
    font-size: var(--font-size-xs);
    flex-shrink: 0;
}

.site-card__sync-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    flex-shrink: 0;
}

.site-card__sync-time {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    white-space: nowrap;
    flex-shrink: 0;
}

/* Right side */
.site-card__group { flex-shrink: 0; }

.site-card__date {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    white-space: nowrap;
    flex-shrink: 0;
}

.site-card__actions {
    display: flex;
    align-items: center;
    gap: 2px;
    flex-shrink: 0;
    opacity: 0;
    transition: opacity var(--duration-fast) var(--ease-ui);
}

.site-card:hover .site-card__actions { opacity: 1; }

/* Grid mode */
.sites-list--grid .site-card {
    flex-direction: column;
    align-items: flex-start;
    border-radius: var(--radius-card);
    padding: var(--space-lg);
    gap: var(--space-sm);
}

.sites-list--grid .site-card__actions { opacity: 1; }
.sites-list--grid .site-card__info { width: 100%; }
.sites-list--grid .site-card__group { margin-top: auto; }
```

### 6b — Оновити Blade шаблон сайтів

- [ ] **Замінити вміст `resources/views/admin/sites/index.blade.php`**

```blade
@extends('layouts.app')

@section('title', 'Сайти')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Сайти</h1>
    <div style="display:flex;align-items:center;gap:var(--space-sm);">
        <div class="view-toggle">
            <button id="btn-view-list" class="view-toggle__btn is-active" title="Список">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
            <button id="btn-view-grid" class="view-toggle__btn" title="Сітка">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </button>
        </div>
        <button class="btn-primary" onclick="openDrawer('drawer-site-create')">+ Новий сайт</button>
    </div>
</div>

{{-- Controls bar --}}
<div class="page-controls">
    <div class="page-controls__search-row">
        <div class="page-controls__search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="page-controls__search-input"
                   placeholder="Пошук сайтів…"
                   value="{{ request('search') }}" id="site-search">
        </div>
        <span class="page-controls__count">{{ $sites->total() }} сайтів</span>
    </div>
    <div class="page-controls__pills">
        <a href="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}"
           class="filter-pill {{ !request('status') ? 'is-active' : '' }}">
            Всі <span class="filter-pill__count">{{ $totalCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'active', 'page' => null]) }}"
           class="filter-pill {{ request('status') === 'active' ? 'is-active' : '' }}">
            <span class="filter-pill__dot" style="background:var(--dot-ok)"></span>
            Active <span class="filter-pill__count">{{ $activeCount }}</span>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive', 'page' => null]) }}"
           class="filter-pill {{ request('status') === 'inactive' ? 'is-active' : '' }}">
            <span class="filter-pill__dot" style="background:var(--dot-off)"></span>
            Disabled <span class="filter-pill__count">{{ $inactiveCount }}</span>
        </a>
        @if($groups->isNotEmpty())
            <div class="filter-pill-sep"></div>
            @foreach($groups as $group)
            <a href="{{ request()->fullUrlWithQuery(['group_id' => $group->id, 'page' => null]) }}"
               class="filter-pill {{ request('group_id') == $group->id ? 'is-active' : '' }}">
                <span class="filter-pill__dot" style="background:{{ $group->color ?? '#708499' }}"></span>
                {{ $group->name }}
            </a>
            @endforeach
            @if(request('group_id'))
            <a href="{{ request()->fullUrlWithQuery(['group_id' => null, 'page' => null]) }}"
               class="filter-pill">✕ Очистити</a>
            @endif
        @endif
        <select class="page-controls__sort" onchange="applyQueryParam('sort', this.value)">
            <option value="date"   {{ request('sort', 'date') === 'date'   ? 'selected' : '' }}>За датою ↓</option>
            <option value="name"   {{ request('sort', 'date') === 'name'   ? 'selected' : '' }}>За назвою A→Z</option>
            <option value="status" {{ request('sort', 'date') === 'status' ? 'selected' : '' }}>За статусом</option>
            <option value="group"  {{ request('sort', 'date') === 'group'  ? 'selected' : '' }}>За групою</option>
        </select>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($sites->isEmpty())
    <div class="empty-page"><p>Сайтів не знайдено.</p></div>
@else
    <div class="sites-list" id="sites-list">
        @foreach($sites as $site)
        @php
            $color = $site->siteGroup?->color ?? '#708499';
            $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
            $syncOk = $site->latestSyncLog?->status === 'success';
            $syncWarn = $site->latestSyncLog && !$syncOk;
            $syncDot = $syncOk ? 'var(--dot-ok)' : ($syncWarn ? 'var(--dot-pause)' : 'var(--text-muted)');
            $syncTime = $site->latestSyncLog?->created_at?->diffForHumans() ?? null;
        @endphp
        <div class="site-card {{ !$site->is_active ? 'site-card--disabled' : '' }}"
             data-searchable="{{ $site->name }} {{ $site->url }} {{ $site->siteGroup?->name }}"
             onclick="window.location='{{ route('sites.show', $site) }}'">
            <div class="site-card__favicon"
                 style="background:{{ $color }}26;color:{{ $color }};">
                {{ $letter }}
            </div>
            <div class="site-card__info">
                <div class="site-card__name-row">
                    <span class="site-card__name">{{ $site->name }}</span>
                    <span class="status-badge status-badge--{{ $site->is_active ? 'active' : 'disabled' }}">
                        <span class="status-badge__dot"></span>{{ $site->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </div>
                <div class="site-card__meta-row">
                    <span class="site-card__url">{{ $site->url }}</span>
                    @if($syncTime)
                        <span class="site-card__meta-sep">·</span>
                        <span class="site-card__sync-dot" style="background:{{ $syncDot }}"></span>
                        <span class="site-card__sync-time">{{ $syncTime }}</span>
                    @endif
                </div>
            </div>
            <div class="site-card__group">
                @if($site->siteGroup)
                    <span class="group-pill" style="--pill-color:{{ $color }}">
                        {{ $site->siteGroup->name }}
                    </span>
                @endif
            </div>
            <span class="site-card__date">{{ $site->created_at->format('d.m.Y') }}</span>
            <div class="site-card__actions" onclick="event.stopPropagation()">
                <a href="{{ $site->url }}" target="_blank" class="btn-icon" title="Відкрити">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                        <polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                </a>
                <button class="btn-icon" title="Редагувати"
                        onclick="openDrawer('drawer-site-{{ $site->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
            </div>
        </div>
        @endforeach
    </div>
    <div class="pagination-wrap">{{ $sites->links() }}</div>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-site-create-overlay" onclick="closeDrawer('drawer-site-create')"></div>
<div class="drawer" id="drawer-site-create">
    <div class="drawer__header">
        <span class="drawer__title">Новий сайт</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.store') }}" class="form-stack" id="form-site-create">
            @csrf
            @include('admin.sites._form', ['site' => null, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-create')">Скасувати</button>
        <button type="submit" form="form-site-create" class="btn-primary">Додати</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($sites as $site)
<div class="drawer-overlay" id="drawer-site-{{ $site->id }}-overlay" onclick="closeDrawer('drawer-site-{{ $site->id }}')"></div>
<div class="drawer" id="drawer-site-{{ $site->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $site->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-{{ $site->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.update', $site) }}" class="form-stack" id="form-site-{{ $site->id }}">
            @csrf @method('PUT')
            @include('admin.sites._form', ['site' => $site, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('sites.destroy', $site) }}" class="drawer__footer-left">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити сайт «{{ $site->name }}»?')">Видалити</button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-{{ $site->id }}')">Скасувати</button>
        <button type="submit" form="form-site-{{ $site->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initViewToggle('sites-view', 'sites-list', 'btn-view-list', 'btn-view-grid');
    initClientSearch('site-search', '.site-card');
</script>
@endpush

@endsection
```

### 6c — Оновити контролер SitesController@index

Потрібні: `$totalCount`, `$activeCount`, `$inactiveCount`, `$groups` (для pills), `latestSyncLog` (для sync-dot).

- [ ] **Відкрити `app/Http/Controllers/Admin/SiteController.php`**, знайти метод `index()` і оновити:**

```php
public function index(Request $request): View
{
    $query = Site::with(['siteGroup', 'latestSyncLog'])
        ->orderBy(match($request->get('sort', 'date')) {
            'name'   => 'name',
            'status' => 'is_active',
            default  => 'created_at',
        }, match($request->get('sort', 'date')) {
            'name' => 'asc',
            default => 'desc',
        });

    if ($request->filled('group_id')) {
        $query->where('site_group_id', $request->get('group_id'));
    }

    if ($request->filled('status')) {
        $query->where('is_active', $request->get('status') === 'active');
    }

    if ($request->filled('search')) {
        $search = $request->get('search');
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('url', 'like', "%{$search}%");
        });
    }

    // Counts for pills
    $totalCount    = Site::count();
    $activeCount   = Site::where('is_active', true)->count();
    $inactiveCount = Site::where('is_active', false)->count();

    $sites  = $query->paginate(20)->withQueryString();
    $groups = SiteGroup::withCount('sites')->orderBy('name')->get();

    return view('admin.sites.index', compact(
        'sites', 'groups', 'totalCount', 'activeCount', 'inactiveCount'
    ));
}
```

**Якщо `latestSyncLog` relationship не існує** в моделі `Site` — додати:

```php
// app/Models/Site.php
public function latestSyncLog(): HasOne
{
    return $this->hasOne(SyncLog::class)->latestOfMany();
}
```

- [ ] **Перевірити** — http://localhost:8082/sites.  
Очікувано: favicon-блок, 2-рядкові картки, pills з кількостями, group-nav зник, пошук без reload.

- [ ] **Commit**

```bash
git add public/assets/css/pages/sites.css
git add resources/views/admin/sites/index.blade.php
git add app/Http/Controllers/Admin/SiteController.php
git add app/Models/Site.php  # якщо додавали latestSyncLog
git commit -m "feat(sites): favicon-block rows, sync-dot, client-side search, pills controls"
git push origin feature/task-ui-redesign
```

---

## Task 7: Сторінка сайту — sidebar layout

**Files:**
- Modify: `resources/views/admin/sites/show.blade.php`
- Modify: `public/assets/css/pages/sites.css` (додати sidebar стилі)

### 7a — CSS для sidebar

- [ ] **Додати в кінець sites.css:**

```css
/* ─── Site show — sidebar layout ─── */
.site-show {
    display: flex;
    gap: 0;
    background: var(--bg-card);
    border-radius: var(--radius-card);
    box-shadow: var(--shadow-card);
    overflow: hidden;
    min-height: 520px;
}

.site-show__sidebar {
    width: 220px;
    flex-shrink: 0;
    border-right: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    padding: var(--space-lg);
    gap: var(--space-md);
}

.site-show__favicon {
    width: 44px;
    height: 44px;
    border-radius: var(--radius-item);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: var(--font-size-lg);
    font-weight: 700;
    flex-shrink: 0;
}

.site-show__name {
    font-size: var(--font-size-md);
    font-weight: 700;
    color: var(--text-primary);
    word-break: break-word;
}

.site-show__url {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    word-break: break-all;
}

.site-show__info {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    padding-top: var(--space-sm);
    border-top: 1px solid var(--border-color);
}

.site-show__info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--space-sm);
}

.site-show__info-label {
    font-size: var(--font-size-xs);
    color: var(--text-muted);
    flex-shrink: 0;
}

.site-show__info-val {
    font-size: var(--font-size-xs);
    font-weight: 500;
    color: var(--text-primary);
    text-align: right;
}

.site-show__nav {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding-top: var(--space-sm);
    border-top: 1px solid var(--border-color);
    margin-top: auto;
}

.site-show__nav-item {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: 7px var(--space-sm);
    border-radius: var(--radius-item);
    font-size: var(--font-size-sm);
    font-weight: 500;
    color: var(--text-muted);
    text-decoration: none;
    transition: background var(--duration-fast) var(--ease-ui),
                color var(--duration-fast) var(--ease-ui);
    cursor: pointer;
}

.site-show__nav-item:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.site-show__nav-item.is-active {
    background: color-mix(in srgb, var(--accent) 15%, transparent);
    color: var(--accent);
}

.site-show__nav-count {
    margin-left: auto;
    font-size: var(--font-size-xs);
    background: var(--bg-card);
    border-radius: 8px;
    padding: 1px 6px;
    color: var(--text-muted);
}

.site-show__content {
    flex: 1;
    padding: var(--space-lg);
    overflow-y: auto;
}

.site-show__content-title {
    font-size: var(--font-size-md);
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: var(--space-md);
}

.site-show__placeholder {
    color: var(--text-muted);
    font-size: var(--font-size-sm);
    text-align: center;
    padding: var(--space-xl) 0;
}
```

### 7b — Оновити Blade show.blade.php

- [ ] **Замінити вміст `resources/views/admin/sites/show.blade.php`:**

```blade
@extends('layouts.app')

@section('title', $site->name)

@section('content')

<div class="page-toolbar">
    <div style="display:flex;align-items:center;gap:var(--space-md);">
        <a href="{{ route('sites.index') }}" class="btn-ghost">← Сайти</a>
        <span class="status-dot status-dot--{{ $site->is_active ? 'ok' : 'off' }}"></span>
        <h1 class="page-title">{{ $site->name }}</h1>
    </div>
    <div style="display:flex;gap:var(--space-sm);">
        <a href="{{ $site->url }}" target="_blank" class="btn-ghost">↗ Відкрити</a>
        <button class="btn-primary" onclick="openDrawer('drawer-site-edit')">Редагувати</button>
    </div>
</div>

@php
    $color  = $site->siteGroup?->color ?? '#708499';
    $letter = strtoupper(substr(parse_url($site->url, PHP_URL_HOST) ?: $site->name, 0, 1));
    $syncLog  = $site->latestSyncLog;
    $syncOk   = $syncLog?->status === 'success';
    $syncWarn = $syncLog && !$syncOk;
    $syncColor = $syncOk ? 'var(--dot-ok)' : ($syncWarn ? 'var(--dot-pause)' : 'var(--text-muted)');
@endphp

<div class="site-show">
    {{-- Sidebar --}}
    <div class="site-show__sidebar">
        <div class="site-show__favicon"
             style="background:{{ $color }}26;color:{{ $color }};">
            {{ $letter }}
        </div>
        <div>
            <div class="site-show__name">{{ $site->name }}</div>
            <div class="site-show__url">{{ $site->url }}</div>
        </div>

        <div class="site-show__info">
            <div class="site-show__info-row">
                <span class="site-show__info-label">Статус</span>
                <span class="site-show__info-val"
                      style="color:{{ $site->is_active ? 'var(--dot-ok)' : 'var(--dot-off)' }}">
                    ● {{ $site->is_active ? 'Active' : 'Disabled' }}
                </span>
            </div>
            <div class="site-show__info-row">
                <span class="site-show__info-label">Група</span>
                <span class="site-show__info-val">{{ $site->siteGroup?->name ?? '—' }}</span>
            </div>
            @if($syncLog)
            <div class="site-show__info-row">
                <span class="site-show__info-label">Sync</span>
                <span class="site-show__info-val" style="color:{{ $syncColor }}">
                    {{ $syncLog->created_at->diffForHumans() }}
                </span>
            </div>
            @endif
            <div class="site-show__info-row">
                <span class="site-show__info-label">Додано</span>
                <span class="site-show__info-val">{{ $site->created_at->format('d.m.Y') }}</span>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="site-show__nav">
            <a class="site-show__nav-item is-active" href="#">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 8.91a16 16 0 0 0 6 6l.72-.72a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.5 16a2 2 0 0 1 .42.92z"/>
                </svg>
                Телефони
                <span class="site-show__nav-count">—</span>
            </a>
            <a class="site-show__nav-item" href="#">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                Ціни
                <span class="site-show__nav-count">—</span>
            </a>
            <a class="site-show__nav-item" href="#">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Адреси
                <span class="site-show__nav-count">—</span>
            </a>
            <a class="site-show__nav-item" href="#">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23 4 23 10 17 10"/>
                    <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                </svg>
                Sync-логи
            </a>
        </nav>
    </div>

    {{-- Content --}}
    <div class="site-show__content">
        <div class="site-show__content-title">Телефони</div>
        <p class="site-show__placeholder">
            Вкладки з даними (телефони, ціни, адреси) — будуть доступні в задачі L011
        </p>
    </div>
</div>

{{-- Edit drawer --}}
<div class="drawer-overlay" id="drawer-site-edit-overlay" onclick="closeDrawer('drawer-site-edit')"></div>
<div class="drawer" id="drawer-site-edit">
    <div class="drawer__header">
        <span class="drawer__title">{{ $site->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-edit')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.update', $site) }}" class="form-stack" id="form-site-edit">
            @csrf @method('PUT')
            @include('admin.sites._form', ['site' => $site, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('sites.destroy', $site) }}" class="drawer__footer-left">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити сайт «{{ $site->name }}»?')">Видалити</button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-edit')">Скасувати</button>
        <button type="submit" form="form-site-edit" class="btn-primary">Зберегти</button>
    </div>
</div>

@endsection
```

- [ ] **Перевірити** — http://localhost:8082/sites → клік на будь-який сайт.  
Очікувано: sidebar зліва з favicon, назвою, URL, статусом, sync, датою і вертикальною навігацією. Праворуч — placeholder.

- [ ] **Commit**

```bash
git add public/assets/css/pages/sites.css
git add resources/views/admin/sites/show.blade.php
git commit -m "feat(site-show): sidebar layout — favicon, info, vertical nav, content area"
git push origin feature/task-ui-redesign
```

---

## Task 8: Фінальна перевірка + оновити MEMORY.md

- [ ] **Пройтись по всіх сторінках**

| URL | Що перевіряти |
|---|---|
| http://localhost:8082 | Дашборд: TG Dark кольори, stat-cards, лог |
| http://localhost:8082/site-groups | List-рядки, view-toggle, пошук (фокус не губиться) |
| http://localhost:8082/sites | Favicon-блок, sync-dot, pills з кількостями, group-nav зник |
| http://localhost:8082/sites/{id} | Sidebar, навігація, drawer редагування |
| http://localhost:8082/users | Без регресій |
| http://localhost:8082/logs | Без регресій |
| Toggle theme (☀/🌙) | Світла тема відображається коректно |

- [ ] **Оновити MEMORY.md**

```markdown
## 📍 Поточний стан
- **Активна гілка:** `feature/task-ui-redesign` (запушено)
- **Наступна задача:** TASK-L010 — API Keys (generate + revoke per site)
- **Виконано:** UI Redesign — TG Dark, controls pills, groups list-row, sites favicon-block, site-show sidebar
```

- [ ] **Фінальний commit**

```bash
git add MEMORY.md
git commit -m "docs: update MEMORY.md after UI redesign"
git push origin feature/task-ui-redesign
```

---

## Self-Review

**Spec coverage:**
- ✅ TG Dark tokens → Task 2
- ✅ Client-side search bug fix → Task 3
- ✅ Page controls 2-row + pills → Task 4
- ✅ Groups list-row + view toggle → Task 5
- ✅ Sites favicon-block + sync-dot + disabled opacity → Task 6
- ✅ Site show sidebar layout → Task 7
- ✅ Group-nav видалено → Task 6b (blade) + 6a (CSS)

**Placeholders:** відсутні — весь код наведено повністю.

**Type consistency:**
- `initClientSearch` визначено в Task 3, використовується в Task 5 і 6 ✅
- `initViewToggle` — існуюча функція з layout.js ✅
- `.site-card--disabled` визначено в Task 6a, застосовується в Task 6b ✅
- `.site-show__*` визначено в Task 7a, використовується в Task 7b ✅
- `$totalCount`, `$activeCount`, `$inactiveCount` передаються з контролера в Task 6c ✅
- `latestSyncLog` relationship додається в Task 6c, використовується в Task 6b і 7b ✅
