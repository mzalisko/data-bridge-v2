# DataBridge Plugin Rework — Design Spec
**Date:** 2026-04-17
**Approach:** A (In-place rework)
**Approved mockups:** data-page, settings+status, logs

---

## 1. Scope Overview

Two parallel workstreams that must be completed together:

| Workstream | What changes |
|---|---|
| **CRM** | Add `custom_fields` API + per-site sync logs tab |
| **Plugin** | Fix endpoints, add CRUD with permission gating, Restrained Loft CSS, all views rewritten |

---

## 2. CRM Changes

### 2a. Custom Fields module (new)

**Migration:** `2026_04_17_create_site_custom_fields_table.php`
```sql
site_custom_fields (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  site_id     INT UNSIGNED NOT NULL,
  field_key   VARCHAR(128) NOT NULL,
  field_value TEXT NOT NULL,
  field_type  ENUM('text','number','boolean','url') DEFAULT 'text',
  sort_order  SMALLINT DEFAULT 0,
  source      ENUM('crm','plugin') DEFAULT 'crm',
  created_at  TIMESTAMP,
  updated_at  TIMESTAMP,
  FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
)
```

**Model:** `app/Models/SiteCustomField.php` — fillable, belongsTo(Site)

**Controller:** `app/Http/Controllers/Api/ApiCustomFieldController.php`
- `store(Request $request)` — POST `/api/v1/custom-fields`
- `update(Request $request, int $id)` — PUT `/api/v1/custom-fields/{id}`
- `destroy(int $id)` — DELETE `/api/v1/custom-fields/{id}`
- Permission check: `custom_fields.write` on API key

**SyncController additions:**
- `pullCustomFields()` — GET `/api/v1/sync/custom-fields`
- Include `custom_fields` array in main `pull()` response under `data`

**Routes** (`routes/api.php`):
```php
Route::get('/sync/custom-fields', [SyncController::class, 'pullCustomFields']);
Route::post('/custom-fields',        [ApiCustomFieldController::class, 'store']);
Route::put('/custom-fields/{id}',    [ApiCustomFieldController::class, 'update']);
Route::delete('/custom-fields/{id}', [ApiCustomFieldController::class, 'destroy']);
```

**Permissions table additions:**
```
custom_fields.read
custom_fields.write
```

**SyncService:** Add `formatCustomField(SiteCustomField $cf): array` method

**Tests:** Extend `SyncApiTest` with custom_fields pull + write permission tests (4 tests)

### 2b. Per-site sync logs tab

**Where:** `resources/views/admin/sites/show.blade.php` — new tab `?tab=logs`

**Content:** Paginated `sync_logs` for the specific site, same design as global logs page:
- Columns: synced_at · status (badge) · duration_ms · checksum (truncated)
- Filter by status (ok / error / no_changes)
- 20 per page

**Controller:** `SiteController@show` — handle `?tab=logs`, pass `$syncLogs` paginated

**CSS:** Add `.sync-log-tab` styles to existing `sites.css` — reuse log badge classes from `logs.css`

---

## 3. Plugin Changes

### 3a. API layer fixes

**`class-databridge-api-endpoints.php`:**
- Remove `pull_meta()` method entirely (no `/api/v1/sync/meta` endpoint exists)
- Add `pull_custom_fields(int $since)` → GET `sync/custom-fields`
- Add `create_custom_field(array $data)` → POST `custom-fields`
- Add `update_custom_field(int $id, array $data)` → PUT `custom-fields/{id}`
- Add `delete_custom_field(int $id)` → DELETE `custom-fields/{id}`

**`class-databridge-sync-engine.php`:**
- Add `custom_fields` to `$this->models` array
- Add `'custom_fields'` to sync loop in `run_sync()` and `force_sync()`
- **Fix latent bug:** `push_create('phones')` builds `"create_phones"` but API endpoint method is `create_phone` (singular). Add a `$type_map` to convert plural → singular:
```php
private array $type_map = [
    'phones'        => 'phone',
    'prices'        => 'price',
    'addresses'     => 'address',
    'socials'       => 'social',
    'custom_fields' => 'custom_field',
    'custom-fields' => 'custom_field', // from data-ajax str_replace
];
// In push_create/update/delete:
$singular = $this->type_map[$type] ?? $type;
$method = "create_{$singular}"; // → create_phone, create_price, etc.
```

