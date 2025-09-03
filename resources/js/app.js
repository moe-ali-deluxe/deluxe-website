import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

/* -------------------- Utilities -------------------- */
function csrfToken() {
  const el = document.querySelector('meta[name="csrf-token"]');
  return el ? el.getAttribute('content') : '';
}

function setCartBadge(count) {
  const n = Number(count) || 0;
  const els = [
    document.getElementById('cart-count'),         // desktop
    document.getElementById('cart-count-mobile'),  // mobile
  ];
  els.forEach((el) => {
    if (!el) return;
    el.textContent = String(n);
    if (n > 0) el.classList.remove('hidden');
    else el.classList.add('hidden');
  });
}
window.setCartBadge = setCartBadge; // optional global

function setWishlistBadge(count) {
  const el = document.getElementById('wishlist-count');
  if (!el) return;
  const n = Number(count) || 0;
  el.textContent = String(n);
  if (n > 0) el.classList.remove('hidden');
  else el.classList.add('hidden');
}
window.setWishlistBadge = setWishlistBadge; // optional global

async function postJson(url, payload = {}) {
  // Prefer axios (Laravel default)
  if (window.axios) {
    const { data } = await window.axios.post(url, payload, {
      headers: { 'X-CSRF-TOKEN': csrfToken() }
    });
    return data;
  }

  // Fallback to fetch (URL-encoded)
  const body = new URLSearchParams();
  Object.entries(payload).forEach(([k, v]) => body.append(k, v));

  const res = await fetch(url, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': csrfToken(),
      'Accept': 'application/json',
      'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
    },
    body
  });

  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data?.error || `Request failed (${res.status})`);
    err.response = { data, status: res.status };
    throw err;
  }
  return data;
}

async function deleteJson(url, payload = {}) {
  const body = new URLSearchParams();
  Object.entries(payload).forEach(([k, v]) => body.append(k, v));

  if (window.axios) {
    return window.axios.delete(url, {
      data: payload,
      headers: { 'X-CSRF-TOKEN': csrfToken() }
    }).then(r => r.data);
  }

  const res = await fetch(url, {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': csrfToken(),
      'Accept': 'application/json',
      'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
    },
    body
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok) throw { response: { data, status: res.status } };
  return data;
}

/* -------------------- Delegated handlers -------------------- */
// Add-to-cart (captures clicks on any .add-to-cart)
function handleAddToCartClick(e) {
  const btn = e.target.closest('.add-to-cart');
  if (!btn) return;

  e.preventDefault();

  const id = btn.dataset.id;
  if (!id) return;

  const qtySelector = btn.dataset.qtyInput;
  const qty = qtySelector
    ? Math.max(1, parseInt(document.querySelector(qtySelector)?.value || '1', 10) || 1)
    : 1;

  btn.disabled = true;
  (async () => {
    try {
      const data = await postJson(`/cart/add/${id}`, { quantity: qty });
      // Expecting { cart: { count, subtotal? }, success? }
      setCartBadge(data?.cart?.count ?? 0);
      if (window.toast && data?.success) window.toast.success(data.success);
    } catch (err) {
      const msg = err?.response?.data?.error || 'Could not add to cart';
      if (window.toast) window.toast.error(msg);
      console.error(err);
    } finally {
      btn.disabled = false;
    }
  })();
}

// Cart quantity change (captures changes on inputs with data-cart-qty & data-product-id)
function handleCartQtyChange(e) {
  const input = e.target.closest('[data-cart-qty][data-product-id]');
  if (!input) return;

  const id = input.dataset.productId;
  const qty = Math.max(0, parseInt(input.value || '0', 10) || 0);

  (async () => {
    try {
      const data = await postJson(`/cart/update/${id}`, { quantity: qty });
      setCartBadge(data?.cart?.count ?? 0);

      // Optional: update subtotal element if you render it
      const subtotalEl = document.getElementById('cart-subtotal');
      if (subtotalEl && data?.cart?.subtotal != null) {
        subtotalEl.textContent = Number(data.cart.subtotal).toFixed(2);
      }

      // Optional: update per-line total if you have an element with id="line-total-<id>"
      const row = input.closest('tr');
      const unitPriceText = row?.querySelector('td:nth-child(2)')?.textContent || '';
      const unit = parseFloat(unitPriceText.replace(/[^0-9.]/g, '')) || 0;
      const lineEl = document.getElementById(`line-total-${id}`);
      if (lineEl) lineEl.textContent = (unit * qty).toFixed(2);
    } catch (err) {
      const msg = err?.response?.data?.error || 'Could not update quantity';
      if (window.toast) window.toast.error(msg);
      console.error(err);
    }
  })();
}

// Wishlist toggle (captures clicks on any .wishlist-btn)
function wireWishlist() {
  document.body.addEventListener('click', async (e) => {
    const btn = e.target.closest('.wishlist-btn');
    if (!btn) return;

    e.preventDefault();

    const id = btn.dataset.id;
    const mode = btn.dataset.mode || 'toggle'; // 'add' | 'remove' | 'toggle'
    const addUrl = `/wishlist/${id}/add`;
    const delUrl = `/wishlist/${id}/remove`;
    const togUrl = `/wishlist/${id}/toggle`;

    btn.disabled = true;
    try {
      let resp;
      if (mode === 'toggle') {
        resp = await postJson(togUrl);
      } else if (mode === 'add') {
        resp = await postJson(addUrl);
      } else {
        resp = await deleteJson(delUrl);
      }

      if (resp && typeof resp.count !== 'undefined') {
        setWishlistBadge(resp.count);
      }

      if (resp?.action === 'added') {
        btn.classList.add('is-active');
        btn.setAttribute('aria-pressed', 'true');
        btn.title = 'Remove from wishlist';
        btn.dataset.mode = 'toggle';
        btn.innerHTML = '❤️';
      } else if (resp?.action === 'removed') {
        btn.classList.remove('is-active');
        btn.setAttribute('aria-pressed', 'false');
        btn.title = 'Add to wishlist';
        btn.dataset.mode = 'toggle';
        btn.innerHTML = '🤍';
      }

      if (window.toast && resp?.msg) window.toast.success(resp.msg);
    } catch (err) {
      const status = err?.response?.status;
      if (status === 401) {
        window.location.href = '/login';
        return;
      }
      const msg = err?.response?.data?.message || 'Wishlist action failed';
      if (window.toast) window.toast.error(msg);
      console.error(err);
    } finally {
      btn.disabled = false;
    }
  });
}

/* -------------------- Init -------------------- */
document.addEventListener('DOMContentLoaded', () => {
  // One clean init: delegate clicks/changes + wishlist
  document.addEventListener('click', handleAddToCartClick);
  document.addEventListener('change', handleCartQtyChange);
  wireWishlist();
});
