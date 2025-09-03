@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-800 rounded">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white p-6 rounded shadow">
        {{-- Gallery --}}
        <div>
            @if ($product->images->isNotEmpty())
                <div class="grid grid-cols-2 gap-3">
                    @foreach ($product->images as $image)
                        <a href="{{ asset('storage/' . $image->image) }}" data-lightbox="product-gallery" class="block">
                            <img src="{{ asset('storage/' . $image->image) }}"
                                 alt="{{ $product->name }}"
                                 class="w-full h-56 object-contain bg-gray-50 rounded border" />
                        </a>
                    @endforeach
                </div>
            @else
                <div class="w-full h-64 flex items-center justify-center bg-gray-100 rounded border">
                    <span class="text-gray-500">No images available</span>
                </div>
            @endif
        </div>

        {{-- Info / Buy box --}}
        <div>
            <h1 class="text-2xl font-bold mb-2">{{ $product->name }}</h1>

            @php
                $hasDiscount = $product->discount_price && $product->discount_price > 0;
                $unit = $hasDiscount ? $product->discount_price : $product->price;
            @endphp

            <div class="flex items-baseline gap-3 mb-4">
                <div class="text-2xl font-semibold text-green-700">
                    ${{ number_format($unit, 2) }}
                </div>
                @if($hasDiscount)
                    <div class="text-gray-400 line-through">
                        ${{ number_format($product->price, 2) }}
                    </div>
                @endif
            </div>

            <p class="text-sm text-gray-600 mb-2">
                SKU: {{ $product->sku ?? '—' }}
            </p>

            @if(($product->stock ?? 0) > 0)
                <p class="text-sm text-green-700 mb-4">In stock: {{ $product->stock }}</p>
            @else
                <p class="text-sm text-red-600 mb-4">Out of stock</p>
            @endif

            <div class="flex items-center gap-3 mb-6">
                <label for="qty-{{ $product->id }}" class="text-sm">Qty</label>
                <input id="qty-{{ $product->id }}"
                       type="number"
                       min="1"
                       max="{{ max(1, (int)($product->stock ?? 1)) }}"
                       value="1"
                       class="w-20 border rounded px-2 py-1" />
                <button
                    class="add-to-cart bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded transition"
                    data-id="{{ $product->id }}"
                    data-qty-input="#qty-{{ $product->id }}"
                    @if(($product->stock ?? 0) <= 0) disabled @endif
                >
                    Add to Cart
                </button>
            </div>

            {{-- Wishlist toggle (works with/without JS) --}}
@auth
    @php
        // mark active without extra queries if controller passed it
        $inWishlist = $inWishlist
            ?? (isset($wishlistProducts) ? in_array($product->id, (array)$wishlistProducts) : false);
    @endphp

    <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="inline wishlist-fallback-form">
        @csrf
        <button
            class="wishlist-btn text-pink-600 hover:underline"
            data-id="{{ $product->id }}"
            data-in="{{ $inWishlist ? '1' : '0' }}"
            type="submit"
            aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
            title="{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
        >
            {!! $inWishlist ? '❤️ In wishlist (click to remove)' : '🤍 Add to Wishlist' !!}
        </button>
    </form>
@else
    <a href="{{ route('login') }}" class="text-pink-600 hover:underline">🤍 Add to Wishlist</a>
@endauth

            <div class="mt-6 prose max-w-none">
                {!! nl2br(e($product->description ?? '')) !!}
            </div>
        </div>
    </div>

    {{-- Recommended / Related (optional placeholder you can replace) --}}
    @if(isset($related) && $related->count())
        <h2 class="mt-10 mb-4 text-xl font-bold">You may also like</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($related as $rel)
                @include('products._card', ['product' => $rel])
            @endforeach
        </div>
    @endif
</div>
@endsection
