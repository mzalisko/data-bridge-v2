# MEMORY.md — DataBridge CRM
# Постійна пам'ять між сесіями Claude Code.
# Оновлювати в кінці КОЖНОЇ сесії.

---

## 📍 Поточний стан

- **Версія:** 0.1.0-alpha (Laravel stack)
- **Активна фаза:** Phase 0–1 (Foundation — Laravel migration)
- **Активний спринт:** Sprint 01
- **Наступна задача:** TASK-L010 — API Keys (generate + revoke per site)
- **Активна гілка:** `feature/task-l009-logs` (запушено)
- **Останній коміт:** `4d83457` feat(logs): Logs viewer with system + sync tabs, level/status filter
- **Точка повернення:** git tag `v0.1-vanilla-php-foundation` (весь vanilla PHP код збережено)

---

## ✅ Виконано (ця сесія)

| Дата | Задача | Гілка | Статус |
|---|---|---|---|
| 2026-04-08 | TASK-001: Obsidian Vault init | — | ✅ |
| 2026-04-08 | TASK-002: Docker Setup | main | ✅ |
| 2026-04-08 | TASK-003: Git init + remote | main | ✅ |
| 2026-04-08 | TASK-004: PHP Directory Structure | feature/task-004-php-structure | ✅ |
| 2026-04-08 | TASK-005: Database PDO singleton | feature/task-005-database | ✅ |
| 2026-04-08 | TASK-006: Router + routes.php + index.php | feature/task-006-router | ✅ |
| 2026-04-08 | TASK-007: CSRF + Logger | feature/task-007-csrf-session | ✅ |
| 2026-04-08 | TASK-008: View.php + Layout.php + CrmRail + tokens/reset CSS + shell/components/drawer CSS + layout.js | feature/task-008-view-engine | ✅ |

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

## 🔲 Наступні задачі

1. **TASK-L010** — API Keys (generate + revoke per site)

---

## 🌿 Git стан

- **Remote:** `git@github.com:mzalisko/data-bridge-v2.git` ✅
- **Активна гілка:** `feature/task-l009-logs` (запушено)
- **Незлиті Laravel гілки:** task-l001..task-l009 (всі на GitHub, PR не відкриті)

---

## 🔑 Ключові рішення

| Рішення | Значення | Дата |
|---|---|---|
| **PHP фреймворк** | **Laravel** (замість vanilla PHP) | 2026-04-08 |
| Причина переходу | Складність CRM (ORM, RBAC, batch, API) виправдовує Laravel | 2026-04-08 |
| Auth | Laravel вбудований + власні контролери (не Breeze UI) | 2026-04-08 |
| Views | Blade templates + Blade components (`<x-stat-card>`, тощо) | 2026-04-08 |
| CSS/JS | Без Tailwind/Bootstrap — наш Restrained Loft design system | 2026-04-08 |
| CrmRail | Завжди темний (#111), незалежно від теми | 2026-04-08 |
| Тема | Cookie `theme` = 'dark'/'light', default: dark | 2026-04-08 |
| Drawer | 440px стандарт, 600px batch | 2026-04-08 |
| API key | `dbapi_` + 32 hex = 38 символів; Hash::make() | 2026-04-08 |
| DB | MySQL 8.0, Eloquent ORM + migrations | 2026-04-08 |
| Validation | Form Requests (не ручна валідація) | 2026-04-08 |
| RBAC | Laravel Policies + Gates | 2026-04-08 |

---

## 📋 Факти (не забувати)

- Vault: `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\` (MCP Obsidian доступний)
- Репо: `M:\Projects\CC\data-bridge-v2\`
- Мова документації: Ukrainian | код і коміти: English
- БЕЗ фреймворків (PHP/CSS/JS), без SaaS
- Admin default: `admin@databridge.local` / `admin123` (з міграції)
- Docker: `docker-compose up -d --build` → http://localhost:8082

---

*Оновлено: 2026-04-10 | Сесія: sprint-01-l009-logs*
