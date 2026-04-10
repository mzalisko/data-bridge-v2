/* DataBridge CRM — Layout JS */

// Theme toggle
function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    document.cookie = 'theme=' + next + ';path=/;max-age=31536000;SameSite=Lax';
}

// Drawer
function openDrawer(id) {
    const drawer = document.getElementById(id);
    const overlay = document.getElementById(id + '-overlay');
    if (drawer) drawer.classList.add('is-open');
    if (overlay) overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function closeDrawer(id) {
    const drawer = document.getElementById(id);
    const overlay = document.getElementById(id + '-overlay');
    if (drawer) drawer.classList.remove('is-open');
    if (overlay) overlay.classList.remove('is-open');
    document.body.style.overflow = '';
}

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('.drawer.is-open').forEach(function(drawer) {
        closeDrawer(drawer.id);
    });
});

// View toggle (sites list / grid)
function initViewToggle(storageKey, listId, btnListId, btnGridId) {
    var list   = document.getElementById(listId);
    var btnList = document.getElementById(btnListId);
    var btnGrid = document.getElementById(btnGridId);
    if (!list || !btnList || !btnGrid) return;

    var saved = localStorage.getItem(storageKey) || 'list';
    applyView(saved);

    btnList.addEventListener('click', function() { applyView('list'); });
    btnGrid.addEventListener('click', function() { applyView('grid'); });

    function applyView(mode) {
        if (mode === 'grid') {
            list.classList.add('sites-list--grid');
            btnGrid.classList.add('is-active');
            btnList.classList.remove('is-active');
        } else {
            list.classList.remove('sites-list--grid');
            btnList.classList.add('is-active');
            btnGrid.classList.remove('is-active');
        }
        localStorage.setItem(storageKey, mode);
    }
}

// Users view toggle (list rows ↔ cards)
function initUserViewToggle(storageKey, listId, btnListId, btnGridId) {
    var list    = document.getElementById(listId);
    var btnList = document.getElementById(btnListId);
    var btnGrid = document.getElementById(btnGridId);
    if (!list || !btnList || !btnGrid) return;

    var saved = localStorage.getItem(storageKey) || 'list';
    applyView(saved);

    btnList.addEventListener('click', function() { applyView('list'); });
    btnGrid.addEventListener('click', function() { applyView('grid'); });

    function applyView(mode) {
        if (mode === 'grid') {
            list.classList.add('users-list--grid');
            btnGrid.classList.add('is-active');
            btnList.classList.remove('is-active');
        } else {
            list.classList.remove('users-list--grid');
            btnList.classList.add('is-active');
            btnGrid.classList.remove('is-active');
        }
        localStorage.setItem(storageKey, mode);
    }
}

// Apply single query param, preserve others
function applyQueryParam(key, value) {
    var u = new URL(window.location.href);
    u.searchParams.set(key, value);
    u.searchParams.delete('page');
    window.location = u.toString();
}

// Password: toggle visibility
function togglePasswordVisibility(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        btn.title = 'Сховати пароль';
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
    } else {
        input.type = 'password';
        btn.title = 'Показати пароль';
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    }
}

// Password: generate random
function generatePassword(inputId) {
    var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$';
    var password = '';
    for (var i = 0; i < 16; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    var input = document.getElementById(inputId);
    if (input) {
        input.value = password;
        input.type = 'text';
    }
}

// Password: copy to clipboard
function copyPassword(inputId, btn) {
    var input = document.getElementById(inputId);
    if (!input || !input.value) return;
    navigator.clipboard.writeText(input.value).then(function() {
        var original = btn.innerHTML;
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
        btn.classList.add('input-group__btn--copied');
        btn.title = 'Скопійовано!';
        setTimeout(function() {
            btn.innerHTML = original;
            btn.classList.remove('input-group__btn--copied');
            btn.title = 'Копіювати пароль';
        }, 1500);
    });
}
