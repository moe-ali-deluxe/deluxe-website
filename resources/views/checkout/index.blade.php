@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white shadow rounded">

    <h2 class="text-2xl font-bold mb-4">Checkout</h2>

    {{-- Flash messages --}}
    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Out of stock details (if any) --}}
    @if(session('out_of_stock'))
        <div class="bg-yellow-100 text-yellow-900 px-4 py-2 rounded mb-4">
            The following items are out of stock: {{ implode(', ', (array) session('out_of_stock')) }}
        </div>
    @endif

    @php
        if (!isset($computed)) {
            $computed = $cart->items->map(function ($item) {
                $p = $item->product;
                $unit = 0.0;
                if ($p) {
                    $unit = ($p->discount_price !== null && $p->discount_price > 0)
                        ? (float) $p->discount_price
                        : (float) $p->price;
                } elseif (!is_null($item->price)) {
                    $unit = (float) $item->price;
                }
                $qty  = (int) $item->quantity;
                return [
                    'item'    => $item,
                    'product' => $p,
                    'qty'     => $qty,
                    'unit'    => $unit,
                    'line'    => round($unit * $qty, 2),
                ];
            });
        }
        if (!isset($subtotal)) {
            $subtotal = round(collect($computed)->sum('line'), 2);
        }
    @endphp

    <form action="{{ route('checkout.store') }}" method="POST" data-checkout-form>
        @csrf

        {{-- Cart Items --}}
        <div class="overflow-x-auto mb-6">
            <table class="w-full table-auto border border-gray-200">
                <thead>
                    <tr class="text-left border-b bg-gray-50">
                        <th class="py-2 px-2">Product</th>
                        <th class="py-2 px-2">Quantity</th>
                        <th class="py-2 px-2">Price</th>
                        <th class="py-2 px-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($computed as $row)
                        @php
                            $product = $row['product'];
                            $qty     = (int) $row['qty'];
                            $unit    = (float) $row['unit'];
                            $line    = (float) $row['line'];
                            $imgPath = $product?->images->first()->image ?? null;
                        @endphp
                        <tr class="border-b">
                            <td class="py-2 px-2">
                                <div class="flex items-center gap-3">
                                    <img
                                        src="{{ $imgPath ? asset('storage/' . $imgPath) : asset('images/placeholder.png') }}"
                                        alt="{{ $product?->name ?? 'Product' }}"
                                        class="w-16 h-16 object-cover border rounded"
                                    />
                                    <div class="flex flex-col">
                                        <span class="font-medium">
                                            {{ $product?->name ?? ('#' . $row['item']->product_id) }}
                                        </span>
                                        @if($product?->sku)
                                            <span class="text-xs text-gray-500">SKU: {{ $product->sku }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 px-2">{{ $qty }}</td>
                            <td class="py-2 px-2">${{ number_format($unit, 2) }}</td>
                            <td class="py-2 px-2">${{ number_format($line, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 px-2 text-center text-gray-600">
                                Your cart is empty.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="flex justify-end mb-6">
            <div class="text-right">
                <div class="font-semibold text-lg">
                    Subtotal: ${{ number_format($subtotal, 2) }}
                </div>
            </div>
        </div>

        {{-- Shipping Address --}}
        <div class="mb-4">
            <label class="block font-medium mb-1" for="shipping_address">Shipping Address</label>
            <textarea
                name="shipping_address"
                id="shipping_address"
                rows="3"
                class="w-full border rounded px-3 py-2"
                required
            >{{ old('shipping_address') }}</textarea>
            @error('shipping_address')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Payment Method --}}
        <div class="mb-6">
            <label class="block font-medium mb-1" for="payment_method">Payment Method</label>
            <select
                name="payment_method"
                id="payment_method"
                class="w-full border rounded px-3 py-2"
                required
            >
                <option value="">Select Payment Method</option>
                <option value="cash_on_delivery" {{ old('payment_method')=='cash_on_delivery' ? 'selected' : '' }}>
                    Cash on Delivery
                </option>
                <option value="whish_money" {{ old('payment_method')=='whish_money' ? 'selected' : '' }}>
                    WHISH MONEY
                </option>
                <option value="omt" {{ old('payment_method')=='omt' ? 'selected' : '' }}>
                    OMT
                </option>
            </select>
            @error('payment_method')
                <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center justify-center">
                Place Order
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('[data-checkout-form]');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    const btn = form.querySelector('[type="submit"]');
    if (btn) {
      btn.disabled = true;
      btn.classList.add('opacity-50');

      // Replace text with spinner
      btn.dataset.originalText = btn.innerHTML;
      btn.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
        Processing...
      `;
    }
  });
});
</script>
@endpush
