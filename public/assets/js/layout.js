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
