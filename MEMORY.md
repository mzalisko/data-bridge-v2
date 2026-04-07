# MEMORY.md — DataBridge CRM
# Постійна пам'ять між сесіями Claude Code.
# Оновлювати в кінці КОЖНОЇ сесії.

---

## 📍 Поточний стан

- **Версія:** 0.0.1-pre-alpha
- **Активна фаза:** Phase 0 (Environment Bootstrap)
- **Активний спринт:** Sprint 01
- **Наступна задача:** TASK-002 — Docker Setup
- **Остання гілка:** —
- **Останній коміт:** —

---

## ✅ Виконано

| Дата | Задача | Агент |
|---|---|---|
| 2026-04-08 | TASK-001: Obsidian Vault init (41 файл) | obsidian |
| 2026-04-08 | Налаштування CLAUDE.md + MEMORY.md у репо | obsidian |
| 2026-04-08 | Створення каркасу PHP-проекту | arch |

---

## 🔲 Наступні кроки

1. **TASK-002** — docker-compose.yml + Dockerfile + nginx.conf → запустити `docker-compose up`
2. **TASK-003** — `git remote add origin <URL>` + перший push
3. **TASK-004** — Структура директорій PHP (вже є каркас)
4. **TASK-005** — `src/Core/Database.php` + `migrations/001_initial_schema.sql`
5. **TASK-006** — `src/Core/Router.php` + `public/index.php`

---

## 🔑 Ключові рішення

| Рішення | Значення | Дата |
|---|---|---|
| PHP підхід | `final class` + `static` для Core; функції для Views | 2026-04-08 |
| Drawer | 440px стандарт, 600px batch | 2026-04-08 |
| API key | `dbapi_` + 32 hex = 38 символів | 2026-04-08 |
| Auto-push | Пушити одразу після коміту | 2026-04-08 |
| DB | MySQL 8.0, PDO prepared statements | 2026-04-08 |
| Session | httponly + secure + samesite=Strict | 2026-04-08 |

---

## ⚠️ Відкриті питання

- [ ] **Git remote URL** — потрібно від MeWeek перед TASK-003
- [ ] **VPS домен** — для production
- [ ] **DB password** — вибрати перед TASK-002 (.env)

---

## 🐳 Docker стан

- **Статус:** НЕ запущено (чекає TASK-002)
- **URL:** http://localhost:8080

---

## 🌿 Git стан

- **Remote:** не підключено
- **Гілки:** відсутні (чекає git init)

---

## 📋 Факти (не забувати)

- Vault: `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\`
- Репо: `M:\Projects\CC\data-bridge-v2\`
- Мова документації: Ukrainian | код і коміти: English
- Без фреймворків (PHP/CSS/JS), без SaaS
- Sprint 01 tasks: TASK-001..010 (деталі у vault/08-Задачі/sprint_01.md)

---

*Оновлено: 2026-04-08 | Сесія: init*
