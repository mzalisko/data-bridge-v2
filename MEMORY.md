# MEMORY.md — DataBridge CRM
# Постійна пам'ять між сесіями Claude Code.
# Оновлювати в кінці КОЖНОЇ сесії.

---

## 📍 Поточний стан

- **Версія:** 0.3.0 (Laravel stack + Sync API + merged to main)
- **Активна фаза:** Phase 3 — Sprint 03 повністю завершено ✅
- **Активний спринт:** Sprint 04 — WP Plugin
- **Активна гілка:** `main` (запушено, тег `v0.3.0-sprint03-complete`)
- **Наступна задача:** Sprint 04 — WP Plugin базова структура

---

## ✅ Виконано (Laravel stack)

| Задача | Гілка | Статус |
|---|---|---|
| TASK-L001: Laravel 13 + Docker (nginx:8082, mysql:3307, cloudflared) | feature/task-l001-laravel | ✅ |
| TASK-L002: 12 міграцій (всі 15 таблиць схеми) | feature/task-l002-migrations | ✅ |
| TASK-L003: 13 Eloquent моделей з відносинами | feature/task-l003-models | ✅ |
| TASK-L004: Auth (LoginController + Blade layouts + design system CSS/JS) | feature/task-l004-auth | ✅ |
| TASK-L005: Dashboard (real data + x-stat-card + log list) | feature/task-l005-dashboard | ✅ |
| TASK-L006: SiteGroups CRUD + Drawer + Form Requests | feature/task-l006-site-groups | ✅ |
| TASK-L007: Sites CRUD + Drawer + group pill badge | feature/task-l007-sites | ✅ |
| TASK-L008: Users CRUD + Drawer + role badges | feature/task-l008-users | ✅ |
| TASK-L009: Logs viewer (SystemLog + SyncLog, tabs, level/status filter, paginated) | feature/task-l009-logs | ✅ |
| TASK-UI: UI Redesign (TG Dark, dashboard timeline, favorites, site/group/user redesign) | feature/task-ui-redesign | ✅ |
| TASK-L010: API Keys (generate + revoke per site, sidebar block in sites/show) | feature/task-l010-api-keys | ✅ |
| TASK-UI-2: UI Fixes (CSS @stack, pagination arrows, api-key icons, dashboard dot, groups grid) | feature/task-l010-api-keys | ✅ |
| TASK-L011: Site data tabs (phones/prices/addresses/socials CRUD, 4 tests) | feature/task-l010-api-keys | ✅ |
| TASK-BATCH: Batch Edit — 7 tabs (status/group/phone/price/address/social/delete) | feature/task-data-browser | ✅ |
| TASK-DATA-BROWSER: /data page — крос-сайтовий пошук, bulk edit/delete/copy | feature/task-data-browser | ✅ |
| TASK-SEED: SiteDataSeeder (3 groups Alpha/Beta/Gamma, 8 sites Site1–Site8 + повні дані) | feature/task-data-browser | ✅ |
| TASK-MOBILE: Drawer bottom-sheet, 44px touch targets, batch tabs scroll, stat grid | feature/task-data-browser | ✅ |
| TASK-UI-3: Batch mode toggle (Вибрати button), data-row table-style layout, sticky sidebar, right-align col3, preview system | feature/task-data-browser | ✅ |
| TASK-SYNC: Sync Engine REST API (GET /api/v1/sync, write endpoints, ApiKeyAuth, 13 tests) | feature/task-sprint03-sync | ✅ |

| TASK-MERGE: Мерж feature/task-sprint03-sync → main (fast-forward + remote merge) | main | ✅ |

## ✅ Sprint 04 — WP Plugin Rework (в процесі)

| Задача | Гілка | Статус |
|---|---|---|
| TASK-PLUGIN-REWORK: CRM custom_fields API + per-site logs tab | feature/task-plugin-rework | ✅ |
| TASK-PLUGIN-REWORK: Plugin CSS rewrite (Restrained Loft) + all views rewrite | data-bridge-v2-plugin | ✅ |
| TASK-PLUGIN-REWORK: Shortcodes (if/plural/format_tel) + copy UI | data-bridge-v2-plugin | ✅ |
| TASK-PLUGIN-REWORK: CRUD data.js + type_map fix + security fix | data-bridge-v2-plugin | ✅ |

## 🔲 Залишилось (Sprint 04)

