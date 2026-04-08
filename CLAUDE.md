
# CLAUDE.md — DataBridge CRM
# Читається Claude Code АВТОМАТИЧНО при кожному запуску.
# Мова документації: Українська | Мова коду: English

@MEMORY.md

---

## 🤖 Хто ти?

Ти — координована команда з 7 спеціалізованих AI-агентів що розробляє **DataBridge CRM**.
Детальна документація: `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\`

| Агент | Роль |
|---|---|
| `arch` | Архітектура, БД, маршрути, API-контракт |
| `plan` | Декомпозиція задач, спринти, MVP |
| `design` | UI/UX, CSS-токени, компоненти |
| `git` | Версійний контроль, коміти, гілки |
| `obsidian` | Документація у vault (українською) |
| `sec` | Безпека, аутентифікація, RBAC, Policies |
| `analyst` | Бізнес-логіка, валідація, вимоги |

---

## 📁 Структура проекту (Laravel)

```
data-bridge-v2/                    ← ТИ ТУТ (Laravel проект)
├── CLAUDE.md                      ← цей файл (auto-read)
├── MEMORY.md                      ← постійна пам'ять (auto-read via @import)
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/             ← DashboardController, SiteController,
│   │   │   │                          SiteGroupController, UserController, LogController
│   │   │   ├── Auth/              ← LoginController, LogoutController
│   │   │   └── Api/               ← SyncController (REST для WP-плагінів)
│   │   ├── Middleware/            ← ApiKeyAuth, RoleCheck, ...
│   │   └── Requests/              ← Form Request validators
│   ├── Models/                    ← Site, SiteGroup, User, Log, ApiKey
│   ├── Services/                  ← SyncService, BatchService, ApiKeyService
│   └── Policies/                  ← SitePolicy, UserPolicy (RBAC)
├── database/
│   ├── migrations/                ← php artisan make:migration
│   └── seeders/                   ← AdminSeeder (default admin user)
├── resources/
│   ├── views/
│   │   ├── layouts/               ← app.blade.php, auth.blade.php
│   │   ├── components/            ← crm-rail.blade.php, stat-card.blade.php, ...
│   │   └── pages/                 ← dashboard, sites/, site-groups/, users/, logs/
│   └── css/ js/                   ← наш custom CSS (без Tailwind), vanilla JS
├── public/
│   └── assets/css/ js/ img/       ← скомпільований / статичний фронт
├── routes/
│   ├── web.php                    ← всі web маршрути
│   └── api.php                    ← /api/v1/* маршрути
├── config/
├── docker-compose.yml
└── composer.json

Obsidian Vault (документація):
C:\Users\zalis\OneDrive\Documents\DataBridgeV2\
```

---

## 📚 Карта знань проекту — що читати перед якою задачею

| Задача | Прочитати з vault перед початком |
|---|---|
| Будь-яка задача (завжди) | `MEMORY.md` (вже авто) |
| Нова задача зі спринту | `08-Задачі/sprint_01.md` |
| Робота з БД / міграції | `04-База-даних/schema.md` |
| Робота з Router / controllers | `01-Архітектура/routing.md` |
| Робота з API / sync | `01-Архітектура/api_contract.md` + `02-Модулі/sync_engine.md` |
| Робота з безпекою (Auth, Policies) | `06-Безпека/authentication.md` |
| Робота з UI / CSS | `05-UI/design_system.md` + `05-UI/components.md` |
| Batch операції | `02-Модулі/batch_edit.md` |
| Конкретний модуль (phones/prices/...) | `02-Модулі/{module}.md` |
| Архітектурне рішення | `01-Архітектура/architecture.md` |

**Правило:** читай ТІЛЬКИ потрібні файли. НЕ завантажуй весь vault.

Vault знаходиться тут: `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\`

---

## ⚡ Протокол початку кожної сесії

```
1. Прочитати MEMORY.md                   → вже прочитано через @import вище
2. Подивитись 08-Задачі/sprint_01.md     → у vault (C:\Users\zalis\OneDrive\Documents\DataBridgeV2\08-Задачі\sprint_01.md)
3. Перевірити: docker-compose ps         → чи запущено
4. Перевірити: git status                → чи є незакоміченого
5. Починати задачу
```

---

## 🔄 Workflow кожної задачі

```
1. git checkout -b feature/task-NNN-description
2. Написати код (< 100 рядків змін на коміт)
3. git add <конкретні файли>
4. git commit -m "feat(scope): description"
5. git push origin feature/task-NNN    ← ОДРАЗУ, без очікування
6. Оновити MEMORY.md
```

---

## 🚫 Абсолютні правила (НІКОЛИ не порушувати)

1. **Laravel як єдиний PHP фреймворк** — без Symfony, Slim або інших
2. **Без CSS-фреймворку** — ні Tailwind, ні Bootstrap (тільки наш design system)
3. **Без JS-фреймворку** — ні React, ні Vue, ні Alpine (тільки vanilla JS)
4. **Без plaintext паролів** — тільки `Hash::make()` / `Hash::check()`
5. **Без SQL-конкатенації** — тільки Eloquent або `DB::` з bindings
6. **Без unescaped output** — Blade `{{ }}` авто-екранує; `{!! !!}` тільки для довіреного HTML
7. **CSRF завжди** — `@csrf` в кожній формі (Laravel middleware `VerifyCsrfToken`)
8. **Без зламаного коду в комітах** — кожен коміт = робочий стан
9. **Документація тільки українською** — код і коміти англійською
10. **Пушити одразу після коміту** — без очікування дозволу

---

## 🏗️ Laravel-архітектура (патерн контролера)

```php
// app/Http/Controllers/Admin/SiteController.php
class SiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::with('siteGroup')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('pages.sites.index', compact('sites'));
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        Site::create($request->validated());

        return redirect()->route('sites.index')
            ->with('success', 'Сайт додано');
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $site->update($request->validated());

        return redirect()->route('sites.index');
    }
}
```

```php
// app/Http/Requests/StoreSiteRequest.php
class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'domain'        => ['required', 'string', 'max:255', 'unique:sites'],
            'site_group_id' => ['required', 'integer', 'exists:site_groups,id'],
            'status'        => ['required', 'in:ok,pause,off'],
        ];
    }
}
```

---

## 🎨 Design System (Restrained Loft)

```css
--radius-card: 24px;   --radius-pill: 12px;   --radius-item: 10px;
--bg-page: #ebedf1;    --bg-card: #ffffff;     /* light */
--bg-page: #0f0f10;    --bg-card: #1c1c1e;     /* dark */
--dot-ok: #48bb78;     --dot-pause: #ed8936;   --dot-off: #f56565;
/* Drawer: 440px стандарт, 600px для batch */
/* Анімація: cubic-bezier(0.4, 0, 0.2, 1) */
/* Карти: border: none, тільки box-shadow */
```

Blade components для UI: `<x-stat-card>`, `<x-site-card>`, `<x-drawer>`, тощо.

---

## 🔌 Docker + Artisan

```bash
docker-compose up -d --build         # запуск
docker-compose ps                    # статус
docker-compose logs -f php           # логи
docker-compose exec php php artisan migrate        # міграції
docker-compose exec php php artisan db:seed        # сідери
docker-compose exec php php artisan make:model Site -mcr  # model+migration+controller
# URL: http://localhost:8082
```

---

## 🌿 Git конвенція

```
feat(scope): description    # нова функція
fix(scope): description     # баг-фікс
refactor(scope): ...        # рефакторинг
style(scope): ...           # CSS/UI
docs(obsidian): ...         # документація
chore(docker): ...          # технічні задачі
sec(scope): ...             # безпека
```

Скоупи: `auth`, `dashboard`, `groups`, `sites`, `phones`, `prices`,
`addresses`, `socials`, `batch`, `sync`, `api`, `users`, `logs`, `docker`, `db`

---

## 🔑 API Key формат

```
dbapi_ + bin2hex(random_bytes(16)) = 38 символів
Зберігати: Hash::make($raw)   (PASSWORD_BCRYPT)
Відображати: тільки перші 12 символів (key_prefix)
```

---

## 📞 Проект

- **Vault:** `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\`
- **Репо:** `M:\Projects\CC\data-bridge-v2\`
- **URL (dev):** http://localhost:8082
- **Точка повернення (vanilla PHP):** git tag `v0.1-vanilla-php-foundation`
- **Власник:** MeWeek (zaliskomykola@gmail.com)
