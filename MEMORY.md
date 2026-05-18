# MEMORY.md — DataBridge CRM
# Постійна пам'ять між сесіями Claude Code.
# Оновлювати в кінці КОЖНОЇ сесії.

---

## 📍 Поточний стан

- **Версія:** 0.4.0 — злито в main, тег `v0.4.0-sprint04-push-arch`
- **Активний спринт:** Sprint 04 — майже завершено
- **Активна гілка:** `feature/per-item-geo-visibility` (коміт `526430b` — table alignment + world panel + pool bar fixes)
- **Plugin гілка:** `feature/push-plugin-v2` (активна, без remote)
- **Наступний крок:** мерж `feature/per-item-geo-visibility` → main + Plugin GitHub remote

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
| CRM i18n: повний переклад всіх Blade views на українську (всі сторінки + drawers + confirm dialogs) | feature/crm-redesign | ✅ |

## ✅ Failover Pool (Sprint 04)

| Задача | Гілка | Статус |
|---|---|---|
| `is_standby/is_blocked/blocked_reason` колонки + `site_failover_logs` таблиця | feature/per-item-geo-visibility | ✅ |
| `FailoverService::trigger()` + `rollback()` (транзакції, snapshot, push WP) | feature/per-item-geo-visibility | ✅ |
| API: `POST /api/v1/failover` + `/rollback` (зовнішній тригер) | feature/per-item-geo-visibility | ✅ |
| Admin: standby toggle, ручний modal, відкат у Data tab | feature/per-item-geo-visibility | ✅ |
| Failover журнал у Data tab | feature/per-item-geo-visibility | ✅ |
| Failover UX: ієрархія за standby_for_id (не is_standby), пагінація 10/стор, очистка в Settings | feature/per-item-geo-visibility | ✅ |
| Failover: прибрати кнопки standby toggle з Data tab (тільки swipe) | feature/per-item-geo-visibility | ✅ |
| Глобальні дані: /data sidebar item, пошук, tabs, bulk edit/copy/delete, Add-to-sites drawer | feature/per-item-geo-visibility | ✅ |
| BulkDataController: /bulk/phones|prices|socials|geos|delete AJAX endpoints | feature/per-item-geo-visibility | ✅ |
| DataBrowserController: paginate(50) + counts в controller | feature/per-item-geo-visibility | ✅ |
| Overview "Всі дані" tab (перед "Весь світ") — всі записи з status badges | feature/per-item-geo-visibility | ✅ |
| Прибрати зелений "Конфліктів не виявлено"; відновити favorites star button | feature/per-item-geo-visibility | ✅ |
| Obsidian документація `02-Модулі/failover_pool.md` | vault | ✅ |
| Overview: таблиці "Всі дані" — table-layout:fixed + colgroup, виправлено "Всш" (обрізання гео-тексту) | feature/per-item-geo-visibility | ✅ |
| Overview: "Весь світ" → 2-col grid як PL/UA (ліво: картки, право: всі поля з ✓/✗) | feature/per-item-geo-visibility | ✅ |
| data/index: pool bar + action bar overlap fix (updateActionBarBottom динамічно) | feature/per-item-geo-visibility | ✅ |
| data/index: прибрати поле "Країна ISO" з edit drawer для phones | feature/per-item-geo-visibility | ✅ |

## ✅ Аудит 2026-05-18 — критичні фікси ЗЛИТО в main (vault: `10-Оптимізація/audit_2026-05-18.md`)

Merge `e6eda95` (origin/main). Production-блокери знято.

1. ✅ **SEC-C1** — `EnforcePermission` middleware (`perm:{view|edit|delete|api_key}`) на web write-маршрутах. RBAC e2e зелений: viewer→403 мутації / 200 reads; manager→bypass; viewer+UserPermission grant→200. Permissions-UI живий.
2. ✅ **SEC-C2** — `Api/FailoverController::rollback` site-scoped (мирор trigger/restore).
3. ✅ **DB-C2** — unique `push_key`/`plugin_edit_token` + (site_id,sort_order)/updated_at індекси (міграція `2026_05_18_000001` прогнана). *(DB-C1 `key_prefix` unique — НЕ зроблено, лишилось.)*
4. ✅ **BatchController**+SiteGeoVisController видалено.
5. ⏳ Спрощення (НЕ зроблені): Site* дубль 8×, `show.blade.php` 3357 р., `src/` мертвий, дві bulk-системи.

## 🔲 Залишилось (Sprint 04)

