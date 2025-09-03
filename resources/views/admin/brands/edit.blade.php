@extends('layouts.app')

@section('title', 'Edit Brand')

@section('content')
<div class="container mx-auto p-4 max-w-xl">
    <h1 class="text-2xl font-bold mb-6">Edit Brand</h1>

    <form action="{{ route('admin.brands.update', $brand) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block mb-1 font-semibold">Name</label>
            <input type="text" name="name" class="w-full border rounded p-2" value="{{ old('name', $brand->name) }}" required>
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block mb-1 font-semibold">Logo</label>
            @if($brand->logo)
                <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="h-16 mb-2 rounded border">
            @endif
            <input type="file" name="logo" accept="image/*" class="w-full border rounded p-2">
            @error('logo') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block mb-1 font-semibold">Website</label>
            <input type="url" name="website" class="w-full border rounded p-2" value="{{ old('website', $brand->website) }}">
            @error('website') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block mb-1 font-semibold">Description</label>
            <textarea name="description" rows="4" class="w-full border rounded p-2">{{ old('description', $brand->description) }}</textarea>
            @error('description') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex space-x-4">
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition">Update Brand</button>
            <a href="{{ route('admin.brands.index') }}" class="px-5 py-2 border rounded hover:bg-gray-100 transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
