@extends('layouts.app')

@section('title', 'Thank You')

{{-- Search engines shouldn’t index the order confirmation --}}
@push('meta')
<meta name="robots" content="noindex,nofollow">
@endpush

@section('content')
<div class="max-w-3xl mx-auto py-12">
  <div class="bg-white rounded-lg shadow p-6 text-center">
    <h1 class="text-3xl font-bold text-green-600 mb-2">🎉 Thank you!</h1>
    <p class="text-gray-700">
      Your order <span class="font-semibold">#{{ $order->id }}</span> was placed successfully.
    </p>

    {{-- Mini order summary --}}
    <div class="mt-6 text-left">
      <h2 class="text-lg font-semibold mb-2">Order summary</h2>
      <ul class="divide-y">
        @foreach($order->items as $item)
          <li class="py-2 flex justify-between">
            <span class="text-gray-700">
              {{ $item->product->name ?? ('#'.$item->product_id) }} × {{ $item->quantity }}
            </span>
            <span class="text-gray-900 font-medium">${{ number_format($item->price * $item->quantity, 2) }}</span>
          </li>
        @endforeach
      </ul>
      <div class="mt-3 flex justify-between text-lg font-semibold">
        <span>Total</span>
        <span>${{ number_format($order->total_amount, 2) }}</span>
      </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-3 justify-center">
      <a href="{{ route('products.index') }}"
         class="px-5 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
        Continue shopping
      </a>
      @auth
        <a href="{{ route('orders.index') }}"
           class="px-5 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-900">
          View my orders
        </a>
      @endauth
    </div>

    {{-- Optional success flash --}}
    @if(session('success'))
      <p class="mt-4 text-sm text-gray-500">{{ session('success') }}</p>
    @endif
  </div>
</div>
@endsection
