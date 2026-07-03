<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'FusionERP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased bg-gray-50">

    <div class="min-h-screen flex">
        {{-- Left panel — branding --}}
        <div class="hidden lg:flex lg:w-1/2 bg-gray-900 flex-col items-center justify-center p-12">
            <div class="text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-600 mx-auto mb-6">
                    <svg class="h-9 w-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-white mb-3">FusionERP</h1>
                <p class="text-gray-400 text-lg max-w-sm">
                    The unified enterprise platform to manage products, inventory, orders, and teams.
                </p>
                <div class="mt-10 grid grid-cols-2 gap-4 text-left">
                    @foreach(['Products & Inventory', 'Order Processing', 'Role-based Access', 'Real-time Reports'] as $feat)
                    <div class="flex items-center gap-2 text-gray-300 text-sm">
                        <svg class="h-4 w-4 text-indigo-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $feat }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right panel — form --}}
        <div class="flex flex-1 flex-col items-center justify-center px-6 py-12">
            <div class="w-full max-w-sm">
                <div class="mb-8 text-center lg:hidden">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 mx-auto mb-3">
                        <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h1 class="text-xl font-bold text-gray-900">FusionERP</h1>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>
