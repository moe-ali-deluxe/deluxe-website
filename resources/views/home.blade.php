@extends('layouts.app')

@section('title', 'Deluxe Plus | Medical & Dental Supplies')
@section('meta_description', 'Enhance your practice with premium dental & medical supplies from Deluxe Plus. Trusted brands, sharp pricing, and fast delivery.')
@section('canonical', route('home'))
@section('robots', 'index,follow')

@section('content')

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    
    <!-- Hero Section with Enhanced Design -->
    <section class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 py-16 lg:py-24">
            
            <!-- Hero Content -->
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12 mb-16">
                <div class="flex-1 text-center lg:text-left">
                    <!-- Logo Integration -->
                    <div class="flex items-center justify-center lg:justify-start gap-4 mb-6">
                        <img src="{{ asset('storage/logo.jpeg') }}" alt="Deluxe Plus Logo" 
                             class="w-16 h-16 rounded-2xl shadow-lg ring-2 ring-white">
                        <div class="text-left">
                            <h1 class="text-4xl lg:text-6xl font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-800 bg-clip-text text-transparent">
                                Deluxe Plus
                            </h1>
                            <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full"></div>
                        </div>
                    </div>
                    
                    <h2 class="text-xl lg:text-2xl text-gray-700 font-medium mb-6 leading-relaxed">
                        Enhance Your Practice with <span class="font-semibold text-indigo-600">Premium</span> 
                        Dental & Medical Supplies
                    </h2>
                    
                    <p class="text-gray-600 text-lg mb-8 max-w-xl mx-auto lg:mx-0">
                        Trusted by healthcare professionals worldwide. Get the quality supplies you need 
                        with competitive pricing and lightning-fast delivery.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('products.index') }}"
                           class="group inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-300">
                            <span>Shop Now</span>
                            <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        
                        <a href="#categories" 
                           class="inline-flex items-center justify-center px-8 py-4 border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-indigo-300 hover:text-indigo-600 transition-colors duration-300">
                            Browse Categories
                        </a>
                    </div>
                </div>
                
                <!-- Features Cards -->
                <div class="flex-1 max-w-md">
                    <div class="grid grid-cols-1 gap-6">
                        <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Premium Quality</h3>
                                    <p class="text-sm text-gray-600">FDA approved supplies</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Fast Delivery</h3>
                                    <p class="text-sm text-gray-600">2-day shipping available</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white/70 backdrop-blur-sm rounded-2xl p-6 shadow-lg border border-white/50">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800">Best Prices</h3>
                                    <p class="text-sm text-gray-600">Competitive wholesale rates</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Slideshow -->
            <div class="relative w-full overflow-hidden rounded-3xl shadow-2xl" 
                 style="aspect-ratio: 1533 / 361;" 
                 x-data="slideshow()" 
                 x-init="start()">
                
                <template x-for="(image, index) in images" :key="index">
                    <div x-show="current === index"
                         x-transition:enter="transition-opacity duration-1000"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         class="absolute inset-0">
                        <img :src="image" alt="Slide" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/10"></div>
                    </div>
                </template>

                <!-- Enhanced Navigation -->
                <button @click="prev"
                        class="absolute top-1/2 left-6 transform -translate-y-1/2 bg-white/90 backdrop-blur-sm text-gray-700 rounded-full w-14 h-14 flex items-center justify-center shadow-xl hover:bg-white hover:scale-105 transition-all duration-300 z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button @click="next"
                        class="absolute top-1/2 right-6 transform -translate-y-1/2 bg-white/90 backdrop-blur-sm text-gray-700 rounded-full w-14 h-14 flex items-center justify-center shadow-xl hover:bg-white hover:scale-105 transition-all duration-300 z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>

                <!-- Enhanced Dots -->
                <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-3">
                    <template x-for="(image, index) in images" :key="index">
                        <button @click="goTo(index)"
                                :class="{'bg-white scale-125': current === index, 'bg-white/50': current !== index}"
                                class="w-3 h-3 rounded-full transition-all duration-300 focus:outline-none"></button>
                    </template>
                </div>
            </div>
        </div>
        
        <!-- Background Decoration -->
        <div class="absolute top-0 right-0 -z-10 transform translate-x-1/2 -translate-y-1/2">
            <div class="w-96 h-96 bg-gradient-to-br from-blue-400/20 to-indigo-400/20 rounded-full blur-3xl"></div>
        </div>
    </section>

    <!-- New Products Section -->
    @if(isset($newProducts) && $newProducts->count())
    <section class="py-16 bg-white/50 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Latest Arrivals</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Discover our newest collection of professional-grade medical and dental supplies
                </p>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full mx-auto mt-4"></div>
            </div>

            <div class="swiper product-swiper relative">
                <div class="swiper-wrapper">
                    @foreach($newProducts as $product)
                        <div class="swiper-slide">
                            <a href="{{ route('products.show', $product) }}" class="block group">
                                <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden h-96 group-hover:-translate-y-1">
                                    <!-- New Badge -->
                                    <div class="relative">
                                        <span class="absolute top-4 left-4 bg-green-500 text-white text-xs font-semibold px-3 py-1 rounded-full z-10">NEW</span>
                                        <div class="h-48 bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4">
                                            @php
                                                $img = optional($product->images->sortBy([
                                                    ['is_primary', 'desc'],
                                                    ['sort_order', 'asc'],
                                                    ['id', 'asc'],
                                                ])->first());
                                                $imgUrl = $img?->image ? asset('storage/' . $img->image) : asset('images/placeholder.png');
                                            @endphp
                                            <img src="{{ $imgUrl }}" alt="{{ e($product->name) }}" 
                                                 class="max-w-full max-h-full object-contain group-hover:scale-105 transition-transform duration-300" />
                                        </div>
                                    </div>
                                    
                                    <div class="p-6 flex flex-col justify-between flex-1">
                                        <div>
                                            <h3 class="font-semibold text-gray-900 line-clamp-2 mb-2 group-hover:text-indigo-600 transition-colors">
                                                {{ $product->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                                {{ \Illuminate\Support\Str::limit(strip_tags($product->description), 100) }}
                                            </p>
                                            <div class="text-2xl font-bold text-indigo-600 mb-4">
                                                ${{ number_format($product->display_price, 2) }}
                                            </div>
                                        </div>
                                        
                                        <button class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-medium px-6 py-3 rounded-xl hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-300">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                <!-- Enhanced Navigation -->
                <div class="swiper-button-prev !w-12 !h-12 !bg-white !shadow-xl !rounded-full after:!text-lg after:!font-bold after:!text-gray-700 hover:!scale-105 !transition-all"></div>
                <div class="swiper-button-next !w-12 !h-12 !bg-white !shadow-xl !rounded-full after:!text-lg after:!font-bold after:!text-gray-700 hover:!scale-105 !transition-all"></div>
            </div>
        </div>
    </section>
    @endif

    <!-- Enhanced Shop By Category -->
    <section id="categories" class="py-16 bg-gradient-to-br from-indigo-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-between mb-12">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Shop By Category</h2>
                    <p class="text-gray-600 text-lg">Find exactly what you need for your practice</p>
                </div>
                <a href="{{ route('products.index') }}" 
                   class="hidden sm:inline-flex items-center text-indigo-600 hover:text-indigo-700 font-medium group">
                    View all products
                    <svg class="ml-2 w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>

            @php
                $cats = ($topCategories ?? collect());
            @endphp

            @if($cats->count())
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-6">
                    @foreach($cats as $cat)
                        <a href="{{ route('products.byCategory', ['slug' => $cat->slug]) }}"
                           class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden hover:-translate-y-1">
                            <div class="aspect-square w-full bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4">
                                @if(!empty($cat->image))
                                    <img src="{{ asset('storage/'.$cat->image) }}"
                                         alt="{{ $cat->name }}"
                                         class="max-h-full max-w-full object-contain group-hover:scale-110 transition-transform duration-300"
                                         loading="lazy">
                                @else
                                    <svg viewBox="0 0 24 24" class="h-12 w-12 text-gray-400 group-hover:text-indigo-500 transition-colors" aria-hidden="true">
                                        <path fill="currentColor"
                                              d="M12 2c2.8 0 5 2.2 5 5v2h1a3 3 0 0 1 0 6h-1v5a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-5H5a3 3 0 0 1 0-6h1V7c0-2.8 2.2-5 5-5z"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="font-semibold text-gray-900 line-clamp-2 mb-1 group-hover:text-indigo-600 transition-colors">
                                    {{ $cat->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ (int)($cat->total_products_count ?? $cat->products_count ?? 0) }} products
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-lg">Categories coming soon...</p>
                </div>
            @endif
        </div>
    </section>

    <!-- Enhanced Shop By Brand Section -->
    @if(isset($brands) && $brands->count())
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Trusted Brands</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    We partner with the industry's most respected manufacturers
                </p>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full mx-auto mt-4"></div>
            </div>

            <div class="swiper brand-swiper">
                <div class="swiper-wrapper">
                    @foreach($brands as $brand)
                        <div class="swiper-slide">
                            <a href="{{ route('products.byBrand', ['brandSlug' => $brand->slug]) }}" 
                               class="group block bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-8 text-center hover:-translate-y-1">
                                @if($brand->logo)
                                    <img src="{{ asset('storage/' . $brand->logo) }}" 
                                         alt="{{ $brand->name }}" 
                                         class="h-16 mx-auto object-contain mb-4 group-hover:scale-105 transition-transform" />
                                @else
                                    <div class="h-16 w-full bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 mb-4 group-hover:bg-gray-200 transition-colors">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                    {{ $brand->name }}
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-prev !w-12 !h-12 !bg-white !shadow-xl !rounded-full after:!text-lg after:!font-bold after:!text-gray-700 hover:!scale-105 !transition-all"></div>
                <div class="swiper-button-next !w-12 !h-12 !bg-white !shadow-xl !rounded-full after:!text-lg after:!font-bold after:!text-gray-700 hover:!scale-105 !transition-all"></div>
            </div>
        </div>
    </section>
    @endif

    <!-- Enhanced Shop By Vendor Section -->
    @if(isset($vendors) && $vendors->count())
    <section class="py-16 bg-gradient-to-br from-gray-50 to-blue-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">Featured Vendors</h2>
                <p class="text-gray-600 text-lg max-w-2xl mx-auto">
                    Quality suppliers committed to excellence in healthcare
                </p>
                <div class="w-24 h-1 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full mx-auto mt-4"></div>
            </div>

            <div class="swiper vendor-swiper mb-16">
                <div class="swiper-wrapper">
                    @foreach($vendors as $vendor)
                        <div class="swiper-slide">
                            <a href="{{ route('products.byVendor', $vendor->id) }}" 
                               class="group block bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-8 text-center hover:-translate-y-1">
                                @if($vendor->logo)
                                    <img src="{{ asset('storage/' . $vendor->logo) }}" 
                                         alt="{{ $vendor->name }}" 
                                         class="h-16 mx-auto object-contain mb-4 group-hover:scale-105 transition-transform" />
                                @else
                                    <div class="h-16 w-full bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 mb-4 group-hover:bg-gray-200 transition-colors">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                    </div>
                                @endif
                                <span class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                    {{ $vendor->name }}
                                </span>
                            </a>
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-prev !w-12 !h-12 !bg-white !shadow-xl !rounded-full after:!text-lg after:!font-bold after:!text-gray-700 hover:!scale-105 !transition-all"></div>
                <div class="swiper-button-next !w-12 !h-12 !bg-white !shadow-xl !rounded-full after:!text-lg after:!font-bold after:!text-gray-700 hover:!scale-105 !transition-all"></div>
            </div>
        </div>
    </section>
    @endif

    <!-- Trust & Support Section -->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-8">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Quality Guaranteed</h3>
                    <p class="text-gray-600">All products meet the highest industry standards with full warranty coverage.</p>
                </div>
                
                <div class="text-center p-8">
                    <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Expert Support</h3>
                    <p class="text-gray-600">Our knowledgeable team is here to help you find the right supplies for your needs.</p>
                </div>
                
                <div class="text-center p-8">
                    <div class="w-16 h-16 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Competitive Pricing</h3>
                    <p class="text-gray-600">Get the best value with our wholesale pricing and bulk discount options.</p>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Enhanced Alpine.js Slideshow Script -->
<script>
    function slideshow() {
        return {
            images: [
                '{{ asset('images/pg1.png') }}',
                '{{ asset('images/pg2.png') }}',
                '{{ asset('images/pg3.png') }}',
            ],
            current: 0,
            timer: null,

            start() {
                this.stop();
                this.timer = setInterval(() => this.next(), 6000);
            },
            stop() {
                if (this.timer) clearInterval(this.timer);
                this.timer = null;
            },
            next() {
                this.current = (this.current + 1) % this.images.length;
            },
            prev() {
                this.current = (this.current - 1 + this.images.length) % this.images.length;
            },
            goTo(index) {
                this.current = index;
                this.start(); // Restart timer when manually navigating
            }
        }
    }
</script>

<!-- Enhanced Menu Toggle Script -->
<script>
    // Enhanced click-away for categories menu with better performance
    document.addEventListener('DOMContentLoaded', function() {
        const menu = document.getElementById('categories-menu');
        const button = document.getElementById('categories-button');
        
        if (menu && button) {
            let isOpen = false;
            
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                isOpen = !isOpen;
                menu.classList.toggle('hidden', !isOpen);
            });
            
            document.addEventListener('click', function(e) {
                if (isOpen && !menu.contains(e.target) && !button.contains(e.target)) {
                    isOpen = false;
                    menu.classList.add('hidden');
                }
            });
        }
    });
