# Obsidian MCP — Покрокова інструкція підключення

## Що це дає?

Claude Code зможе читати і писати у vault (`DataBridgeV2/`) прямо під час кодування.
Замість ручного копіювання шляхів — Claude сам знайде і прочитає потрібний файл.

---

## Крок 1 — Встановити плагін у Obsidian

1. Відкрий Obsidian → Settings → Community plugins → Browse
2. Знайди: **"Local REST API"**
3. Встанови і **увімкни** плагін
4. Перейди у налаштування плагіну:
   - Port: `27123` (або інший, запам'ятай його)
   - Enable HTTPS: вимкнути (для локального)
   - API Key: згенерувати і зберегти

---

## Крок 2 — Додати MCP до Claude Code

Відкрий термінал і виконай:

```bash
claude mcp add obsidian \
  -e OBSIDIAN_API_KEY="твій-api-key-з-плагіну" \
  -- npx -y @modelcontextprotocol/server-obsidian
```

**Або вручну** — відкрий файл:
```
C:\Users\zalis\.claude.json
```

Додай секцію `mcpServers`:
```json
{
  "mcpServers": {
    "obsidian": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-obsidian"],
      "env": {
        "OBSIDIAN_API_KEY": "твій-api-key-з-плагіну",
        "OBSIDIAN_BASE_URL": "http://127.0.0.1:27123"
      }
    }
  }
}
```

---

## Крок 3 — Перевірити

```bash
# У терміналі Claude Code:
claude mcp list
# Має показати: obsidian — connected

# Тест у розмові з Claude Code:
# "Прочитай 08-Задачі/sprint_01.md з vault"
```

---

## Альтернатива без плагіну (простіша)

Якщо не хочеш Obsidian Local REST API — Claude Code може читати vault **напряму як файли** через Bash/Read, бо шлях прописаний у CLAUDE.md:

```
C:\Users\zalis\OneDrive\Documents\DataBridgeV2\
```

Claude Code сам знайде файли якщо в CLAUDE.md є шлях до vault.
Це вже налаштовано — Obsidian MCP є бонусом, не обов'язком.

---

## Статус

- [ ] Obsidian Local REST API плагін встановлено
- [ ] API ключ збережено
- [ ] MCP додано через `claude mcp add`
- [ ] Перевірено: `claude mcp list` показує obsidian
