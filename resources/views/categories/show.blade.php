@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-4">{{ $category->name }}</h1>

        @if($products->count())
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div class="border p-4 rounded shadow">
                        <img src="{{ $product->images->first()->url ?? '/default.jpg' }}" alt="{{ $product->name }}" class="w-full h-40 object-cover mb-2">
                        <h2 class="font-semibold">{{ $product->name }}</h2>
                        <p>${{ $product->price }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p>No products found in this category.</p>
        @endif
    </div>
@endsection
