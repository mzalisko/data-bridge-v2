# UI Redesign — Design Spec
**Date:** 2026-04-11  
**Status:** Approved  
**Scope:** Color scheme, page-controls, groups page, sites page, site show page

---

## 1. Color Scheme — TG Dark

Replace current Deep Dark tokens with TG Dark palette.

```css
/* Dark theme (default) */
--bg-page:        #17212b;
--bg-card:        #242f3d;
--bg-card2:       #1e2a38;   /* topbar, controls bg */
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

--shadow-card:    0 1px 3px rgba(0,0,0,.4), 0 4px 16px rgba(0,0,0,.3);
--shadow-drawer:  -4px 0 32px rgba(0,0,0,.5);

/* Rail stays black */
--rail-bg:        #0e1621;

/* Light theme unchanged — existing tokens */
```

Status dot colors unchanged: `--dot-ok: #48bb78`, `--dot-pause: #ed8936`, `--dot-off: #f56565`.

---

## 2. Page Controls — Search + Pills (all pages)

**Problem fixed:** search input loses focus because `applyQueryParam` triggers full page reload.  
**Solution:** replace server-side search with client-side DOM filtering. Filter fires on `input` event, no navigation.

### Markup structure (replaces current `.page-controls`)

```html
<div class="page-controls">
  <!-- Row 1: search + count -->
  <div class="page-controls__search-row">
    <div class="page-controls__search">
      <svg><!-- search icon --></svg>
      <input type="text" id="search-input" placeholder="Пошук…" value="{{ request('search') }}">
    </div>
    <span class="page-controls__count">{{ $total }} записів</span>
  </div>
  <!-- Row 2: filter pills + sort -->
  <div class="page-controls__pills">
    <a class="filter-pill {{ !request('status') ? 'is-active' : '' }}" href="…">
      Всі <span class="filter-pill__count">{{ $total }}</span>
    </a>
    <a class="filter-pill …" href="…">
      <span class="filter-pill__dot" style="background:var(--dot-ok)"></span>
      Active <span class="filter-pill__count">{{ $activeCount }}</span>
    </a>
    <a class="filter-pill …" href="…">
      <span class="filter-pill__dot" style="background:var(--dot-off)"></span>
      Disabled <span class="filter-pill__count">{{ $disabledCount }}</span>
    </a>
    <!-- group pills (sites page only) -->
    @foreach($groups as $group)
    <a class="filter-pill …" href="…">{{ $group->name }}</a>
    @endforeach
    <div class="filter-pill-sep"></div>
    <select class="page-controls__sort" onchange="applyQueryParam('sort', this.value)">…</select>
  </div>
</div>
```

### Client-side search JS (replaces `applyQueryParam` for search)

```js
const searchInput = document.getElementById('search-input');
const listItems   = document.querySelectorAll('[data-searchable]');

searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    listItems.forEach(el => {
        el.style.display = el.dataset.searchable.toLowerCase().includes(q) ? '' : 'none';
    });
});

// Restore focus if search param was set on load (for back-navigation)
if (searchInput.value) searchInput.focus();
```

Each row element gets `data-searchable="name url"` with the relevant searchable text.

### CSS changes

- `.page-controls` becomes a flex column (two rows)  
- `.filter-pill` replaces `.btn-group__btn` — pill-shaped, colored dot for status pills, count badge  
- `.page-controls__sort` — compact select, `margin-left: auto`  
- `.filter-pill-sep` — 1px vertical divider between status pills and group pills  
- Remove `.group-nav` from sites page (replaced by group pills in controls)

---

## 3. Groups Page

**Layout:** list view by default, toggle to grid (same view-toggle as sites).

### List row (default)

```
[ colored icon block | Name + desc | site1.com site2.com +N | count | edit btn ]
```

