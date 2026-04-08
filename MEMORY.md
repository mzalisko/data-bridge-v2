# MEMORY.md — DataBridge CRM
# Постійна пам'ять між сесіями Claude Code.
# Оновлювати в кінці КОЖНОЇ сесії.

---

## 📍 Поточний стан

- **Версія:** 0.1.0-alpha (в процесі)
- **Активна фаза:** Phase 0–1 (Foundation)
- **Активний спринт:** Sprint 01
- **Наступна задача:** TASK-008 (View Engine — залишилось CSS shell/components + JS + login view) → потім TASK-009 (Auth)
- **Активна гілка:** `feature/task-008-view-engine`
- **Останній коміт:** `5796f88` feat(design): View engine, Layout shell, CrmRail, CSS tokens

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
| 2026-04-08 | TASK-008: View.php + Layout.php + CrmRail + tokens/reset CSS | feature/task-008-view-engine | 🔄 |

---

## 🔲 TASK-008 — що залишилось

Гілка: `feature/task-008-view-engine`

- [ ] `public/assets/css/layout/shell.css`
- [ ] `public/assets/css/components/cards.css`
- [ ] `public/assets/css/components/buttons.css`
- [ ] `public/assets/css/components/forms.css`
- [ ] `public/assets/css/components/drawer.css`
- [ ] `public/assets/css/app.css`
- [ ] `public/assets/js/layout.js` (theme toggle + drawer open/close)

---

## 🔲 Наступні задачі

1. **TASK-008** — завершити CSS + JS (поточна гілка)
2. **TASK-009** — Auth (AuthController + AuthGuard + Login view)
3. **TASK-010** — Dashboard skeleton (DashboardController + view + StatCard)

---

## 🌿 Git стан

- **Remote:** `git@github.com:mzalisko/data-bridge-v2.git` ✅
- **Гілки на remote:** `main`, `develop`, `feature/task-004..008`
- **Незлиті гілки:** task-004, task-005, task-006, task-007, task-008 (всі на GitHub, PR не відкриті)

---

## 🔑 Ключові рішення

| Рішення | Значення | Дата |
|---|---|---|
| View::render() | Буферизує контент, потім включає Layout.php | 2026-04-08 |
| View::renderBare() | Для login, errors, API responses | 2026-04-08 |
| CrmRail | Завжди темний (#111), незалежно від теми | 2026-04-08 |
| Тема | Cookie `theme` = 'dark'/'light', default: dark | 2026-04-08 |
| Router | Regex match, {id} = digits only → intval() | 2026-04-08 |
| Logger | Silent — ніколи не кидає Exception | 2026-04-08 |
| PHP підхід | `final class` + `static` для Core | 2026-04-08 |
| Drawer | 440px стандарт, 600px batch | 2026-04-08 |
| API key | `dbapi_` + 32 hex = 38 символів | 2026-04-08 |
| DB | MySQL 8.0, PDO prepared statements | 2026-04-08 |

---

## 📋 Факти (не забувати)

- Vault: `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\` (MCP Obsidian доступний)
- Репо: `M:\Projects\CC\data-bridge-v2\`
- Мова документації: Ukrainian | код і коміти: English
- БЕЗ фреймворків (PHP/CSS/JS), без SaaS
- Admin default: `admin@databridge.local` / `admin123` (з міграції)
- Docker: `docker-compose up -d --build` → http://localhost:8080

---

*Оновлено: 2026-04-08 | Сесія: sprint-01-foundation*