</script>

<!-- Enhanced Swiper Initialization with Modern Configuration -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced Product Swiper
    const productSwipers = document.querySelectorAll('.product-swiper');
    productSwipers.forEach(swiperEl => {
        new Swiper(swiperEl, {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: swiperEl.querySelector('.swiper-button-next'),
                prevEl: swiperEl.querySelector('.swiper-button-prev'),
            },
            pagination: {
                el: swiperEl.querySelector('.swiper-pagination'),
                clickable: true,
            },
            breakpoints: {
                640: { 
                    slidesPerView: 2, 
                    spaceBetween: 20 
                },
                768: { 
                    slidesPerView: 3, 
                    spaceBetween: 24 
                },
                1024: { 
                    slidesPerView: 4, 
                    spaceBetween: 30 
                },
                1280: { 
                    slidesPerView: 4, 
                    spaceBetween: 32 
                },
            },
            on: {
                init: function() {
                    // Add loading animation
                    this.el.classList.add('swiper-initialized');
                }
            }
        });
    });

    // Enhanced Brand/Vendor Swipers
    const brandSwipers = document.querySelectorAll('.brand-swiper, .vendor-swiper');
    brandSwipers.forEach(swiperEl => {
        new Swiper(swiperEl, {
            slidesPerView: 2,
            spaceBetween: 16,
            loop: true,
            centeredSlides: false,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
            navigation: {
                nextEl: swiperEl.querySelector('.swiper-button-next'),
                prevEl: swiperEl.querySelector('.swiper-button-prev'),
            },
            breakpoints: {
                640: { 
                    slidesPerView: 3, 
                    spaceBetween: 20 
                },
                768: { 
                    slidesPerView: 4, 
                    spaceBetween: 24 
                },
                1024: { 
                    slidesPerView: 5, 
                    spaceBetween: 30 
                },
                1280: { 
                    slidesPerView: 6, 
                    spaceBetween: 32 
                },
            },
        });
    });

    // Generic swiper for any remaining swipers
    const genericSwipers = document.querySelectorAll('.swiper:not(.product-swiper):not(.brand-swiper):not(.vendor-swiper)');
    genericSwipers.forEach(swiperEl => {
        new Swiper(swiperEl, {
            slidesPerView: 1,
            spaceBetween: 20,
            loop: true,
            navigation: {
                nextEl: swiperEl.querySelector('.swiper-button-next'),
                prevEl: swiperEl.querySelector('.swiper-button-prev'),
            },
            breakpoints: {
                640: { 
                    slidesPerView: 2, 
                    spaceBetween: 20 
                },
                768: { 
                    slidesPerView: 3, 
                    spaceBetween: 24 
                },
                1024: { 
                    slidesPerView: 4, 
                    spaceBetween: 30 
                },
            },
        });
    });

    // Prevent event bubbling on navigation buttons
    document.querySelectorAll('.swiper-button-next, .swiper-button-prev').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
        });
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add scroll-based animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe sections for animation
    document.querySelectorAll('section').forEach(section => {
        observer.observe(section);
    });
});

// Add CSS for fade-in animation
document.head.insertAdjacentHTML('beforeend', `
    <style>
        .animate-fadeInUp {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .swiper-initialized {
            opacity: 1;
            transition: opacity 0.3s ease;
        }
        
        .swiper:not(.swiper-initialized) {
            opacity: 0;
        }
        
        /* Enhanced hover effects */
        .group:hover .group-hover\\:scale-105 {
            transform: scale(1.05);
        }
        
        .group:hover .group-hover\\:scale-110 {
            transform: scale(1.1);
        }
        
        /* Line clamp utilities for better text truncation */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        /* Custom scrollbar for better UX */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* Enhanced button styles */
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
    </style>
`);
</script>

@endsection