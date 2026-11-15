// DataTables owns its collection menus, even when styled as Bootstrap dropdowns.
// Intercept only their dropdown keys before Bootstrap's document capture handler.
window.addEventListener('keydown', function (event) {
    const menu = event.target.closest && event.target.closest('.dt-button-collection .dropdown-menu');
    if (!menu || !['Escape', 'ArrowUp', 'ArrowDown'].includes(event.key)) return;
    if (event.key !== 'Escape' && /^(INPUT|TEXTAREA)$/.test(event.target.tagName)) return;
    event.stopPropagation();
    // DataTables closes its collection on keyup; leave that event untouched.
    if (event.key === 'Escape') return;
    event.preventDefault();
    const items = Array.from(menu.querySelectorAll('.dropdown-item:not(.disabled):not(:disabled)'))
        .filter(item => item.getClientRects().length);
    if (!items.length) return;
    const current = items.indexOf(document.activeElement);
    const next = current < 0 ? (event.key === 'ArrowDown' ? 0 : items.length - 1)
        : (current + (event.key === 'ArrowDown' ? 1 : -1) + items.length) % items.length;
    items[next].focus();
}, true);