### 3b. Security fix

**`class-databridge-ajax.php` line 65:**
```php
// BEFORE (bug):
$msg = isset($data['message']) ? $data['message'] : "Unexpected HTTP {$code}";
wp_send_json_error(['message' => 'Error: ' . escapeshellcmd($msg)]);

// AFTER:
$msg = isset($data['message']) ? $data['message'] : "Unexpected HTTP {$code}";
wp_send_json_error(['message' => 'Error: ' . esc_html($msg)]);
```

### 3c. New model

**`includes/sync/class-databridge-model-custom-fields.php`:**
- Same structure as `class-databridge-model-phones.php`
- Table: `{prefix}databridge_custom_fields`
- `upsert(array $record)` — fields: `crm_id`, `field_key`, `field_value`, `field_type`, `sort_order`, `synced_at`
- `get_all()`, `get_by_crm_id(int $crm_id)`, `delete_by_crm_id(int $crm_id)`, `delete_all()`

### 3d. CRUD with permission gating

**Pattern for all data pages (phones/prices/addresses/socials/custom-fields):**

1. **Show Add/Edit/Delete buttons always** when plugin is connected (api_url + api_key set)
2. If write operation returns `WP_Error` with code `forbidden` (403) → display inline notice:
   > "API key lacks `phones.write` permission — contact your CRM admin."
3. No local caching of permissions — server decides on each request

**New AJAX actions** (in `class-databridge-data-ajax.php`):
```
databridge_create_{type}   → push_create($type, $data)
databridge_update_{type}   → push_update($type, $crm_id, $data)
databridge_delete_{type}   → push_delete($type, $crm_id)
```
Where `{type}` ∈ `phones | prices | addresses | socials | custom_fields`

All actions: `check_ajax_referer('databridge_admin', 'nonce')` + `current_user_can('manage_options')`

### 3e. CSS — Restrained Loft

**`admin/css/databridge-admin.css`** — full rewrite:

```css
/* Tokens (subset of CRM design system) */
--radius-card: 24px;
--radius-item: 10px;
--radius-pill: 20px;
--bg-page: #ebedf1;
--bg-card: #ffffff;
--dot-ok: #48bb78;
--dot-warn: #ed8936;
--dot-err: #f56565;
--text-primary: #1a1a2e;
--text-secondary: #6b7280;
--text-muted: #9ca3af;
--border: #f3f4f6;
```

Key components:
- `.db-card` — `border-radius: 24px`, `box-shadow: 0 2px 12px rgba(0,0,0,.06)`, **no border**
- `.db-data-row` — grid layout, `border-bottom: 1px solid var(--border)`, hover `#f9fafb`
- `.db-badge-ok/err/warn/gray` — matching CRM badge colors
- `.db-btn-primary` — `background: #1a1a2e; color: #fff; border-radius: 10px`
- `.db-add-panel` — inline form at bottom of card, `background: #f9fafb`
- `.db-tabs` — pill-style tabs matching CRM

### 3f. Admin views rewrite

All 8 views rewritten with new CSS classes. Key changes per view:

| View | Changes |
|---|---|
| `settings-page.php` | 2 cards (Connection + Schedule), Test button, Connected/Error status pill |
| `status-page.php` | Connection card + Sync info card + Data counts grid (2-col), Force Sync |
| `logs-page.php` | Tabs (All/Pull/Push/Health), status filter, type+status badges, pagination |
| `data-phones.php` | data-row grid, inline Add panel, Edit/Delete per row, 403 notice |
| `data-prices.php` | same pattern + currency/period fields |
| `data-addresses.php` | same pattern + country/city/street fields |
| `data-socials.php` | same pattern + platform select + handle/URL fields |
| `data-custom-fields.php` | same pattern + key/value/type fields |

**Sync bar** (shared partial `_sync-bar.php`) — shown at top of all data pages:
- Last sync time
- Status dot
- Force Sync button

---

## 4. Data Flow

