<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name')) — FusionERP</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet"/>

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="h-full font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        {{-- ── Sidebar ──────────────────────────────────────────────────────── --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-gray-900 transform transition-transform duration-300 ease-in-out lg:static lg:inset-0 lg:translate-x-0"
        >
            {{-- Logo --}}
            <div class="flex h-16 shrink-0 items-center justify-between px-5 border-b border-gray-700/50">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="text-lg font-bold text-white tracking-tight">FusionERP</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white p-1">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

                <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home">
                    Dashboard
                </x-sidebar-link>

                @canany(['products.view', 'categories.view'])
                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Catalog</p>
                    @can('products.view')
                    <x-sidebar-link :href="route('products.index')" :active="request()->routeIs('products.*')" icon="cube">
                        Products
                    </x-sidebar-link>
                    @endcan
                    @can('categories.view')
                    <x-sidebar-link :href="route('categories.index')" :active="request()->routeIs('categories.*')" icon="tag">
                        Categories
                    </x-sidebar-link>
                    @endcan
                </div>
                @endcanany

                @can('inventory.view')
                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Warehouse</p>
                    <x-sidebar-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')" icon="archive-box">
                        Inventory
                    </x-sidebar-link>
                </div>
                @endcan

                @can('orders.view')
                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Sales</p>
                    <x-sidebar-link :href="route('orders.index')" :active="request()->routeIs('orders.*')" icon="shopping-cart">
                        Orders
                    </x-sidebar-link>
                </div>
                @endcan

                @can('reports.view')
                <x-sidebar-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" icon="chart-bar">
                    Reports
                </x-sidebar-link>
                @endcan

                @canany(['users.view', 'roles.view', 'settings.view'])
                <div class="pt-3">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Administration</p>
                    @can('users.view')
                    <x-sidebar-link :href="route('users.index')" :active="request()->routeIs('users.*')" icon="users">
                        Users
                    </x-sidebar-link>
                    @endcan
                    @can('roles.view')
                    <x-sidebar-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" icon="shield-check">
                        Roles
                    </x-sidebar-link>
                    @endcan
                    @can('settings.view')
                    <x-sidebar-link :href="route('settings.index')" :active="request()->routeIs('settings.*')" icon="cog-6-tooth">
                        Settings
                    </x-sidebar-link>
                    @endcan
                </div>
                @endcanany

            </nav>

            {{-- User section --}}
            <div class="shrink-0 border-t border-gray-700/50 p-3">
                <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                    <img src="{{ auth()->user()->avatar_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="h-8 w-8 rounded-full object-cover ring-2 ring-indigo-500 shrink-0">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-gray-400 capitalize">{{ auth()->user()->getRoleNames()->first() ?? 'user' }}</p>
                    </div>
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="text-gray-400 hover:text-white transition-colors">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h.01M12 12h.01M19 12h.01"/>
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                Profile
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Log Out
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </aside>

        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            @click="sidebarOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-gray-900/75 lg:hidden"
            style="display: none;"
        ></div>

        {{-- ── Main area ────────────────────────────────────────────────────── --}}
        <div class="flex flex-1 flex-col overflow-hidden">

            {{-- Top bar --}}
            <header class="flex h-16 shrink-0 items-center gap-4 border-b border-gray-200 bg-white px-6 shadow-sm">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="flex-1">
                    <h1 class="text-lg font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="flex items-center gap-3">
                    @yield('header-actions')
                </div>
            </header>

            {{-- Flash messages --}}
            @if(session('success') || session('error') || session('warning') || session('info'))
            <div class="px-6 pt-4 space-y-2">
                @if(session('success'))
                    <x-alert type="success" :message="session('success')" />
                @endif
                @if(session('error'))
                    <x-alert type="error" :message="session('error')" />
                @endif
                @if(session('warning'))
                    <x-alert type="warning" :message="session('warning')" />
                @endif
                @if(session('info'))
                    <x-alert type="info" :message="session('info')" />
                @endif
            </div>
            @endif

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
