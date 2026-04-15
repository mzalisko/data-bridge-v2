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

// Client-side search — filters DOM rows without page reload
// inputId:       id of the <input> element
// itemsSelector: CSS selector for filterable rows (e.g. '.site-card', '.group-row')
// searchAttr:    data-attribute that holds searchable text (default: 'data-searchable')
function initClientSearch(inputId, itemsSelector, searchAttr) {
    searchAttr = searchAttr || 'data-searchable';
    var input = document.getElementById(inputId);
    if (!input) return;

    // Restore focus if search value came from URL (back-navigation)
    if (input.value) input.focus();

    input.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        document.querySelectorAll(itemsSelector).forEach(function (el) {
            var text = (el.getAttribute(searchAttr) || el.textContent).toLowerCase();
            el.style.display = text.includes(q) ? '' : 'none';
        });
    });
}

// Geo visibility: toggle countries chips and active card
function geoToggle(prefix, mode) {
    // show/hide countries wrap
    var wrap = document.getElementById('geo-countries-' + prefix);
    if (wrap) {
        wrap.style.display = (mode === 'include' || mode === 'exclude') ? '' : 'none';
    }
    // update active class on tiles — use radio.value === mode
    // (onchange fires after check, so radio.checked is already updated)
    var grid = document.getElementById('geo-grid-' + prefix);
    if (grid) {
        grid.querySelectorAll('.geo-option').forEach(function(opt) {
            var radio = opt.querySelector('input[type=radio]');
            opt.classList.toggle('is-active', radio && radio.value === mode);
        });
    }
}

// Geo: rebuild hidden field from checked chip-checkboxes
function geoUpdateCountries(prefix) {
    var chips = document.getElementById('geo-chips-' + prefix);
    var hidden = document.getElementById('geo-countries-input-' + prefix);
    if (!chips || !hidden) return;
    var checked = [];
    chips.querySelectorAll('.geo-chip-cb:checked').forEach(function(cb) {
        checked.push(cb.value);
    });
    hidden.value = checked.join(', ');
}

// Favorites: toggle via AJAX
function toggleFavorite(e, btn, siteId) {
    if (e && e.stopPropagation) e.stopPropagation();

    // Debounce: ignore if request already in flight
    if (btn.dataset.pending) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrf) return;

    // Optimistic UI: flip immediately, before server responds
    const wasFav = btn.classList.contains('is-fav');
    btn.dataset.pending = '1';
    if (wasFav) {
        btn.classList.remove('is-fav');
        btn.title = 'Додати до улюблених';
    } else {
        btn.classList.add('is-fav');
        btn.title = 'Прибрати з улюблених';
    }

    fetch('/sites/' + siteId + '/favorite', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        // Sync exact server state
        if (data.favorite) {
            btn.classList.add('is-fav');
            btn.title = 'Прибрати з улюблених';
        } else {
            btn.classList.remove('is-fav');
            btn.title = 'Додати до улюблених';

            // Dashboard favorites sidebar: animate-out the removed item
            const li = btn.closest('li');
            const title = btn.closest('.db-card')?.querySelector('.db-card__title')?.textContent;
            if (li && title && title.includes('Улюблені')) {
                li.style.opacity = '0';
                li.style.transform = 'translateX(20px)';
                li.style.transition = 'all 0.3s ease';
                setTimeout(() => { li.remove(); }, 300);
            }
        }
    })
    .catch(() => {
        // Revert on error
        if (wasFav) {
            btn.classList.add('is-fav');
            btn.title = 'Прибрати з улюблених';
        } else {
            btn.classList.remove('is-fav');
            btn.title = 'Додати до улюблених';
        }
    })
    .finally(() => {
        delete btn.dataset.pending;
    });
}

