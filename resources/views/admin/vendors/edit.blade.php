@extends('layouts.app')

@section('title', 'Edit Vendor')

@section('content')
<div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold mb-6">Edit Vendor</h1>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.vendors.update', $vendor->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <label class="block mb-2 font-semibold" for="name">Name *</label>
        <input type="text" id="name" name="name" value="{{ old('name', $vendor->name) }}" required
            class="w-full mb-4 p-2 border rounded @error('name') border-red-500 @enderror">
        @error('name')
            <div class="text-red-600 mb-4">{{ $message }}</div>
        @enderror

        {{-- Current logo preview --}}
        @if($vendor->logo)
            <div class="mb-4">
                <p class="font-semibold mb-1">Current Logo:</p>
                <img src="{{ asset('storage/' . $vendor->logo) }}" alt="Logo of {{ $vendor->name }}" class="w-32 h-auto rounded border">
            </div>
        @endif

        {{-- Logo upload --}}
        <label class="block mb-2 font-semibold" for="logo">Change Logo</label>
        <input type="file" id="logo" name="logo" accept="image/*"
            class="w-full mb-4 p-2 border rounded @error('logo') border-red-500 @enderror">
        @error('logo')
            <div class="text-red-600 mb-4">{{ $message }}</div>
        @enderror

        <label class="block mb-2 font-semibold" for="email">Email</label>
        <input type="email" id="email" name="email" value="{{ old('email', $vendor->email) }}"
            class="w-full mb-4 p-2 border rounded @error('email') border-red-500 @enderror">
        @error('email')
            <div class="text-red-600 mb-4">{{ $message }}</div>
        @enderror

        <label class="block mb-2 font-semibold" for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $vendor->phone) }}"
            class="w-full mb-4 p-2 border rounded @error('phone') border-red-500 @enderror">
        @error('phone')
            <div class="text-red-600 mb-4">{{ $message }}</div>
        @enderror

        <label class="block mb-2 font-semibold" for="website">Website</label>
        <input type="url" id="website" name="website" value="{{ old('website', $vendor->website) }}"
            class="w-full mb-4 p-2 border rounded @error('website') border-red-500 @enderror">
        @error('website')
            <div class="text-red-600 mb-4">{{ $message }}</div>
        @enderror

        <label class="block mb-2 font-semibold" for="address">Address</label>
        <input type="text" id="address" name="address" value="{{ old('address', $vendor->address) }}"
            class="w-full mb-4 p-2 border rounded @error('address') border-red-500 @enderror">
        @error('address')
            <div class="text-red-600 mb-4">{{ $message }}</div>
        @enderror

        <label class="block mb-2 font-semibold" for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="3"
            class="w-full mb-4 p-2 border rounded @error('notes') border-red-500 @enderror">{{ old('notes', $vendor->notes) }}</textarea>
        @error('notes')
            <div class="text-red-600 mb-4">{{ $message }}</div>
        @enderror

        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Update Vendor
        </button>

        <a href="{{ route('admin.vendors.index') }}" class="inline-block mt-4 text-gray-600 hover:underline">
            Cancel
        </a>
    </form>
</div>
@endsection
