@extends('layouts.app')

@section('title', 'My Wishlist')
@section('canonical', route('wishlist.index'))
@section('robots', 'noindex,follow')

@section('content')
@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
        {{ session('success') }}
    </div>
@endif

@php $count = $items->count(); @endphp

<div class="max-w-5xl mx-auto p-6 bg-white rounded shadow">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">My Wishlist</h1>
        <div class="text-sm text-gray-500">
            <span id="wishlist-total">{{ $count }}</span>
            item<span id="wishlist-total-s">{{ $count === 1 ? '' : 's' }}</span>
        </div>
    </div>

    @if($items->isEmpty())
        <div class="text-gray-600 text-center">
            Your wishlist is empty.
            <a href="{{ route('products.index') }}" class="text-indigo-600 underline ml-1">Browse products</a>
        </div>
    @else
        <div id="wishlist-grid" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($items as $product)
                <div class="wishlist-card border rounded p-4 flex flex-col" data-product-id="{{ $product->id }}">
                    <a href="{{ route('products.show', $product) }}" class="mb-4 block">
                        @php
                            $img = optional($product->images->sortBy([
                                ['is_primary', 'desc'],
                                ['sort_order', 'asc'],
                                ['id', 'asc'],
                            ])->first());
                            $imgUrl = $img?->image ? asset('storage/' . $img->image) : asset('images/placeholder.png');
                            $imgAlt = $img?->alt ?? $product->name;
                        @endphp

                        <img src="{{ $imgUrl }}" alt="{{ e($imgAlt) }}" class="w-full h-48 object-cover rounded">
                    </a>

                    <h2 class="text-base font-semibold mb-1 line-clamp-2">
                        <a href="{{ route('products.show', $product) }}" class="hover:underline">
                            {{ $product->name }}
                        </a>
                    </h2>

                    <p class="text-indigo-600 font-bold mb-3">
                        ${{ number_format($product->discount_price ?? $product->price, 2) }}
                    </p>

                    {{-- Remove via AJAX (DELETE) --}}
                    <form action="{{ route('wishlist.remove', $product) }}" method="POST" class="mt-auto remove-from-wishlist">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded w-full">
                            Remove from Wishlist
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function() {
    $('form.remove-from-wishlist').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var url   = $form.attr('action');
        var $btn  = $form.find('button[type=submit]');
        var $card = $form.closest('.wishlist-card');

        $btn.prop('disabled', true).text('Removing...');

        $.ajax({
            url: url,
            type: 'DELETE',
            data: $form.serialize(),
            headers: { 'Accept': 'application/json' },
            success: function(response) {
                // Remove the product card with a fade
                $card.fadeOut(150, function() {
                    $(this).remove();

                    // Update header count + pluralization
                    var remaining = $('#wishlist-grid .wishlist-card').length;
                    $('#wishlist-total').text(remaining);
                    if (remaining === 1) {
                        $('#wishlist-total-s').text('');
                    } else {
                        $('#wishlist-total-s').text('s');
                    }

                    // Navbar badge (if server returned a count)
                    if (typeof response !== 'undefined' && typeof response.count !== 'undefined') {
                        var $badge = $('#wishlist-count');
                        if ($badge.length) {
                            if (response.count > 0) {
                                $badge.text(String(response.count)).removeClass('hidden');
                            } else {
                                $badge.text('0').addClass('hidden');
                            }
                        } else if (typeof window.setWishlistBadge === 'function') {
                            // Support a global helper if you add one later
                            window.setWishlistBadge(response.count);
                        }
                    }

                    // If no cards left, show empty state
                    if (remaining === 0) {
                        $('#wishlist-grid').replaceWith(
                            '<div class="text-gray-600 text-center">' +
                                'Your wishlist is empty. ' +
                                '<a href="{{ route('products.index') }}" class="text-indigo-600 underline ml-1">Browse products</a>' +
                            '</div>'
                        );
                    }
                });
            },
            error: function(xhr) {
                alert((xhr && xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.');
                $btn.prop('disabled', false).text('Remove from Wishlist');
            }
        });
    });
});
</script>
@endpush
