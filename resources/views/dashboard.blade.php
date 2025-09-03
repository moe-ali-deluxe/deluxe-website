@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-blue-600 mb-4">Welcome, {{ Auth::user()->name }}!</h1>
    <p class="text-gray-700">This is your dashboard. You can start by browsing our <a href="/products" class="text-blue-600 underline">products</a>.</p>
</div>
@endsection