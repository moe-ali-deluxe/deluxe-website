@extends('layouts.app')

{{-- Dynamic page title (same logic you had) --}}
@section('title', isset($category) ? "Category: {$category->name}" : (isset($brand) ? "Brand: {$brand->name}" : (isset($vendor) ? "Vendor: {$vendor->name}" : 'Products')))

{{-- SEO helpers: self-canonical; noindex on internal search pages --}}
@section('canonical', url()->current())
@section('robots', request()->has('q') ? 'noindex,follow' : 'index,follow')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Heading --}}
    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="text-2xl font-bold">
            @if(isset($category))
                Products in Category: {{ $category->name }}
            @elseif(isset($brand))
                Products by Brand: {{ $brand->name }}
            @elseif(isset($vendor))
                Products by Vendor: {{ $vendor->name }}
            @else
                All Products
            @endif
        </h1>

        {{-- Quick result count --}}
        <div class="text-sm text-gray-500">
            {{ number_format($products->total()) }} result{{ $products->total() === 1 ? '' : 's' }}
        </div>
    </div>

    {{-- Filters: search + sort --}}
    <form method="GET" action="{{ route('products.index') }}" class="mb-6 bg-white p-4 rounded shadow">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label for="q" class="block text-sm text-gray-600 mb-1">Search</label>
                <input
                    id="q"
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search products..."
                    class="w-full border rounded px-3 py-2"
                >
            </div>

            <div>
                <label for="sort" class="block text-sm text-gray-600 mb-1">Sort by</label>
                @php $sort = request('sort'); @endphp
                <select id="sort" name="sort" class="w-full border rounded px-3 py-2">
                    <option value="">Relevance (default)</option>
                    <option value="name_asc"    {{ $sort === 'name_asc'    ? 'selected' : '' }}>Name (A–Z)</option>
                    <option value="name_desc"   {{ $sort === 'name_desc'   ? 'selected' : '' }}>Name (Z–A)</option>
                    <option value="price_asc"   {{ $sort === 'price_asc'   ? 'selected' : '' }}>Price (Low → High)</option>
                    <option value="price_desc"  {{ $sort === 'price_desc'  ? 'selected' : '' }}>Price (High → Low)</option>
                    <option value="latest"      {{ $sort === 'latest'      ? 'selected' : '' }}>Newest</option>
                    <option value="rating_desc" {{ $sort === 'rating_desc' ? 'selected' : '' }}>Rating (High → Low)</option>
                </select>
            </div>

            <div class="flex items-end">
                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded w-full md:w-auto">
                    Apply
                </button>
                @if(request()->hasAny(['q','sort']))
                    <a href="{{ route('products.index') }}" class="ml-3 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- Active filter chips (optional) --}}
    @if(request()->hasAny(['q']))
        <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
            @if(request('q'))
                <span class="px-2 py-1 rounded bg-gray-200">Search: “{{ request('q') }}”</span>
            @endif
        </div>
    @endif

    {{-- Products grid --}}
    @if($products->count())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                {{-- Card uses slug route: route('products.show', $product) --}}
                @include('products.partials._card', ['product' => $product])
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $products->withQueryString()->links() }}
        </div>
    @else
        <div class="bg-white p-6 rounded shadow text-center text-gray-600">
            No products found.
        </div>
    @endif
</div>
@endsection
