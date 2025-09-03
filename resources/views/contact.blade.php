@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-3xl font-bold mb-6 text-center">Contact Us</h1>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('contact.submit') }}" class="space-y-6 bg-white p-6 rounded shadow">
        @csrf

        <div>
            <label for="name" class="block font-semibold mb-1">Name</label>
            <input type="text" name="name" id="name"
                   value="{{ old('name') }}"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-indigo-500"
                   required>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block font-semibold mb-1">Email</label>
            <input type="email" name="email" id="email"
                   value="{{ old('email') }}"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-indigo-500"
                   required>
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="subject" class="block font-semibold mb-1">Subject</label>
            <input type="text" name="subject" id="subject"
                   value="{{ old('subject') }}"
                   class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-indigo-500"
                   required>
            @error('subject')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="message" class="block font-semibold mb-1">Message</label>
            <textarea name="message" id="message" rows="5"
                      class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-indigo-500"
                      required>{{ old('message') }}</textarea>
            @error('message')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="bg-indigo-600 text-white font-semibold px-6 py-2 rounded hover:bg-indigo-700 transition">
            Send Message
        </button>
    </form>
</div>
@endsection
