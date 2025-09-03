@extends('layouts.app')

@section('title', 'Vendors List')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-white rounded shadow">
    <h1 class="text-3xl font-bold mb-6">Vendors</h1>
@if(session('success'))
    <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
        {{ session('success') }}
    </div>
@endif
    <a href="{{ route('admin.vendors.create') }}" 
       class="inline-block mb-4 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
        + Add New Vendor
    </a>

    @if($vendors->isEmpty())
        <p class="text-gray-600">No vendors found.</p>
    @else
        <table class="w-full border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-gray-300 px-4 py-2 text-left">Logo</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Name</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Email</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Phone</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Website</th>
                    <th class="border border-gray-300 px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendors as $vendor)
                    <tr>
                        <td class="border border-gray-300 px-4 py-2">
                            @if($vendor->logo)
                                <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->name }}" class="w-12 h-12 object-contain rounded">
                            @else
                                <span class="text-gray-400">No Logo</span>
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2">{{ $vendor->name }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $vendor->email ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2">{{ $vendor->phone ?? '-' }}</td>
                        <td class="border border-gray-300 px-4 py-2">
                            @if($vendor->website)
                                <a href="{{ $vendor->website }}" target="_blank" class="text-blue-600 hover:underline">
                                    {{ $vendor->website }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="border border-gray-300 px-4 py-2 whitespace-nowrap">
                            <a href="{{ route('admin.vendors.edit', $vendor->id) }}" 
                               class="text-indigo-600 hover:underline mr-4">
                                Edit
                            </a>

                            <form action="{{ route('admin.vendors.destroy', $vendor->id) }}" method="POST" class="inline"
                                onsubmit="return confirm('Are you sure you want to delete this vendor?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $vendors->links() }}
        </div>
    @endif
</div>
@endsection