```
WP Admin user edits phone
  → databridge_update_phones AJAX (nonce + capability check)
  → DataBridge_Data_Ajax::update_phones()
  → DataBridge_Sync_Engine::push_update('phones', $crm_id, $data)
  → DataBridge_API_Endpoints::update_phone($crm_id, $data)
  → DataBridge_API_Client::put('phones/{id}', $data)
  → CRM: ApiPhoneController::update() [checks phones.write permission]
  → 200 OK → local model upserted → success response to JS
  → 403 Forbidden → WP_Error 'forbidden' → notice shown in UI
```

---

## 5. Plugin file structure (after rework)

```
data-bridge-v2-plugin/
├── data-bridge.php                          (no change)
├── includes/
│   ├── class-databridge.php                 (no change)
│   ├── class-databridge-activator.php       (add custom_fields table)
│   ├── class-databridge-deactivator.php     (no change)
│   ├── class-databridge-shortcodes.php      (no change)
│   ├── api/
│   │   ├── class-databridge-api-client.php  (no change)
│   │   ├── class-databridge-api-endpoints.php  (remove pull_meta, add custom_fields)
│   │   └── class-databridge-logger.php      (no change)
│   ├── sync/
│   │   ├── class-databridge-sync-engine.php    (add custom_fields to models + loop)
│   │   ├── class-databridge-model-phones.php   (no change)
│   │   ├── class-databridge-model-prices.php   (no change)
│   │   ├── class-databridge-model-addresses.php (no change)
│   │   ├── class-databridge-model-socials.php  (no change)
│   │   └── class-databridge-model-custom-fields.php  (NEW)
│   └── admin/
│       ├── class-databridge-settings.php    (no change)
│       ├── class-databridge-ajax.php        (fix escapeshellcmd → esc_html)
│       └── class-databridge-data-ajax.php   (add create/update/delete for all types)
├── admin/
│   ├── css/databridge-admin.css             (full rewrite — Restrained Loft)
│   ├── js/databridge-admin.js               (minor: update test connection response handling)
│   ├── js/databridge-data.js                (add create/edit/delete JS)
│   └── views/
│       ├── _sync-bar.php                    (NEW shared partial)
│       ├── settings-page.php                (rewrite)
│       ├── status-page.php                  (rewrite)
│       ├── logs-page.php                    (rewrite)
│       ├── data-phones.php                  (rewrite)
│       ├── data-prices.php                  (rewrite)
│       ├── data-addresses.php               (rewrite)
│       ├── data-socials.php                 (rewrite)
│       └── data-custom-fields.php           (rewrite)
└── sync-manifest.json                       (add custom_fields entry)
```

---

## 6. Additional: CRM per-site logs tab

Requested during brainstorm — added to sprint scope.

- New tab `?tab=logs` in `sites/show`
- Displays `sync_logs` WHERE `site_id = $site->id`, paginated 20/page
- Columns: synced_at · status (ok/error/no_changes badge) · duration_ms · checksum (truncated sha256:xxxx…)
- Filter by status
- CSS: reuse `.log-badge-*` classes from `logs.css`

---

## 7. What is NOT changing

- `DataBridge_API_Client` — HTTP layer is correct
- `DataBridge_Sync_Engine` core logic — pull/push/force_sync/health
- All 4 existing model classes — schema matches CRM
- `DataBridge_Activator` schema for existing 5 tables
- `DataBridge_Settings` menu registration
- `DataBridge_Deactivator`, shortcodes
- Plugin entry point `data-bridge.php`

---

## 8. Git strategy

Single branch: `feature/task-plugin-rework`
Commit order:
1. `feat(crm): add site_custom_fields migration + model`
2. `feat(crm): ApiCustomFieldController + routes + SyncService`
3. `feat(crm): pullCustomFields in SyncController`
4. `test(crm): extend SyncApiTest with custom_fields`
5. `feat(crm): per-site sync logs tab in sites/show`
6. `feat(plugin): add DataBridge_Model_CustomFields`
7. `feat(plugin): update API endpoints — remove pull_meta, add custom_fields`
8. `feat(plugin): update sync engine — custom_fields in models + loop`
9. `fix(plugin): escapeshellcmd → esc_html in ajax`
10. `feat(plugin): CRUD ajax actions for all data types`
11. `style(plugin): Restrained Loft CSS rewrite`
12. `feat(plugin): rewrite all admin views`
13. `docs(memory): update MEMORY.md`
