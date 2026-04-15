# MEMORY.md — DataBridge CRM
# Постійна пам'ять між сесіями Claude Code.
# Оновлювати в кінці КОЖНОЇ сесії.

---

## 📍 Поточний стан

- **Версія:** 0.2.0-alpha (Laravel stack)
- **Активна фаза:** Phase 2 (Batch + Data Browser — завершено)
- **Активний спринт:** Sprint 02 — частково завершено
- **Активна гілка:** `feature/task-data-browser` (запушено)
- **Наступна задача:** Sprint 03 — Sync Engine / API endpoints / Merge

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

## 🔲 Наступні задачі (Sprint 03)

1. **Sync Engine** — WP plugin → CRM API endpoint (`/api/v1/sync`)
2. **API endpoints** — REST для WP-плагінів
3. **Merge** всіх feature/* гілок до `main`

---

## 🌿 Git стан

- **Remote:** `git@github.com:mzalisko/data-bridge-v2.git` ✅
- **Активна гілка:** `feature/task-data-browser` (запушено)
- **Точка повернення для data browser:** `feature/task-batch-edit`
- **Незлиті гілки:** task-l001..task-data-browser (всі на GitHub)
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

*Оновлено: 2026-04-15 | Сесія: sprint-02-data-browser-mobile*
