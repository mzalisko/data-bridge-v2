<?php
/** @var string $content  Page HTML captured by View::render() */
/** @var string $title    Escaped page title */
/** @var string $csrf     Current CSRF token */

$theme    = $_COOKIE['theme'] ?? 'dark';
$theme    = in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="uk" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> — DataBridge CRM</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>

<div class="shell">
    <?php require BASE_PATH . '/src/Views/Components/CrmRail.php'; ?>

    <div class="shell-body">
        <header class="topbar">
            <span class="topbar-title"><?= $title ?></span>
            <div class="topbar-actions">
                <button class="btn-icon theme-toggle" onclick="toggleTheme()" title="Змінити тему">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <?php if ($theme === 'dark'): ?>
                        <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/>
                        <line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                        <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                        <?php else: ?>
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        <?php endif; ?>
                    </svg>
                </button>
                <span class="topbar-user"><?= $userName ?></span>
            </div>
        </header>

        <main class="page-content">
            <?= $content ?>
        </main>
    </div>
</div>

<!-- Drawer -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<aside class="drawer" id="mainDrawer">
    <div class="drawer-header">
        <h3 class="drawer-title" id="drawerTitle"></h3>
        <button class="drawer-close" onclick="closeDrawer()">×</button>
    </div>
    <div class="drawer-body" id="drawerBody"></div>
    <div class="drawer-footer">
        <button class="btn-ghost" onclick="closeDrawer()">Скасувати</button>
        <button class="btn-primary" id="drawerSubmit">Зберегти</button>
    </div>
</aside>

<script src="/assets/js/layout.js"></script>
</body>
</html>
