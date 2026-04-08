/* layout.js — theme toggle + drawer */

'use strict';

/* ─── Theme ─────────────────────────────────────────────── */

function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';

    html.setAttribute('data-theme', next);
    document.cookie = 'theme=' + next + '; path=/; max-age=31536000; SameSite=Lax';

    // Reload to swap the SVG icon in PHP-rendered topbar
    location.reload();
}

/* ─── Drawer ─────────────────────────────────────────────── */

function openDrawer(title, bodyHtml, options) {
    options = options || {};

    document.getElementById('drawerTitle').textContent = title || '';
    document.getElementById('drawerBody').innerHTML = bodyHtml || '';

    const drawer = document.getElementById('mainDrawer');
    const overlay = document.getElementById('drawerOverlay');

    drawer.classList.toggle('drawer--wide', !!options.wide);
    drawer.classList.add('is-open');
    overlay.classList.add('is-open');

    if (options.submitLabel) {
        document.getElementById('drawerSubmit').textContent = options.submitLabel;
    }

    if (typeof options.onSubmit === 'function') {
        const btn = document.getElementById('drawerSubmit');
        btn.onclick = options.onSubmit;
    }
}

function closeDrawer() {
    document.getElementById('mainDrawer').classList.remove('is-open');
    document.getElementById('drawerOverlay').classList.remove('is-open');
}

// Close on Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeDrawer();
    }
});
