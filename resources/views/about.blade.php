@extends('layouts.app')

@section('title', 'About Us')

@section('content')
<div class="max-w-6xl mx-auto p-6 mt-6">

    {{-- Hero Section --}}
    <div class="bg-blue-50 rounded-lg p-8 text-center mb-12">
        <h1 class="text-4xl font-bold mb-4 text-blue-800">About Deluxe Plus</h1>
        <p class="text-lg text-gray-700 max-w-2xl mx-auto">
            Deluxe Plus Medical & Dental Supplies is committed to providing high-quality medical and dental products with exceptional customer service. 
            We strive to make healthcare and dental care accessible, reliable, and professional for all our clients.
        </p>
    </div>

    {{-- Our Mission --}}
    <div class="mb-12 text-center">
        <h2 class="text-3xl font-semibold mb-4">Our Mission</h2>
        <p class="text-gray-700 max-w-3xl mx-auto">
            To provide medical and dental professionals with reliable, high-quality products and equipment, backed by a team that is dedicated to service, education, and innovation.
        </p>
    </div>

    {{-- Our Values --}}
    <div class="mb-12">
        <h2 class="text-3xl font-semibold text-center mb-8">Our Values</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div class="p-6 bg-white rounded shadow">
                <h3 class="text-xl font-bold mb-2">Quality</h3>
                <p class="text-gray-600">We ensure all products meet high-quality standards for safety and reliability.</p>
            </div>
            <div class="p-6 bg-white rounded shadow">
                <h3 class="text-xl font-bold mb-2">Integrity</h3>
                <p class="text-gray-600">We operate transparently and ethically in all our business practices.</p>
            </div>
            <div class="p-6 bg-white rounded shadow">
                <h3 class="text-xl font-bold mb-2">Customer Focus</h3>
                <p class="text-gray-600">Our customers are our priority, and we strive to exceed their expectations.</p>
            </div>
        </div>
    </div>

    {{-- Our Team (optional) --}}
    <div class="mb-12">
        <h2 class="text-3xl font-semibold text-center mb-8">Meet Our Team</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Example Team Member --}}
            <div class="bg-white p-6 rounded shadow text-center">
                <img src="{{ asset('storage/maledoctor.jpg') }}" alt="Team Member" class="w-32 h-32 mx-auto rounded-full mb-4 object-cover">
                <h3 class="text-xl font-bold mb-1">Dr. Hussein Taha</h3>
                <p class="text-gray-600">Chief Medical Officer</p>
            </div>
            <div class="bg-white p-6 rounded shadow text-center">
                <img src="{{ asset('storage/femaledoctortwo.jpg') }}" alt="Team Member" class="w-32 h-32 mx-auto rounded-full mb-4 object-cover">
                <h3 class="text-xl font-bold mb-1">Dr Rania Sati</h3>
                <p class="text-gray-600">Head of Dental Supplies</p>
            </div>
            <div class="bg-white p-6 rounded shadow text-center">
                <img src="{{ asset('storage/itmanager.png') }}" alt="Team Member" class="w-32 h-32 mx-auto rounded-full mb-4 object-cover">
                <h3 class="text-xl font-bold mb-1">Mohammad Ali Charanek</h3>
                <p class="text-gray-600">Operations Manager</p>
            </div>
        </div>
    </div>

    {{-- Call to Action / Contact --}}
    <div class="bg-blue-50 rounded-lg p-8 text-center">
        <h2 class="text-3xl font-semibold mb-4">Get in Touch</h2>
        <p class="text-gray-700 mb-4">Whether you have questions or need support, our team is here to help you.</p>
        <a href="{{ route('contact.show') }}" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700 font-semibold">Contact Us</a>
    </div>

</div>
@endsection
