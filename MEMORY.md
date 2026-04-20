# MEMORY.md — DataBridge CRM
# Постійна пам'ять між сесіями Claude Code.
# Оновлювати в кінці КОЖНОЇ сесії.

---

## 📍 Поточний стан

- **Версія:** 0.3.0 (Laravel + Sync API, merged to main)
- **Активний спринт:** Sprint 04 — WP Plugin
- **CRM гілка:** `feature/task-plugin-rework` (не злита в main)
- **Plugin гілка:** `feature/plugin-redesign-3pages`
- **Наступний крок:** мерж обох гілок → main

---

## ✅ Виконано — Laravel CRM (Sprint 01–03)

Всі задачі L001–L011, BATCH, DATA-BROWSER, SEED, MOBILE, UI/UI-2/UI-3, SYNC — злиті в `main`, тег `v0.3.0-sprint03-complete`.

---

## ✅ Sprint 04 — WP Plugin (поточний)

| Задача | Де | Статус |
|---|---|---|
| CRM: custom_fields API + ApiCustomFieldController + pullCustomFields | CRM feature/task-plugin-rework | ✅ |
| CRM: per-site logs tab (synced_at, status='ok') | CRM feature/task-plugin-rework | ✅ |
| CRM: group FK cascade → nullOnDelete (migration) | CRM feature/task-plugin-rework | ✅ |
| CRM: plugin_webhook_url на sites (migration) | CRM feature/task-plugin-rework | ✅ |
| CRM: PluginSyncService::ping() після store/update/destroy | CRM feature/task-plugin-rework | ✅ |
| CRM: SitePhoneController — auto-add country + ping після змін | CRM feature/task-plugin-rework | ✅ |
| Plugin: CSS rewrite (Restrained Loft) + all views rewrite | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Shortcodes (if/plural/format_tel) + copy UI | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: CRUD data.js + type_map fix + security fix | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Overview infographic + geo badges + shortcode copy panel | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Zero-flash tabs (cookie server-side) | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Geo-aware shortcodes + template helpers | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Webhook sync trigger endpoint (admin-ajax nopriv) | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Settings page — webhook URL display + copy | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: DB upgrade routine v1.2.0 (dbDelta geo columns) | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Auto-sync on dashboard page load (якщо >60s) | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Fix custom_fields — field_value замість value/label | plugin feature/plugin-redesign-3pages | ✅ |
| Plugin: Fix socials — прибрати неіснуючий $r['label'] | plugin feature/plugin-redesign-3pages | ✅ |

## 🔲 Залишилось (Sprint 04)

1. **Мерж** `feature/task-plugin-rework` → main (CRM)
2. **Мерж** `feature/plugin-redesign-3pages` → (plugin main)
3. **Plugin git remote** — GitHub repo
4. **wp-test/** — docker env з реальним WordPress
5. **Дизайн плагіна** — відповідність макету (користувач вказав невідповідність)
6. **Conflict resolution** — логіка пріоритету CRM

---

## 🌿 Git стан

- **CRM remote:** `git@github.com:mzalisko/data-bridge-v2.git`
- **CRM активна гілка:** `feature/task-plugin-rework`
- **Plugin repo:** `M:\Projects\CC\data-bridge-v2-plugin\` (git init, remote потрібно)
- **Plugin активна гілка:** `feature/plugin-redesign-3pages`
- **Теги повернення:** `v0.3.0-sprint03-complete`, `v0.2.0-sprint02-complete`, `v0.1-vanilla-php-foundation`

---

## 🔑 Ключові рішення

| Рішення | Значення |
|---|---|
| PHP фреймворк | Laravel (єдиний) |
| CSS/JS | Без фреймворків — Restrained Loft / TG Dark design system |
| API key | `dbapi_` + 32 hex = 38 симв; Hash::make(); prefix = перші 12 |
| API auth | Bearer → key_prefix (12) → Hash::check() |
| API permissions | JSON array в api_keys.permissions (nullable) |
| Rate limit | RateLimiter 60/min per token, bootstrap/app.php booted() |
| Tab routing | `?tab=phones\|prices\|addresses\|socials` — server-side |
| Data Browser | `?type=…&q=…` — DataBrowserController@index |
| Plugin sync | CRM→Plugin: pull на page load (>60s) + optional webhook ping; Plugin→CRM: push на CRUD |
| Plugin DB | DATABRIDGE_DB_VERSION='1.2.0'; dbDelta на plugins_loaded prio 5 |
| Plugin geo | geo_mode/geo_countries у phones/prices/addresses/socials; fail-open якщо country невідомий |
| Plugin tabs | Cookie server-side (zero-flash); JS записує cookie при кліку |
| socials | Немає колонки label — тільки platform/handle/url/sort_order/geo |
| custom_fields | Немає label/is_visible — тільки field_key/field_value/field_type/sort_order |
| site sticky | overflow:clip на .site-show + position:sticky на sidebar |
| Group FK | nullOnDelete (не cascade) — sites.group_id nullable |

---

## 📋 Факти

- **CRM repo:** `M:\Projects\CC\data-bridge-v2\`
- **Plugin repo:** `M:\Projects\CC\data-bridge-v2-plugin\`
- **Vault:** `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\` (MCP Obsidian)
- **URL dev:** http://localhost:8082
- **Docker:** `docker-compose up -d --build`
- **Docker reset seeders:** `AdminSeeder` → `TestDataSeeder` → `SiteDataSeeder`
- **Admin:** `admin@databridge.local` / `admin123`
- **Test users:** `irina@databridge.local` (manager), `oleksiy@databridge.local` (viewer) — обидва `pass123`
- **Мова:** документація Ukrainian | код і коміти English
- **Cloudflare tunnel:** ефемерний, URL змінюється при кожному рестарті

---

*Оновлено: 2026-04-20 | Сесія: sprint-04-plugin-fixes*
