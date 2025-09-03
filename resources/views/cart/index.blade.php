@extends('layouts.app')

@section('title', 'Your Cart')
@section('canonical', route('cart.index'))
@section('robots', 'noindex,nofollow')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Your Cart</h1>

    @php
        $cart = $cart ?? session('cart', []);
        $lines = [];
        $subtotal = 0;

        foreach ($products as $p) {
            $qty = (int) ($cart[$p->id] ?? 0);
            if ($qty <= 0) continue;

            $price = ($p->discount_price && $p->discount_price > 0) ? $p->discount_price : $p->price;
            $lineTotal = $price * $qty;
            $subtotal += $lineTotal;

            // primary → lowest sort_order → first
            $imgModel = optional(
                $p->images->sortBy(fn($i) => [($i->is_primary ? 0 : 1), $i->sort_order ?? 9999, $i->id])->first()
            );
            $imgPath = $imgModel->image ?? null;
            $imgAlt  = $imgModel->alt   ?? $p->name;
            $imgUrl  = $imgPath ? asset('storage/' . $imgPath) : asset('images/placeholder.png');

            $lines[] = compact('p','qty','price','lineTotal','imgUrl','imgAlt');
        }
    @endphp

    @if(empty($lines))
        <div class="bg-white p-6 rounded shadow text-center text-gray-600">
            Your cart is empty.
        </div>
    @else
        <div class="bg-white p-4 rounded shadow overflow-x-auto">
            <table class="min-w-full align-top">
                <thead class="text-left text-sm text-gray-500 border-b">
                    <tr>
                        <th class="py-2">Product</th>
                        <th class="py-2">Price</th>
                        <th class="py-2">Qty</th>
                        <th class="py-2">Total</th>
                        <th class="py-2"></th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @foreach($lines as $row)
                        @php
                            /** @var \App\Models\Product $p */
                            [$p,$qty,$price,$lineTotal,$imgUrl,$imgAlt] = [$row['p'],$row['qty'],$row['price'],$row['lineTotal'],$row['imgUrl'],$row['imgAlt']];
                        @endphp
                        <tr class="border-b">
                            <td class="py-3">
                                <div class="flex items-center gap-3">
                                    {{-- Square, non-distorted thumb --}}
                                    <div class="w-16 aspect-square bg-gray-50 border rounded overflow-hidden shrink-0">
                                        <img
                                            src="{{ $imgUrl }}"
                                            alt="{{ e($imgAlt) }}"
                                            loading="lazy"
                                            width="64" height="64"
                                            class="w-full h-full object-contain"
                                        >
                                    </div>

                                    <div class="min-w-[12rem]">
                                        <a href="{{ route('products.show', $p) }}" class="font-medium hover:underline line-clamp-2">
                                            {{ $p->name }}
                                        </a>
                                        @if($p->sku ?? false)
                                            <div class="text-xs text-gray-500 mt-0.5">SKU: {{ $p->sku }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="py-3 whitespace-nowrap align-middle">
                                ${{ number_format($price, 2) }}
                            </td>

                            <td class="py-3 align-middle">
                                <input type="number"
                                       min="0"
                                       value="{{ $qty }}"
                                       data-cart-qty
                                       data-product-id="{{ $p->id }}"
                                       class="w-24 border rounded px-2 py-1" />
                                <p class="text-xs text-gray-400 mt-1">Set to 0 to remove</p>
                            </td>

                            <td class="py-3 whitespace-nowrap align-middle">
                                $<span id="line-total-{{ $p->id }}">{{ number_format($lineTotal, 2) }}</span>
                            </td>

                            <td class="py-3 align-middle">
                                <form action="{{ route('cart.remove', $p->id) }}" method="POST" onsubmit="return confirm('Remove item?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="3" class="py-3 text-right font-semibold">Subtotal</td>
                        <td class="py-3 font-semibold whitespace-nowrap">
                            $<span id="cart-subtotal">{{ number_format($subtotal, 2) }}</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-6 flex items-center gap-3">
                <form action="{{ route('cart.clear') }}" method="POST" onsubmit="return confirm('Clear cart?');">
                    @csrf
                    <button class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">Clear Cart</button>
                </form>

                @auth
                    <a href="{{ route('checkout.index') }}"
                       data-checkout-link
                       class="ml-auto px-5 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Proceed to Checkout
                    </a>
                @else
                    <a href="{{ route('checkout.index') }}"
                       data-checkout-link
                       class="ml-auto px-5 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Login to Checkout
                    </a>
                @endauth
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Fallback badge updater if not defined globally elsewhere
  if (typeof window.setCartBadge !== 'function') {
    window.setCartBadge = function(count) {
      var c = Number(count) || 0;
      var el = document.getElementById('cart-count');
      var elM = document.getElementById('cart-count-mobile');
      if (el)  { if (c > 0) { el.textContent = c; el.classList.remove('hidden'); } else { el.textContent = '0'; el.classList.add('hidden'); } }
      if (elM) { if (c > 0) { elM.textContent = c; elM.classList.remove('hidden'); } else { elM.textContent = '0'; elM.classList.add('hidden'); } }
    };
  }

  const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const fmt = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const postForm = async (url, payload = {}) => {
    const body = new URLSearchParams();
    Object.entries(payload).forEach(([k, v]) => body.append(k, v));
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
      },
      body
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw data;
    return data;
  };

  const debounce = (fn, ms = 250) => {
    let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
  };

  // Block checkout while qty updates are in-flight (avoid racing)
  let inflight = 0;
  const checkoutA = document.querySelector('[data-checkout-link]');
  const setBusy = (busy) => {
    if (!checkoutA) return;
    checkoutA.classList.toggle('pointer-events-none', busy);
    checkoutA.classList.toggle('opacity-60', busy);
    checkoutA.setAttribute('aria-disabled', busy ? 'true' : 'false');
  };

  document.querySelectorAll('[data-cart-qty][data-product-id]').forEach((input) => {
    const handler = debounce(async () => {
      const id  = input.dataset.productId;
      const qty = Math.max(0, parseInt(input.value || '0', 10) || 0);
      input.disabled = true;
      inflight++; setBusy(true);

      try {
        const data = await postForm(`/cart/update/${id}`, { quantity: qty });

        // Update navbar badge if count present
        if (data?.cart?.count != null) window.setCartBadge(data.cart.count);

        // Update subtotal if present
        if (data?.cart?.subtotal != null) {
          const sub = Number(data.cart.subtotal) || 0;
          document.getElementById('cart-subtotal').textContent = fmt.format(sub);
        }

        // Update line total if present, else compute
        if (data?.line?.total != null) {
          document.getElementById(`line-total-${id}`).textContent = fmt.format(Number(data.line.total) || 0);
        } else {
          const row = input.closest('tr');
          const unitPriceText = row?.querySelector('td:nth-child(2)')?.textContent || '';
          const unit = parseFloat(unitPriceText.replace(/[^0-9.]/g, '')) || 0;
          document.getElementById(`line-total-${id}`).textContent = fmt.format(unit * qty);
        }

        // If server removed the line (qty 0), refresh
        if (qty === 0 && (data?.removed || data?.cart?.removed)) {
          location.reload();
        }

      } catch (e) {
        alert((e && (e.error || e.message)) ? (e.error || e.message) : 'Could not update quantity');
      } finally {
        input.disabled = false;
        inflight--; if (inflight <= 0) { inflight = 0; setBusy(false); }
      }
    }, 300);

    input.addEventListener('input', handler);
    input.addEventListener('change', handler);
  });
});
</script>
@endpush
