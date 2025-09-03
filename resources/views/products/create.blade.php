@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="max-w-3xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Add New Product</h2>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-4">
            <label for="name" class="block">Name</label>
            <input type="text" name="name" id="name" class="w-full border p-2" value="{{ old('name') }}" required>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="description" class="block">Description</label>
            <textarea name="description" id="description" class="w-full border p-2" rows="4" required>{{ old('description') }}</textarea>
            @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="price" class="block">Price ($)</label>
            <input type="number" name="price" step="0.01" id="price" class="w-full border p-2" value="{{ old('price') }}" required>
            @error('price')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

       <div class="mb-4">
    <label for="images" class="block">Product Images</label>
    <input type="file" name="images[]" id="images" class="w-full" multiple>
    @error('images')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror
    @if ($errors->has('images.*'))
        @foreach ($errors->get('images.*') as $messages)
            @foreach ($messages as $message)
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @endforeach
        @endforeach
    @endif
</div>
        <div class="mb-4">
            <label for="category" class="block">Category</label>
            <select name="category_id" id="category_id" class="w-full border p-2">
    @foreach($categories as $category)
        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
            {{ $category->name }}
        </option>
    @endforeach
</select>
            @error('category_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="is_active" class="inline-flex items-center">
                <input type="checkbox" name="is_active" id="is_active" class="mr-2" {{ old('is_active') ? 'checked' : '' }}>
                Active
            </label>
        </div>

        <div class="mb-4">
            <label for="stock" class="block">Stock</label>
            <input type="number" name="stock" id="stock" class="w-full border p-2" value="{{ old('stock') }}">
            @error('stock')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="brand" class="block">Brand</label>
            <input type="text" name="brand" id="brand" class="w-full border p-2" value="{{ old('brand') }}">
            @error('brand')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="discount_price" class="block">Discount Price ($)</label>
            <input type="number" step="0.01" name="discount_price" id="discount_price" class="w-full border p-2" value="{{ old('discount_price') }}">
            @error('discount_price')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="meta_title" class="block">Meta Title</label>
            <input type="text" name="meta_title" id="meta_title" class="w-full border p-2" value="{{ old('meta_title') }}">
            @error('meta_title')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="meta_description" class="block">Meta Description</label>
            <textarea name="meta_description" id="meta_description" class="w-full border p-2" rows="3">{{ old('meta_description') }}</textarea>
            @error('meta_description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="text-right">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Product</button>
        </div>
    </form>
</div>
@endsection
