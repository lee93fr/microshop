import './bootstrap';

// ── Margin calculator on product form ─────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const purchaseInput = document.querySelector('[name="purchase_price"]');
    const saleInput     = document.querySelector('[name="sale_price"]');
    const marginDisplay = document.getElementById('margin-display');

    if (purchaseInput && saleInput && marginDisplay) {
        const updateMargin = () => {
            const p = parseFloat(purchaseInput.value) || 0;
            const s = parseFloat(saleInput.value)     || 0;
            if (p > 0) {
                const margin = ((s - p) / p * 100).toFixed(2);
                const color  = margin >= 30 ? '#16a34a' : margin >= 15 ? '#d97706' : '#dc2626';
                marginDisplay.innerHTML = `<span style="color:${color};font-weight:600;">${margin}%</span>`;
            } else {
                marginDisplay.textContent = '—';
            }
        };
        purchaseInput.addEventListener('input', updateMargin);
        saleInput.addEventListener('input', updateMargin);
        updateMargin();
    }

    // ── Flash messages auto-dismiss ───────────────────────────
    const flashes = document.querySelectorAll('[data-flash]');
    flashes.forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity    = '0';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });

    // ── Order items dynamic rows (admin manual order) ─────────
    const addItemBtn = document.getElementById('add-item-row');
    if (addItemBtn) {
        const tbody    = document.getElementById('items-tbody');
        const template = document.getElementById('item-row-template');
        let rowIndex   = tbody.querySelectorAll('tr').length;

        addItemBtn.addEventListener('click', () => {
            const clone = template.content.cloneNode(true);
            clone.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace('__IDX__', rowIndex);
            });
            tbody.appendChild(clone);
            rowIndex++;
            updateOrderTotal();
        });

        tbody.addEventListener('input', updateOrderTotal);
        tbody.addEventListener('click', e => {
            if (e.target.closest('[data-remove-row]')) {
                e.target.closest('tr').remove();
                updateOrderTotal();
            }
        });
    }

    function updateOrderTotal() {
        const rows     = document.querySelectorAll('#items-tbody tr');
        let subtotal   = 0;
        rows.forEach(row => {
            const qty   = parseFloat(row.querySelector('[name*="quantity"]')?.value)   || 0;
            const price = parseFloat(row.querySelector('[name*="unit_price"]')?.value) || 0;
            subtotal   += qty * price;
        });
        const discount  = parseFloat(document.querySelector('[name="discount"]')?.value) || 0;
        const totalEl   = document.getElementById('order-total-display');
        if (totalEl) totalEl.textContent = (subtotal - discount).toFixed(2) + ' €';
    }

    // ── Copier un lien dans le presse-papier ──────────────────
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-copy-link]');
        if (!btn) return;
        e.preventDefault();
        const url = btn.dataset.copyLink;
        const original = btn.innerHTML;
        const flash = (ok) => {
            btn.innerHTML = ok ? '✓ Lien copié' : '⚠ Échec — ' + url;
            btn.classList.toggle('bg-green-100', ok);
            btn.classList.toggle('text-green-700', ok);
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('bg-green-100', 'text-green-700');
            }, ok ? 2000 : 6000);
        };
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(url);
                flash(true);
            } else {
                const ta = document.createElement('textarea');
                ta.value = url;
                ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                const ok = document.execCommand('copy');
                document.body.removeChild(ta);
                flash(ok);
            }
        } catch {
            flash(false);
        }
    });

    // ── Stepper panier en AJAX (pas de rechargement, scroll préservé) ──
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    function updateCartCount(count) {
        document.querySelectorAll('[data-cart-count]').forEach(el => {
            if (count > 0) {
                el.textContent = count;
                el.classList.remove('hidden');
                el.classList.add('inline-flex');
            } else {
                el.textContent = '';
                el.classList.add('hidden');
                el.classList.remove('inline-flex');
            }
        });
    }

    function formatEuro(amount) {
        return (Math.round(amount * 100) / 100)
            .toFixed(2)
            .replace('.', ',')
            .replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' €';
    }

    function updateCartTotal(total) {
        const amount = Number(total) || 0;
        document.querySelectorAll('[data-cart-total]').forEach(el => {
            el.textContent = formatEuro(amount);
            el.classList.toggle('hidden', amount <= 0);
        });
    }

    function updateProductBadge(productId, qty) {
        document.querySelectorAll(`[data-product-badge="${productId}"]`).forEach(el => {
            if (qty > 0) {
                el.textContent = qty;
                el.classList.remove('hidden');
                el.classList.add('flex');
            } else {
                el.textContent = '';
                el.classList.add('hidden');
                el.classList.remove('flex');
            }
        });
    }

    function syncSteppers(productId, qty) {
        document.querySelectorAll(`[data-cart-stepper][data-product-id="${productId}"]`).forEach(stepper => {
            const input = stepper.querySelector('[data-cart-qty]');
            const dec   = stepper.querySelector('[data-cart-dec]');
            const reset = stepper.querySelector('[data-cart-reset]');
            if (input && document.activeElement !== input) input.value = qty;
            if (dec)   dec.disabled = qty <= 0;
            if (reset) reset.classList.toggle('hidden', qty <= 0);
        });
    }

    function showCartToast(msg, isError = false) {
        const toast = document.createElement('div');
        toast.className = 'fixed top-16 md:top-20 left-4 z-[100] rounded-xl border px-4 py-3 text-sm shadow-lg transition-opacity ' +
            (isError ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800');
        toast.textContent = (isError ? '❌ ' : '✅ ') + msg;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 2500);
    }

    function setStepperBusy(stepper, busy) {
        stepper.querySelectorAll('button, input').forEach(el => { el.disabled = busy; });
        if (!busy) {
            const input = stepper.querySelector('[data-cart-qty]');
            const dec   = stepper.querySelector('[data-cart-dec]');
            if (input && dec) dec.disabled = (parseInt(input.value, 10) || 0) <= 0;
        }
    }

    async function cartRequest(productId, action, quantity) {
        const url = `/mon-compte/panier/${productId}`;
        const headers = {
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        let method, body = null;
        if (action === 'add') {
            method = 'POST';
            body = new URLSearchParams({ quantity: String(quantity ?? 1) });
        } else if (action === 'set') {
            method = 'PATCH';
            body = new URLSearchParams({ quantity: String(quantity ?? 0) });
        } else if (action === 'remove') {
            method = 'DELETE';
        }
        const res = await fetch(url, { method, headers, body });
        if (!res.ok) {
            let detail = '';
            try { const j = await res.json(); detail = j.message || ''; } catch {}
            throw new Error('HTTP ' + res.status + (detail ? ' — ' + detail : ''));
        }
        return res.json();
    }

    async function runCartAction(stepper, action, quantity) {
        const productId = stepper.dataset.productId;
        if (!productId) return;
        setStepperBusy(stepper, true);
        try {
            const data = await cartRequest(productId, action, quantity);
            const qty = data.productQty ?? 0;
            syncSteppers(data.productId ?? productId, qty);
            updateProductBadge(data.productId ?? productId, qty);
            updateCartCount(data.count);
            if (data.total !== undefined) updateCartTotal(data.total);
            if (data.message) showCartToast(data.message);
        } catch (err) {
            showCartToast('Impossible de mettre à jour le panier.', true);
        } finally {
            setStepperBusy(stepper, false);
        }
    }

    document.addEventListener('click', (e) => {
        const inc   = e.target.closest('[data-cart-inc]');
        const dec   = e.target.closest('[data-cart-dec]');
        const reset = e.target.closest('[data-cart-reset]');
        const trigger = inc || dec || reset;
        if (!trigger) return;
        const stepper = trigger.closest('[data-cart-stepper]');
        if (!stepper) return;
        e.preventDefault();

        const input = stepper.querySelector('[data-cart-qty]');
        const current = parseInt(input?.value, 10) || 0;

        if (inc) {
            runCartAction(stepper, 'add', 1);
        } else if (dec) {
            runCartAction(stepper, 'set', Math.max(0, current - 1));
        } else {
            runCartAction(stepper, 'remove');
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter') return;
        const input = e.target.closest('[data-cart-qty]');
        if (!input) return;
        e.preventDefault();
        input.blur();
    });

    document.addEventListener('change', (e) => {
        const input = e.target.closest('[data-cart-qty]');
        if (!input) return;
        const stepper = input.closest('[data-cart-stepper]');
        if (!stepper) return;
        const qty = Math.max(0, Math.min(999, parseInt(input.value, 10) || 0));
        runCartAction(stepper, 'set', qty);
    });
});
