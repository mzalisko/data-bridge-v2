# MEMORY.md — DataBridge CRM
# Постійна пам'ять між сесіями Claude Code.
# Оновлювати в кінці КОЖНОЇ сесії.

---

## 📍 Поточний стан

- **Версія:** 0.3.0 (Laravel + Sync API, merged to main)
- **Активний спринт:** Sprint 04 — WP Plugin + CRM Blade redesign V2
- **Активна гілка:** `feature/crm-redesign` — vibeB Blade redesign V2 (full 1:1 match React archive)
- **Останній комміт:** `07c65f9` — fix: persist dark theme + Add geo flow + group form redesign
- **CRM logic гілка:** `feature/task-plugin-rework` (не злита)
- **Plugin гілка:** `feature/plugin-redesign-3pages`
- **Наступний крок:** мерж `feature/crm-redesign` → main + мерж `feature/task-plugin-rework` → main

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
| CRM redesign: standalone React SPA (vibeB, CDN React+Babel) | feature/crm-redesign | ✅ |
| CRM redesign V1: vibeB Blade redesign — всі сторінки (токени, sidebar, компоненти) | feature/crm-redesign | ✅ |
| CRM redesign V2: full 1:1 React archive (consolidated app.css, нові x-favicon/x-status-pill, login card) | feature/crm-redesign | ✅ |
| CRM site detail: top tabs Overview/Data/Activity/Settings + geo selector в Data tab | feature/crm-redesign | ✅ |
| CRM site detail Overview: Geo coverage block (great country pills) + Data by geo (4-col rich) | feature/crm-redesign | ✅ |
| CRM site detail Data: контактний layout з add/edit drawer-ами (phones/prices/addresses/socials) | feature/crm-redesign | ✅ |
| CRM Users page (Team): table з role/status pills + invite/edit/permissions drawers | feature/crm-redesign | ✅ |
| CRM site groups: новий form з color palette + textarea + emoji icon | feature/crm-redesign | ✅ |
| CRM Add geo flow: drawer з country picker → auto-open Add phone з pre-selected ISO | feature/crm-redesign | ✅ |
| CRM dark theme persist: encryptCookies except 'theme' + inline bootstrap script (no flash) | feature/crm-redesign | ✅ |
| CRM site* controllers: redirect via back() замість захардкодженого ?tab=phones | feature/crm-redesign | ✅ |
| CRM Geo V2: eye-toggle is_visible на phones/addresses/socials (migration 2026_05_01) | feature/crm-redesign | ✅ |
| CRM Geo V2: sites.active_geos + geo_rules JSON (migration + SiteGeoController) | feature/crm-redesign | ✅ |
| CRM Geo V2: SiteGeoController — addGeo/removeGeo/saveRules/toggleVisibility | feature/crm-redesign | ✅ |
| CRM Geo V2: geo rules matrix UI в Settings tab + remove geo button в Data tab | feature/crm-redesign | ✅ |
| CRM Users: permissions form redesign (_perm_form.blade.php vibeB grid layout) | feature/crm-redesign | ✅ |
| CRM site groups show: повний rewrite (видалені .page-toolbar/.role-badge класи) | feature/crm-redesign | ✅ |

## 🔲 Залишилось (Sprint 04)

1. **Мерж** `feature/task-plugin-rework` → main (CRM)
2. **Мерж** plugin гілок → master (plugin repo)
3. **Plugin git remote** — GitHub repo
4. **wp-test/** — docker env з реальним WordPress (візуальна перевірка)
5. **Conflict resolution** — логіка пріоритету CRM

---

## 🌿 Git стан

- **CRM remote:** `git@github.com:mzalisko/data-bridge-v2.git`
- **CRM активна гілка:** `feature/crm-redesign` (Blade redesign V2 + standalone React reference)
- **Plugin repo:** `M:\Projects\CC\data-bridge-v2-plugin\` (git init, remote потрібно)
- **Plugin активна гілка:** `feature/plugin-redesign-3pages`
- **Теги повернення:** `v0.3.0-sprint03-complete`, `v0.2.0-sprint02-complete`, `v0.1-vanilla-php-foundation`
- **Stash:** `feature/crm-design-refresh` — стешовані зміни Blade UI

---

## 🔑 Ключові рішення

| Рішення | Значення |
|---|---|
| PHP фреймворк | Laravel (єдиний) |
| CSS/JS (Laravel) | Без фреймворків — Restrained Loft / TG Dark design system |
| CRM standalone | React 18 CDN + Babel standalone + vibeB (Modern SaaS) design |
| CRM standalone файли | `CRM.html` → `src/styles/` → `src/components/` → `src/data/` |
| API key | `dbapi_` + 32 hex = 38 симв; Hash::make(); prefix = перші 12 |
| API auth | Bearer → key_prefix (12) → Hash::check() |
| API permissions | JSON array в api_keys.permissions (nullable) |
| Rate limit | RateLimiter 60/min per token, bootstrap/app.php booted() |
| Tab routing (Laravel) | `?tab=overview/data/activity/settings` — server-side; geo subtab `?country=XX` |
| Site Data CRUD | Drawer-based (add/edit) для phones/prices/addresses/socials. Controller redirect via `back()` |
| Theme cookie | Plain `theme=light/dark` (whitelisted в `encryptCookies(except)`); inline `<head>` script читає до CSS |
| Design system V2 | Single `public/assets/css/app.css` (~530 рядків) — vibeB tokens 1:1 з `src/styles/crm-theme.css` |
| Geo system V2 | `sites.active_geos` (JSON ISO array) + `sites.geo_rules` (JSON map visitor→data). Old `geo_mode/geo_countries` збережені для backward-compat з плагіном |
| Eye-toggle | `is_visible` BOOL DEFAULT 1 на site_phones, site_addresses, site_socials. POST /visibility/{type}/{id} |
| Plugin sync | CRM→Plugin: pull на page load (>60s) + optional webhook ping |
| Plugin DB | DATABRIDGE_DB_VERSION='1.2.0'; dbDelta на plugins_loaded prio 5 |
| Plugin geo | geo_mode/geo_countries у phones/prices/addresses/socials; fail-open |
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
- **Admin:** `admin@databridge.local` / `admin123`
- **Test users:** `irina@databridge.local` (manager), `oleksiy@databridge.local` (viewer) — обидва `pass123`
- **Мова:** документація Ukrainian | код і коміти English
- **Cloudflare tunnel:** ефемерний, URL змінюється при кожному рестарті

---

*Оновлено: 2026-05-01 | Сесія: crm-redesign-geo-v2-visibility-perms*
