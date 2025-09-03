@php
    /** @var \App\Models\Product $product */
    $hasDiscount = $product->discount_price && $product->discount_price > 0;
    $unit        = $hasDiscount ? $product->discount_price : $product->price;
    $imgPath     = optional($product->images->first())->image;
    $inStock     = (int)($product->stock ?? 0) > 0;
@endphp

<div class="bg-white border rounded-lg shadow hover:shadow-md transition overflow-hidden flex flex-col">
    {{-- Image / link --}}
    <a href="{{ route('products.show', $product->id) }}" class="block bg-gray-50">
        <img
          src="{{ $imgPath ? asset('storage/' . $imgPath) : asset('placeholder.png') }}"
          alt="{{ $product->name }}"
          class="w-full h-44 object-contain p-3" />
    </a>

    {{-- Body --}}
    <div class="p-4 flex-1 flex flex-col">
        {{-- Title --}}
        <a href="{{ route('products.show', $product->id) }}" class="font-semibold line-clamp-2 mb-1 hover:underline">
            {{ $product->name }}
        </a>

        {{-- Price --}}
        <div class="mb-2">
            <span class="text-green-700 font-bold">${{ number_format($unit, 2) }}</span>
            @if($hasDiscount)
                <span class="text-gray-400 line-through ml-2">${{ number_format($product->price, 2) }}</span>
            @endif
        </div>

        {{-- Stock badge --}}
        <div class="mb-3">
            @if($inStock)
                <span class="inline-block text-xs px-2 py-0.5 rounded bg-green-100 text-green-700">
                    In stock: {{ $product->stock }}
                </span>
            @else
                <span class="inline-block text-xs px-2 py-0.5 rounded bg-red-100 text-red-700">
                    Out of stock
                </span>
            @endif
        </div>

        {{-- Actions --}}
        <div class="mt-auto flex items-center gap-2">
            <input
                id="qty-{{ $product->id }}"
                type="number"
                min="1"
                value="1"
                class="w-16 border rounded px-2 py-1"
                @unless($inStock) disabled @endunless
            >
            <button
                class="add-to-cart bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                data-id="{{ $product->id }}"
                data-qty-input="#qty-{{ $product->id }}"
                @unless($inStock) disabled @endunless
            >
                Add
            </button>

            {{-- Optional: quick wishlist for authenticated users --}}
            @auth
                <form action="{{ route('wishlist.add', $product->id) }}" method="POST" class="ml-auto">
                    @csrf
                    <button type="submit" class="text-pink-600 hover:underline text-sm">❤️ Wishlist</button>
                </form>
            @endauth
        </div>
    </div>
</div>