1. **Мерж** feature/task-plugin-rework → main (CRM)
2. **Plugin git remote** — підключити до GitHub repo
3. **WP test env** — налаштувати docker wp-test з реальним WordPress
4. **Scheduled sync** — WP Cron pull (вже реалізовано в плагіні)
5. **Conflict resolution** — логіка пріоритету CRM

---

## 🌿 Git стан

- **Remote (CRM):** `git@github.com:mzalisko/data-bridge-v2.git` ✅
- **Активна гілка CRM:** `feature/task-plugin-rework` (запушено)
- **Plugin repo:** `M:\Projects\CC\data-bridge-v2-plugin\` (git init, remote потрібно)
- **Точка повернення Sprint 03:** `v0.3.0-sprint03-complete` (git tag)
- **Точка повернення Sprint 02:** `v0.2.0-sprint02-complete` (git tag)
- **Всі feature/* гілки:** злиті в main ✅
- **Тестові дані:** 3 групи (Alpha/Beta/Gamma) + 8 сайтів (Site1–Site8) + 3 старі групи + 7 старих сайтів

---

## 🔑 Ключові рішення

| Рішення | Значення | Дата |
|---|---|---|
| **PHP фреймворк** | **Laravel** (замість vanilla PHP) | 2026-04-08 |
| Auth | Laravel вбудований + власні контролери (не Breeze UI) | 2026-04-08 |
| Views | Blade templates + Blade components | 2026-04-08 |
| CSS/JS | Без Tailwind/Bootstrap — Restrained Loft / TG Dark design system | 2026-04-08 |
| CrmRail | Завжди темний (#111), незалежно від теми | 2026-04-08 |
| Drawer | 440px стандарт, 600px batch; на мобільному — bottom sheet | 2026-04-08 |
| API key | `dbapi_` + 32 hex = 38 символів; Hash::make(); prefix = перші 12 символів | 2026-04-08 |
| Tab routing | `?tab=phones\|prices\|addresses\|socials` — server-side, SiteController@show | 2026-04-15 |
| Data Browser routing | `?type=phones\|prices\|addresses\|socials&q=...` — DataBrowserController@index | 2026-04-15 |
| Validation | Form Requests (не ручна валідація) | 2026-04-08 |
| RBAC | Laravel Policies + Gates | 2026-04-08 |
| Mobile checkboxes | appearance:none, custom border+fill, indeterminate dash | 2026-04-15 |
| data-row layout | Table-style: data-list = card container (no border-radius), rows use border-bottom separators, col3 right-aligned | 2026-04-16 |
| site-show sticky sidebar | overflow:clip on .site-show (not hidden) + position:sticky on sidebar | 2026-04-16 |
| Preview system | public/preview/*.html — links real CSS files, full shell structure, open at localhost:8082/preview/*.html | 2026-04-16 |
| API auth | Bearer token → find by key_prefix (12 chars) → Hash::check() | 2026-04-17 |
| API permissions | JSON array in api_keys.permissions; MySQL JSON no default → nullable + backfill | 2026-04-17 |
| Rate limiting | Laravel RateLimiter::for('api', 60/min per token) in bootstrap/app.php booted() | 2026-04-17 |

---

## 📋 Факти (не забувати)

- Vault: `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\` (MCP Obsidian доступний)
- Репо: `M:\Projects\CC\data-bridge-v2\`
- Мова документації: Ukrainian | код і коміти: English
- БЕЗ фреймворків (PHP/CSS/JS), без SaaS
- Admin default: `admin@databridge.local` / `admin123` (з міграції)
- Test users: `irina@databridge.local` / `pass123` (manager), `oleksiy@databridge.local` / `pass123` (viewer)
- Docker: `docker-compose up -d --build` → http://localhost:8082
- **ВАЖЛИВО:** При рестарті Docker — якщо volume скинувся — запустити:
  `php artisan db:seed --class=AdminSeeder`
  `php artisan db:seed --class=TestDataSeeder`
  `php artisan db:seed --class=SiteDataSeeder`
- Cloudflare tunnel URL змінюється при кожному рестарті (trycloudflare.com — ефемерний)

---

- Plugin repo path: `M:\Projects\CC\data-bridge-v2-plugin\`
- Plugin CRUD: тип передається до push_create/update/delete у форматі `phones|prices|addresses|socials|custom_fields`
- type_map: phones→phone, prices→price, addresses→address, socials→social, custom_fields→custom_field

*Оновлено: 2026-04-17 | Сесія: sprint-04-plugin-rework*
