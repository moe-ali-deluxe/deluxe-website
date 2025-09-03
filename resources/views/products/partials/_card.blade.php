{{-- resources/views/products/partials/_card.blade.php --}}

@php
    $imgModel = optional(
        $product->images->sortBy(function ($i) {
            return [($i->is_primary ? 0 : 1), $i->sort_order ?? 9999, $i->id];
        })->first()
    );

    $imgPath = $imgModel->image ?? null;
    $imgAlt  = $imgModel->alt   ?? $product->name;
    $imgUrl  = $imgPath ? asset('storage/' . $imgPath) : asset('images/placeholder.png');

    $hasDiscount = !is_null($product->discount_price) && $product->discount_price > 0;
    $priceNow    = $product->display_price;
    $priceOld    = $product->price;
    $isNew       = (bool) ($product->is_new ?? false);
    $inStock     = (int) ($product->stock ?? 0) > 0;

    $inWishlist = in_array($product->id, $wishlistProducts ?? []);
@endphp

<div class="group relative bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition h-full flex flex-col">

    {{-- Absolute wishlist toggle (not inside the link to avoid accidental navigation) --}}
    @auth
        <form action="{{ route('wishlist.toggle', $product->id) }}" method="POST" class="wishlist-fallback-form">
            @csrf
            <button
                class="wishlist-btn absolute top-2 right-2 text-xl {{ $inWishlist ? 'is-active' : '' }}"
                data-id="{{ $product->id }}"
                data-in="{{ $inWishlist ? '1' : '0' }}"
                aria-pressed="{{ $inWishlist ? 'true' : 'false' }}"
                title="{{ $inWishlist ? 'Remove from wishlist' : 'Add to wishlist' }}"
                type="submit"
            >
                {!! $inWishlist ? '❤️' : '🤍' !!}
            </button>
        </form>
    @else
        <a href="{{ route('login') }}" class="absolute top-2 right-2 text-xl" title="Login to use wishlist">🤍</a>
    @endauth

    {{-- Image + badges (clicking anywhere here goes to product page) --}}
    <a href="{{ route('products.show', $product) }}" class="block relative">
        {{-- Square image frame --}}
        <div class="w-full aspect-square bg-gray-50 overflow-hidden">
            <img
                src="{{ $imgUrl }}"
                alt="{{ e($imgAlt) }}"
                loading="lazy"
                width="600" height="600"
                class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-[1.02]">
        </div>

        {{-- Badges (top-left) --}}
        <div class="absolute top-2 left-2 flex gap-2">
            @if($isNew)
                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-emerald-600 text-white">New</span>
            @endif

            @if($hasDiscount)
                @php
                    $discountPct = $priceOld > 0 ? round((1 - ($priceNow / $priceOld)) * 100) : 0;
                @endphp
                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-rose-600 text-white">-{{ $discountPct }}%</span>
            @endif

            @unless($inStock)
                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-gray-800 text-white">Out of stock</span>
            @endunless
        </div>
    </a>

    {{-- Content --}}
    <div class="p-3 flex-1 flex flex-col">
        <a href="{{ route('products.show', $product) }}" class="block">
            {{-- Lock title to 2 lines high --}}
            <h3 class="text-sm leading-5 font-medium text-gray-800 line-clamp-2 h-10">
                {{ $product->name }}
            </h3>
        </a>

        {{-- Price row: fixed height --}}
        <div class="mt-2 flex items-baseline gap-2 h-7">
            <span class="text-lg leading-7 font-semibold text-gray-900">
                ${{ number_format($priceNow, 2) }}
            </span>
            @if($hasDiscount)
                <span class="text-sm leading-5 text-gray-500 line-through">
                    ${{ number_format($priceOld, 2) }}
                </span>
            @else
                {{-- Invisible placeholder so height stays equal --}}
                <span class="text-sm leading-5 opacity-0 select-none">0.00</span>
            @endif
        </div>

        @if($inStock && $product->stock <= 5)
            <div class="mt-1 text-xs leading-4 text-amber-700">
                Only {{ (int)$product->stock }} left
            </div>
        @endif

        {{-- Actions pinned to bottom --}}
        <div class="mt-auto pt-3 flex items-center gap-2">
            <button
                class="add-to-cart inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50"
                data-id="{{ $product->id }}"
                type="button"
                {{ $inStock ? '' : 'disabled' }}
                title="{{ $inStock ? 'Add to cart' : 'Out of stock' }}"
            >
                🛒 Add to Cart
            </button>

            <a href="{{ route('products.show', $product) }}"
               class="text-sm text-gray-600 hover:text-gray-900 underline">
                Details
            </a>
        </div>
    </div>
</div>
