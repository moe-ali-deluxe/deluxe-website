@php
    if (!auth()->check() || !auth()->user()->is_admin) {
        abort(403, 'Unauthorized access.');
    }
@endphp

@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Add New Product</h2>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label for="name" class="block">Name</label>
            <input type="text" name="name" id="name" class="w-full border p-2" value="{{ old('name') }}" required>
        </div>

        <div class="mb-4">
            <label for="description" class="block">Description</label>
            <textarea name="description" id="description" class="w-full border p-2" rows="4" required>{{ old('description') }}</textarea>
        </div>

        <div class="mb-4">
            <label for="price" class="block">Price ($)</label>
            <input type="number" name="price" step="0.01" id="price" class="w-full border p-2" value="{{ old('price') }}" required>
        </div>

        <div class="mb-4">
            <label for="image" class="block">Product Image</label>
            <input type="file" name="image" id="image" class="w-full">
        </div>

        <div class="mb-4">
            <label for="category" class="block">Category</label>
            <input type="text" name="category" id="category" class="w-full border p-2" value="{{ old('category') }}">
        </div>

        <div class="mb-4">
            <label for="is_active" class="inline-flex items-center">
                <input type="checkbox" name="is_active" id="is_active" class="mr-2" {{ old('is_active') ? 'checked' : '' }}>
                Active
            </label>
        </div>

        {{-- Your extra fields below --}}
        <div class="mb-4">
            <label for="stock" class="block">Stock</label>
            <input type="number" name="stock" id="stock" class="w-full border p-2" value="{{ old('stock') }}">
        </div>

        <div class="mb-4">
            <label for="brand" class="block">Brand</label>
            <input type="text" name="brand" id="brand" class="w-full border p-2" value="{{ old('brand') }}">
        </div>

        <div class="mb-4">
            <label for="discount_price" class="block">Discount Price ($)</label>
            <input type="number" step="0.01" name="discount_price" id="discount_price" class="w-full border p-2" value="{{ old('discount_price') }}">
        </div>

        <div class="mb-4">
            <label for="meta_title" class="block">Meta Title</label>
            <input type="text" name="meta_title" id="meta_title" class="w-full border p-2" value="{{ old('meta_title') }}">
        </div>

        <div class="mb-4">
            <label for="meta_description" class="block">Meta Description</label>
            <textarea name="meta_description" id="meta_description" class="w-full border p-2" rows="3">{{ old('meta_description') }}</textarea>
        </div>

        <div class="text-right">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Product</button>
        </div>
    </form>
</div>
@endsection
