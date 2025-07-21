<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Deluxe Plus')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex flex-col min-h-screen bg-gray-100 text-gray-800">

<!-- Navbar -->
<nav x-data="{ open: false }" class="bg-white border-b shadow sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('storage/logo.jpeg') }}" alt="Logo" style="width: 40px; height: 40px;">
                <span class="text-xl font-bold text-green-700">Deluxe Plus</span>
            </div>
            <div class="hidden md:flex space-x-6">
                <a href="/" class="hover:text-green-600">Home</a>
                <a href="/products" class="hover:text-green-600">Products</a>
                <a href="/contact" class="hover:text-green-600">Contact</a>
            </div>
            <div class="md:hidden">
                <button @click="open = !open" class="text-gray-600 focus:outline-none">
                    <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open" class="md:hidden px-4 pt-2 pb-4 space-y-2 bg-white">
        <a href="/" class="block hover:text-green-600">Home</a>
        <a href="/products" class="block hover:text-green-600">Products</a>
        <a href="/contact" class="block hover:text-green-600">Contact</a>
    </div>
</nav>

<!-- Main Content -->
<main class="flex-grow">
    <div class="max-w-7xl mx-auto px-4 py-8">
        @yield('content')
    </div>
</main>

<!-- Footer -->
<footer class="bg-white border-t mt-8">
    <div class="max-w-7xl mx-auto px-4 py-6 text-sm text-gray-600 flex justify-between">
        <span>&copy; {{ date('Y') }} Deluxe Plus</span>
        <div class="space-x-4">
            <a href="#" class="hover:text-green-600">Privacy</a>
            <a href="#" class="hover:text-green-600">Terms</a>
        </div>
    </div>
</footer>

</body>
</html>
