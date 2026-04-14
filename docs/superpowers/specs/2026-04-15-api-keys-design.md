# TASK-L010 — API Keys Design Spec

**Date:** 2026-04-15
**Branch:** feature/task-l010-api-keys
**Status:** Approved

---

## Goal

Generate and revoke API keys per site. Keys are used by WordPress plugins to authenticate sync requests to DataBridge CRM.

---

## Existing Infrastructure (already in place)

| File | Status |
|---|---|
| `app/Models/ApiKey.php` | ✅ Ready — `generate()`, `verify()`, `isRevoked()`, `isActive()` |
| `database/migrations/2026_04_08_000080_create_api_keys_table.php` | ✅ Ready — `api_keys` table |

**Schema:** `api_keys(id, site_id UNIQUE, key_hash, key_prefix(12), created_at, last_used, revoked_at)`

**Key format:** `dbapi_` + `bin2hex(random_bytes(16))` = 38 chars. Stored as `Hash::make()`, displayed as prefix only (`key_prefix` = first 12 chars).

---

## Architecture

### New files

| File | Purpose |
|---|---|
| `app/Http/Controllers/Admin/ApiKeyController.php` | `generate()`, `revoke()` |
| `resources/views/admin/sites/_api-key.blade.php` | Sidebar partial — 3 states |

### Modified files

| File | Change |
|---|---|
| `routes/web.php` | +2 POST routes |
| `app/Http/Controllers/Admin/SiteController.php` | `show()` loads `$site->apiKey` |
| `resources/views/admin/sites/show.blade.php` | `@include` partial in sidebar |

### Routes

```
POST /sites/{site}/api-key/generate   → ApiKeyController@generate
POST /sites/{site}/api-key/revoke     → ApiKeyController@revoke
```

Both routes are under `auth` middleware. No new middleware needed.

---

## Controller Logic

### `ApiKeyController@generate`

1. Load site via route model binding
2. If site has existing key → delete it (regardless of revoked state)
3. Generate new key: `ApiKey::generate()` returns `['raw', 'hash', 'prefix']`
4. Create `ApiKey` record: `site_id`, `key_hash`, `key_prefix`
5. Flash full raw key to session: `session()->flash('api_key_raw', $raw)`
6. Redirect back to `sites.show`

### `ApiKeyController@revoke`

1. Load site via route model binding
2. If no active key → redirect back (no-op)
3. Set `revoked_at = now()`
4. Redirect back to `sites.show` with success flash

---

## UI — API Key Block (sidebar partial)

Location: `site-show__sidebar` — between `.site-show__info` and `.site-show__nav`.

### State 1 — No key

```
API Key                    ● немає
──────────────────────────────────
    Ключ не згенеровано
[ Згенерувати ]
```

### State 2 — Active + flash (immediately after generate)

```
API Key                    ● active
──────────────────────────────────
dbapi_Xc4f9a...
┌─ Скопіюйте зараз ───────────────┐
│ dbapi_Xc4f9a2b3d1e8f7c6a5b4... │
│                    [📋 Скопіювати] │
└───────────────────────────────────┘
[ Перегенерувати ]  [ Відкликати ]
```

Flash box shown only when `session('api_key_raw')` is set. Green tint (`rgba(72,187,120,0.08)` bg, `rgba(72,187,120,0.25)` border).

### State 3 — Active (normal)

```
API Key                    ● active
──────────────────────────────────
dbapi_Xc4f9a...
[ Перегенерувати ]  [ Відкликати ]
```

### State 4 — Revoked

```
API Key                  ● revoked
──────────────────────────────────
dbapi_Xc4f9a...  (strikethrough, opacity 0.4)
[ Згенерувати нового ]
```

---

## CSS

New class: `.api-key-block` — added to `public/assets/css/pages/sites.css`.

Uses existing tokens only:
- `--bg-page`, `--border-color`, `--text-muted`, `--dot-ok`, `--dot-off`, `--accent`
- Button classes: `.btn-sm` (new small variant), reuses existing token values

---

## Security

- Both actions require `auth` middleware (already on all admin routes)
- Old key deleted on regenerate — no orphaned active hashes
- Raw key never stored, never logged — only in flash session (single request lifetime)
- CSRF: `@csrf` on both forms (standard Blade)

---

## Out of Scope (this task)

- RBAC / Policy gates (only `auth` middleware for now)
- `last_used` update on API requests (belongs to sync/API task)
- Multiple keys per site
- Key expiry / TTL
