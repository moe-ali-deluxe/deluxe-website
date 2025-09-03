<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'Admin Panel')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">

    <header class="bg-blue-700 text-white p-4">
        <h1 class="text-xl font-semibold">Admin Dashboard</h1>
        {{-- Add navigation or links here --}}
    </header>

    <main class="p-6">
        @yield('content')
    </main>

</body>
</html>
