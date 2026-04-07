
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
| `sec` | Безпека, CSRF, аутентифікація, RBAC |
| `analyst` | Бізнес-логіка, валідація, вимоги |

---

## 📁 Структура проекту

```
data-bridge-v2/                    ← ТИ ТУТ (PHP код)
├── CLAUDE.md                      ← цей файл (auto-read)
├── MEMORY.md                      ← постійна пам'ять (auto-read via @import)
├── public/
│   ├── index.php                  ← єдина точка входу
│   ├── .htaccess                  ← URL rewriting
│   └── assets/css/ js/ img/
├── src/
│   ├── Core/                      ← Router, View, Layout, Database, CSRF, Session
│   ├── Auth/                      ← AuthController, AuthGuard
│   ├── Admin/                     ← Dashboard, Sites, SiteGroups, Users, Logs controllers
│   ├── Api/                       ← SyncController (REST для WP-плагінів)
│   └── Views/
│       ├── Pages/                 ← Dashboard, Sites/, SiteGroups/, Users/, Logs/
│       └── Components/            ← CrmRail, StatCard, GroupCard, SiteCard, Drawer
├── config/
│   └── database.php
├── migrations/
│   └── 001_initial_schema.sql
├── docker-compose.yml
├── Dockerfile
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
| Робота з безпекою (CSRF, auth) | `06-Безпека/csrf.md` або `06-Безпека/authentication.md` |
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

1. **Без PHP-фреймворку** — ні Laravel, ні Symfony, ні Slim
2. **Без CSS-фреймворку** — ні Tailwind, ні Bootstrap
3. **Без JS-фреймворку** — ні React, ні Vue, ні Alpine
4. **Без plaintext паролів** — тільки `password_hash()` / `password_verify()`
5. **Без SQL-конкатенації** — тільки PDO prepared statements
6. **Без unescaped output** — тільки `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')`
7. **Без POST без CSRF** — завжди `CSRF::verify()` першим
8. **Без зламаного коду в комітах** — кожен коміт = робочий стан
9. **Документація тільки українською** — код і коміти англійською
10. **Пушити одразу після коміту** — без очікування дозволу

---

## 🏗️ PHP-архітектура (патерн контролера)

```php
public function index(): void
{
    AuthGuard::require();                           // 1. Auth check
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        CSRF::verify();                             // 2. CSRF (тільки POST)
    }
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // 3. Validate
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare('SELECT * FROM sites WHERE id = ?'); // 4. PDO
    $stmt->execute([$id]);
    View::render('Pages/Sites/Show', [              // 5. Render
        'title' => 'Деталі сайту',
        'site'  => $stmt->fetch(PDO::FETCH_ASSOC),
        'csrf'  => CSRF::getToken(),
    ]);
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

---

## 🔌 Docker

```bash
docker-compose up -d --build    # запуск
docker-compose ps               # статус
docker-compose logs -f php      # логи
# URL: http://localhost:8080
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
Зберігати: password_hash($raw, PASSWORD_BCRYPT)
Відображати: тільки перші 12 символів (key_prefix)
```

---

## 📞 Проект

- **Vault:** `C:\Users\zalis\OneDrive\Documents\DataBridgeV2\`
- **Репо:** `M:\Projects\CC\data-bridge-v2\`
- **URL (dev):** http://localhost:8080
- **Власник:** MeWeek (zaliskomykola@gmail.com)