1. **Plugin git remote** — GitHub repo (поки локальний)
2. **Перевірка WP плагіна** — `dbp_wordpress` Docker вже є, перевірити shortcodes
3. **Conflict resolution** — логіка пріоритету CRM (гео-правила для плагіна)
4. **/bulk/addresses** endpoint — поки відсутній, показує alert

---

## 🌿 Git стан

- **CRM remote:** `git@github.com:mzalisko/data-bridge-v2.git`
- **CRM main:** `v0.4.0-sprint04-push-arch` (злито feature/per-item-geo-visibility)
- **Plugin repo:** `M:\Projects\CC\data-bridge-v2-plugin\` (git local, remote потрібно)
- **Plugin активна гілка:** `feature/push-plugin-v2`
- **Теги повернення:** `v0.4.0-sprint04-push-arch`, `v0.3.0-sprint03-complete`, `v0.2.0-sprint02-complete`

---

## 🔑 Ключові рішення

| Рішення | Значення |
|---|---|
| PHP фреймворк | Laravel (єдиний) |
| CSS/JS (Laravel) | Без фреймворків — Restrained Loft / TG Dark design system |
| CRM standalone | ВИДАЛЕНО (аудит S5, 2026-05-18) — `src/`+`CRM.html`+Vite/Tailwind більше нема; фронт лише `public/assets/` |
| API key | `dbapi_` + 32 hex = 38 симв; Hash::make(); prefix = перші 12 |
| API auth | Bearer → key_prefix (12) → Hash::check() |
| API permissions | JSON array в api_keys.permissions (nullable) |
| Rate limit | RateLimiter 60/min per token, bootstrap/app.php booted() |
| Tab routing (Laravel) | `?tab=overview/data/activity/settings` — server-side; geo subtab `?country=XX` |
| Site Data CRUD | Drawer-based (add/edit) для phones/prices/addresses/socials. Controller redirect via `back()` |
| Theme cookie | Plain `theme=light/dark` (whitelisted в `encryptCookies(except)`); inline `<head>` script читає до CSS |
| Design system V2 | Single `public/assets/css/app.css` — vibeB tokens (єдине джерело; `src/styles/crm-theme.css` видалено) |
| Geo system V2 | `sites.active_geos` (JSON ISO array) + `sites.geo_rules` (JSON map visitor→data). Old `geo_mode/geo_countries` збережені для backward-compat з плагіном |
| Eye-toggle | `is_visible` BOOL DEFAULT 1 на site_phones, site_addresses, site_socials. POST /visibility/{type}/{id} |
| Plugin sync | CRM→Plugin: one-way PUSH (SyncPushService) — автоматично після кожного CRUD та geo-зміни |
| Plugin sync key | 64-hex; зберігається в WP option `dbp_sync_key`; CRM зберігає в `sites.push_key` |
| Plugin push URL | `sites.push_url` = `https://site.com/wp-json/dbp/v1/sync`; в dev — `host.docker.internal:8090` |
| Plugin DB | DBP_DB_VERSION='2.0.0'; dbDelta на plugins_loaded prio 5 |
| Plugin geo | geo_mode/geo_countries у phones/prices/addresses/socials; fail-open |
| Plugin tabs | Cookie server-side (zero-flash); JS записує cookie при кліку |
| socials | Немає колонки label — тільки platform/handle/url/sort_order/geo |
| custom_fields | Немає label/is_visible — тільки field_key/field_value/field_type/sort_order |
| site sticky | overflow:clip на .site-show + position:sticky на sidebar |
| Group FK | nullOnDelete (не cascade) — sites.group_id nullable |
| Failover pool | `is_standby/is_blocked/blocked_reason` на phones+socials; `site_failover_logs` зберігає snapshot; `FailoverService::trigger/rollback()`; API POST /api/v1/failover. Ієрархія: standby_for_id (не is_standby) — promoted залишається child |
| Failover UX | Swipe-only для standby toggle (без кнопок). Журнал: 10/стор, paginator "failover_page". Очистка: DELETE /sites/{site}/failover/history |
| Глобальні дані | /data → DataBrowserController (paginate 50) + BulkDataController AJAX (/bulk/phones|prices|socials|geos|delete). Sidebar: "Глобальні дані" з cylinder icon |
| Bulk add drawer | Type picker + type-specific fields + geo rules + multi-site picker. Addresses поки без AJAX endpoint |
| Plugin edit callback | CRM надсилає `edit_callback.url+key` у push payload; plugin POST-ить зміни назад; callback URL = `/api/plugin-callback/{64-hex-token}` |

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

*Оновлено: 2026-05-18 | Сесія: full-project-audit (vault: audit_2026-05-18.md)*
