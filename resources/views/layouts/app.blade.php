<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>@yield('title', 'Deluxe Plus')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO: defaults can be overridden per-view with @section(...) --}}
    <meta name="description" content="@yield('meta_description', 'Deluxe Plus — premium dental & medical supplies. Shop quality brands with great service and fast shipping.')">
    <meta name="robots" content="@yield('robots', 'index,follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Open Graph / Twitter --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="Deluxe Plus">
    <meta property="og:title" content="@yield('og_title', trim($__env->yieldContent('title', 'Deluxe Plus')))">
    <meta property="og:description" content="@yield('og_description', trim($__env->yieldContent('meta_description', 'Deluxe Plus — premium dental & medical supplies.')))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.jpg'))">
    <meta name="twitter:card" content="summary_large_image">

    {{-- JSON-LD blocks from pages (Product, Breadcrumbs, LocalBusiness, etc.) --}}
    @stack('structured-data')

    <style>
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <!-- Lightbox2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 text-gray-800">

    <!-- Navbar -->
    <nav x-data="{ open: false }" class="bg-white border-b shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('storage/logo.jpeg') }}" alt="Logo" style="width: 40px; height: 40px;" />
                    <span class="text-xl font-bold text-brand-blue">Deluxe Plus</span>
                </div>
                <div class="hidden md:flex space-x-6">
                    <a href="{{ route('home') }}" class="hover:text-green-600">Home</a>
                    <a href="{{ route('products.index') }}" class="hover:text-green-600">Products</a>
                    <a href="{{ route('contact.show') }}" class="hover:text-green-600">Contact</a>

                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">⚙️ Admin Panel</a>
                        @endif

                        <a href="{{ route('wishlist.index') }}" class="relative inline-block hover:text-pink-600">
                            ❤️ Wishlist
                            <span id="wishlist-count"
                                  class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-5 h-5 text-xs flex items-center justify-center {{ (auth()->check() && auth()->user()->wishlist->count()) ? '' : 'hidden' }}">
                                {{ auth()->check() ? (auth()->user()->wishlist->count() ?? 0) : 0 }}
                            </span>
                        </a>

                        <span class="text-gray-700">Hi, {{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-red-600 hover:underline">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600">Sign In</a>
                        <a href="{{ route('register') }}" class="text-gray-700 hover:text-blue-600">Sign Up</a>
                    @endauth

                    <a href="{{ route('orders.index') }}" class="text-gray-700 hover:text-blue-600">Orders</a>

                    @php
                        $cartCount = 0;
                        $cartId = session()->get('cart_id');
                        if(Auth::check()) {
                            $cartCount = \App\Models\Cart::where('user_id', Auth::id())
                                ->where('status', 'active')
                                ->first()?->items()->sum('quantity') ?? 0;
                        } elseif($cartId) {
                            $cartCount = \App\Models\Cart::find($cartId)?->items()->sum('quantity') ?? 0;
                        }
                    @endphp

                    <a href="{{ route('cart.index') }}" class="relative text-indigo-600 hover:underline">
                        🛒
                        <span id="cart-count"
                              class="absolute -top-2 -right-3 bg-red-500 text-white text-xs rounded-full px-2 py-0.5 {{ $cartCount ? '' : 'hidden' }}">
                            {{ $cartCount }}
                        </span>
                    </a>
                </div>

                <!-- Mobile menu toggle -->
                <div class="md:hidden">
                    <button @click="open = !open" class="text-gray-600 focus:outline-none">
                        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="open" class="md:hidden px-4 pt-2 pb-4 space-y-2 bg-white" @click.away="open = false">
            <a href="{{ route('home') }}" class="block hover:text-green-600">Home</a>
            <a href="{{ route('products.index') }}" class="block hover:text-green-600">Products</a>
            <a href="{{ route('contact.show') }}" class="block hover:text-green-600">Contact</a>

            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.products.create') }}" class="block text-blue-600 hover:underline">➕ Add Product</a>
                    <a href="{{ route('admin.dashboard') }}" class="block text-blue-600 hover:underline">⚙️ Admin Panel</a>
                @endif

                <a href="{{ route('wishlist.index') }}" class="block hover:text-pink-600">❤️ Wishlist</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block text-red-600 hover:underline">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-gray-700 hover:text-blue-600">Sign In</a>
                <a href="{{ route('register') }}" class="block text-gray-700 hover:text-blue-600">Sign Up</a>
            @endauth

            <a href="{{ route('cart.index') }}" class="relative block text-indigo-600 hover:underline">
                🛒 Cart
                <span id="cart-count-mobile"
                      class="absolute ml-2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5 {{ $cartCount ? '' : 'hidden' }}">
                    {{ $cartCount }}
                </span>
            </a>
        </div>
    </nav>

    @include('partials.categories-bar')

    <!-- Main Content -->
    <main class="flex-grow">
        <div class="max-w-7xl mx-auto px-4 py-8">
            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 md:grid-cols-4 gap-8">

            <div>
                <img src="{{ asset('storage/logo.jpeg') }}" alt="Deluxe Plus Logo" class="w-32 mb-4">
                <p class="text-gray-400 text-sm">
                    Deluxe Plus offers premium dental and medical supplies to enhance your practice. Quality and service you can trust.
                </p>
            </div>

            <div>
                <h3 class="text-white font-semibold mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                    <li><a href="{{ route('products.index') }}" class="hover:text-white">Shop</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
                    <li><a href="{{ route('contact.show') }}" class="hover:text-white">Contact</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white font-semibold mb-4">Customer Service</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/faq') }}" class="hover:text-white">FAQ</a></li>
                    <li><a href="{{ url('/returns') }}" class="hover:text-white">Returns</a></li>
                    <li><a href="{{ url('/shipping') }}" class="hover:text-white">Shipping</a></li>
                    <li><a href="{{ url('/support') }}" class="hover:text-white">Support</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-white font-semibold mb-4">Newsletter</h3>
                <p class="text-gray-400 text-sm mb-4">Subscribe to get the latest updates and offers.</p>
                @if(session('success'))
                    <div class="bg-green-100 text-green-800 p-2 rounded mb-3">
                        {{ session('success') }}
                    </div>
                @endif
                <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex">
                    @csrf
                    <input type="email" name="email" placeholder="Enter your email" required
                           class="w-full px-3 py-2 rounded-l bg-gray-800 text-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-r text-white font-semibold transition">
                        Subscribe
                    </button>
                </form>
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="border-t border-gray-800 mt-8 py-4 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} Deluxe Plus Medical & Dental Supplies. All rights reserved.
        </div>
    </footer>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.swiper').forEach(swiperEl => {
                new Swiper(swiperEl, {
                    slidesPerView: 1.3,
                    spaceBetween: 16,
                    loop: true,
                    grabCursor: true,
                    navigation: {
                        nextEl: swiperEl.querySelector('.swiper-button-next'),
                        prevEl: swiperEl.querySelector('.swiper-button-prev'),
                    },
                    breakpoints: {
                        640: { slidesPerView: 2.3 },
                        1024: { slidesPerView: 4 },
                    },
                });
            });
        });
    </script>

    <!-- Lightbox2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>

    {{-- Wishlist toggle (AJAX with graceful fallback) --}}
    <script>
    document.addEventListener('submit', async (e) => {
        const form = e.target.closest('.wishlist-fallback-form');
        if (!form) return;

        e.preventDefault();

        const tokenMeta = document.querySelector('meta[name="csrf-token"]');
        const token = tokenMeta ? tokenMeta.getAttribute('content') : null;
        const btn = form.querySelector('.wishlist-btn');

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token || '',
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) { form.submit(); return; }

            const data = await res.json();
            const nowIn = (typeof data.in_wishlist !== 'undefined') ? !!data.in_wishlist : (data.action === 'added');

            if (btn) {
                const isTextButton = btn.classList.contains('text-pink-600'); // product page vs card
                btn.dataset.in = nowIn ? '1' : '0';
                btn.setAttribute('aria-pressed', nowIn ? 'true' : 'false');
                btn.innerHTML = nowIn ? (isTextButton ? '❤️ In wishlist (click to remove)' : '❤️')
                                      : (isTextButton ? '🤍 Add to Wishlist' : '🤍');
                btn.title = nowIn ? 'Remove from wishlist' : 'Add to wishlist';
                btn.classList.toggle('is-active', nowIn);
            }

            const counter = document.getElementById('wishlist-count');
            if (counter && typeof data.count !== 'undefined') {
                if (data.count > 0) {
                    counter.textContent = data.count;
                    counter.classList.remove('hidden');
                } else {
                    counter.textContent = '0';
                    counter.classList.add('hidden');
                }
            }
        } catch (err) {
            console.error(err);
            form.submit(); // fallback
        }
    });
    </script>

    @stack('scripts')
</body>
</html>
