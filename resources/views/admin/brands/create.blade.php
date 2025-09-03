@extends('layouts.app')

@section('title', 'Add Brand')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Add Brand</h1>

    <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block">Name</label>
            <input type="text" name="name" class="w-full border rounded p-2" value="{{ old('name') }}" required>
            @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block">Logo</label>
            <input type="file" name="logo" class="w-full border rounded p-2">
            @error('logo') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block">Website</label>
            <input type="url" name="website" class="w-full border rounded p-2" value="{{ old('website') }}">
            @error('website') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block">Description</label>
            <textarea name="description" class="w-full border rounded p-2">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Brand</button>
    </form>
</div>
@endsection
