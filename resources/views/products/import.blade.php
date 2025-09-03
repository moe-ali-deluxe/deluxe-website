@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold mb-4">Import Products</h1>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

       <form action="{{ route('products.import.images') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="excel_file" required>
    <button type="submit">Import Products with Images</button>
</form>
    </div>
@endsection
