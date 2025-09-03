@extends('layouts.app')

@section('title', 'Brands')

@section('content')
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Brands</h1>
        <a href="{{ route('admin.brands.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Add Brand</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2 border border-gray-300">Logo</th>
                <th class="p-2 border border-gray-300">Name</th>
                <th class="p-2 border border-gray-300">Website</th>
                <th class="p-2 border border-gray-300">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($brands as $brand)
                <tr>
                    <td class="p-2 border border-gray-300">
                        @if($brand->logo)
                            <img src="{{ asset('storage/' . $brand->logo) }}" alt="{{ $brand->name }}" class="h-10">
                        @else
                            <span class="text-gray-500">No logo</span>
                        @endif
                    </td>
                    <td class="p-2 border border-gray-300">{{ $brand->name }}</td>
                    <td class="p-2 border border-gray-300">
                        @if($brand->website)
                            <a href="{{ $brand->website }}" target="_blank" class="text-blue-600 underline">{{ $brand->website }}</a>
                        @else
                            <span class="text-gray-500">N/A</span>
                        @endif
                    </td>
                    <td class="p-2 border border-gray-300">
                        <a href="{{ route('admin.brands.edit', $brand) }}" class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition">Edit</a>
                        <form action="{{ route('admin.brands.destroy', $brand) }}" method="POST" class="inline" onsubmit="return confirm('Delete this brand?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 transition">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-600">No brands found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $brands->links() }}
    </div>
</div>
@endsection