- Left: `40×40` rounded block, `background: color-mix(in srgb, groupColor 15%, transparent)`, first letter of group name colored
- Center: name (bold 14px) + description (muted 11px, truncated)  
- Right side: 2–3 site URL chips (`font-size:9px`, muted bg), then count (`22px bold`), then edit icon-btn
- Hover: `box-shadow: 0 0 0 1px var(--accent)`
- Click row → `window.location = route('site-groups.show', group)`

### Grid view (toggle)

Same `group-grid` as current but with the colored icon block replacing the dot, and count displayed as `big number + label` inside the card.

### View toggle

Saved to `localStorage` key `groups-view` (same pattern as sites).

---

## 4. Sites Page

**List row:**

```
[ favicon block | Name + status badge | URL + sync dot + sync time | group pill | date | actions ]
```

- **Favicon block:** `36×36` rounded square, `background: color-mix(in srgb, groupColor 15%, transparent)`, first letter of domain, colored by group color
- **Name row:** site name (bold 14px) + status badge inline
- **Second line:** URL (muted 11px) + `·` + sync dot (6px circle, colored ok/warn/off) + sync time text
- **Disabled sites:** entire row at `opacity: 0.6`
- **Actions:** icon buttons (open, edit) — visible on hover
- **Click row** → `window.location = route('sites.show', site)`

**Grid view:** existing grid layout, adapted to new card style (favicon block top-left, name, URL, group pill, status badge, actions always visible).

**Group nav bar removed** — replaced by group pills in page-controls.

---

## 5. Site Show Page — Sidebar Layout

```
[ Toolbar: ← Сайти | status dot + name | ↗ Відкрити | Редагувати ]
┌─────────────────────┬──────────────────────────────────┐
│ SIDEBAR (220px)      │ CONTENT                          │
│ [favicon 44px]       │ [section title]                  │
│ Site name            │ [data rows]                      │
│ https://url          │                                  │
│ ─────────────────── │                                  │
│ Статус  ● Active     │                                  │
│ Група   Retail       │                                  │
│ Sync    2хв тому     │                                  │
│ Додано  12.03.2025   │                                  │
│ ─────────────────── │                                  │
│ 📞 Телефони      [3] │                                  │
│ 💲 Ціни         [12] │                                  │
│ 📍 Адреси        [2] │                                  │
│ 🔄 Sync-логи         │                                  │
└─────────────────────┴──────────────────────────────────┘
```

- Sidebar: `220px`, fixed, `border-right: 1px solid var(--border)`
- Favicon block: `44×44` rounded, first letter, colored by group color
- Info rows: label left (muted 10px) / value right (11px bold)
- Nav items: icon + label + count badge; active = `background: rgba(accentColor .15)`, `color: var(--accent)`
- Content: `flex: 1`, scrollable, padding `16px`
- Active section heading shown in content area
- Tabs L011+ will populate each nav section

---

## Bug Fix — Search Input Focus

**Root cause:** `applyQueryParam('search', v)` in the debounce timeout performs `window.location` navigation, causing full page reload and focus loss.

**Fix:** remove `applyQueryParam` call for search; use client-side DOM filtering instead (see Section 2). Status/sort/group filters remain server-side (full URL navigation on click).

---

## Files to Change

| File | Change |
|---|---|
| `public/assets/css/tokens.css` | Replace dark theme color tokens |
| `public/assets/css/pages/site-groups.css` | `.page-controls` redesign, `.group-card` → list row style, add grid toggle |
| `public/assets/css/pages/sites.css` | `.site-card` → new favicon-block style, remove `.group-nav` |
| `public/assets/css/components/cards.css` | `.stat-card` adjust for TG Dark |
| `resources/views/admin/site-groups/index.blade.php` | New controls + list/grid markup |
| `resources/views/admin/sites/index.blade.php` | New controls + remove group-nav + new row markup |
| `resources/views/admin/sites/show.blade.php` | Sidebar layout |
| `public/assets/js/app.js` | Client-side search function |

---

## Out of Scope

- Light theme token update (separate task)
- L011 tab content (phones, prices, addresses — separate task)
- API Keys page (TASK-L010)
