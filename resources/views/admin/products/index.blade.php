@extends('layouts.app')

@section('title', 'Manage Products')

@section('content')
<div class="max-w-7xl mx-auto p-4">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">Manage Products</h1>
        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
            + Add Product
        </a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-4 bg-white p-3 rounded shadow">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or SKU..."
                       class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-gray-800 text-white rounded">Search</button>
                @if(request()->filled('q'))
                    <a href="{{ route('admin.products.index') }}" class="px-4 py-2 bg-gray-200 rounded">Reset</a>
                @endif
            </div>
        </div>
    </form>

    @php
        /**
         * Render a flat <option> list for a category tree.
         * $nodes      : Collection<Category> (each may have ->children)
         * $selectedId : currently selected category id
         * $prefix     : string indent (e.g. '— ')
         */
        if (!function_exists('renderCategoryOptionsFlat')) {
            function renderCategoryOptionsFlat($nodes, $selectedId, $prefix = '') {
                foreach ($nodes as $node) {
                    echo '<option value="'.e($node->id).'"'
                       . ($selectedId == $node->id ? ' selected' : '')
                       . '>'
                       . e($prefix.$node->name)
                       . '</option>';

                    if (!empty($node->children) && $node->children->count()) {
                        renderCategoryOptionsFlat($node->children, $selectedId, $prefix.'— ');
                    }
                }
            }
        }
    @endphp

    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-50 text-xs uppercase">
                <tr class="text-center">
                    <th class="border px-2 py-2 w-12">#</th>
                    <th class="border px-2 py-2 w-20">Image</th>
                    <th class="border px-2 py-2 text-left w-[22rem]">Name</th>
                    <th class="border px-2 py-2 w-52">Category</th>
                    <th class="border px-2 py-2 w-48">Brand</th>
                    <th class="border px-2 py-2 w-28">Price</th>
                    <th class="border px-2 py-2 w-32">Discount Price</th>
                    <th class="border px-2 py-2 w-24">Stock</th>
                    <th class="border px-2 py-2 w-20">Active</th>
                    <th class="border px-2 py-2 w-24">Save</th>
                    <th class="border px-2 py-2 w-40">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach($products as $product)
                    @php $formId = "quick-{$product->id}"; @endphp
                    <tr class="border-b hover:bg-gray-50 align-top">

                        {{-- # --}}
                        <td class="border px-2 py-2 text-center">
                            {{ $loop->iteration + ($products->currentPage()-1)*$products->perPage() }}
                        </td>

                        {{-- Image --}}
                        <td class="border px-2 py-2 text-center">
                            @if($product->images->first())
                                <img src="{{ asset('storage/' . $product->images->first()->image) }}"
                                     alt="Product image"
                                     class="w-12 h-12 object-cover rounded mx-auto">
                            @else
                                <span class="text-gray-400 text-xs">No Image</span>
                            @endif
                        </td>

                        {{-- Name (wider) --}}
                        <td class="border px-2 py-2">
                            <div class="font-medium text-gray-800 truncate max-w-[20rem]" title="{{ $product->name }}">
                                {{ $product->name }}
                            </div>
                            <div class="text-xs text-gray-500">SKU: {{ $product->sku ?? '—' }}</div>
                        </td>

                        {{-- Hidden quick-update form (one per row) --}}
                        <td class="hidden">
                            <form id="{{ $formId }}" action="{{ route('admin.products.quickUpdate', $product) }}" method="POST">
                                @csrf
                                @method('PUT')
                            </form>
                        </td>

                        {{-- Category (belongs to quick form via form="...") --}}
                        <td class="border px-2 py-2">
                            <select name="category_id" form="{{ $formId }}" class="border rounded px-2 py-1 w-full">
                                <option value="">-- None --</option>
                                @php
                                    // $categories is expected to be roots with eager-loaded children & grandchildren:
                                    // Category::with('children.children')->whereNull('parent_id')->orderBy('name')->get();
                                    renderCategoryOptionsFlat($categories, $product->category_id, '');
                                @endphp
                            </select>
                        </td>

                        {{-- Brand --}}
                        <td class="border px-2 py-2">
                            <select name="brand_id" form="{{ $formId }}" class="border rounded px-2 py-1 w-48">
                                <option value="">-- None --</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected($product->brand_id == $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </td>

                        {{-- Price --}}
                        <td class="border px-2 py-2 text-center">
                            <input type="number" name="price" form="{{ $formId }}" value="{{ $product->price }}"
                                   step="0.01" class="border rounded px-2 py-1 w-24 text-center">
                        </td>

                        {{-- Discount Price --}}
                        <td class="border px-2 py-2 text-center">
                            <input type="number" name="discount_price" form="{{ $formId }}"
                                   value="{{ $product->discount_price }}" step="0.01"
                                   class="border rounded px-2 py-1 w-28 text-center">
                        </td>

                        {{-- Stock --}}
                        <td class="border px-2 py-2 text-center">
                            <input type="number" name="stock" form="{{ $formId }}" value="{{ $product->stock }}"
                                   class="border rounded px-2 py-1 w-20 text-center">
                        </td>

                        {{-- Active --}}
                        <td class="border px-2 py-2 text-center">
                            <input type="hidden" name="is_active" form="{{ $formId }}" value="0">
                            <input type="checkbox" name="is_active" form="{{ $formId }}" value="1"
                                   @checked($product->is_active) class="h-4 w-4 align-middle">
                        </td>

                        {{-- Save --}}
                        <td class="border px-2 py-2 text-center">
                            <button type="submit" form="{{ $formId }}"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded">
                                Save
                            </button>
                        </td>

                        {{-- Actions --}}
                        <td class="border px-2 py-2">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1.5 rounded">
                                    Edit
                                </a>

                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                      onsubmit="return confirm('Delete this product?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->withQueryString()->links() }}
    </div>

</div>
@endsection
