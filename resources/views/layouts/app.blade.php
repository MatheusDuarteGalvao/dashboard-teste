<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard')</title>

    <!-- Tailwind CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    @stack('head')
</head>
<body class="bg-gray-100 min-h-screen">

    <!-- TOP NAVBAR -->
    <nav class="bg-white shadow p-4">
        <div class="container mx-auto flex justify-between items-center">
            <a href="{{ url('/') }}" class="text-xl font-semibold text-gray-700">Dashboard</a>

            <div class="flex items-center gap-4">
                <a href="{{ route('dashboard.orders') }}" class="text-gray-600 hover:text-black">Pedidos</a>
                <a href="{{ route('dashboard.top_products') }}" class="text-gray-600 hover:text-black">Top Produtos</a>
            </div>
        </div>
    </nav>

    <!-- PAGE CONTENT -->
    <main class="container mx-auto p-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
