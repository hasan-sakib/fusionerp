<x-app-layout>
    @section('title', 'Dashboard')
    @section('page-title', 'Dashboard')

    {{-- ── Subdomain welcome flash ──────────────────────────────────────── --}}
    @if (session('subdomain_url'))
    <div class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50 p-4">
        <div class="flex items-start gap-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100">
                <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-indigo-900">Your company ERP is ready!</p>
                <p class="mt-0.5 text-sm text-indigo-700">
                    Access it at:
                    <a href="{{ session('subdomain_url') }}"
                       class="font-mono font-medium underline hover:text-indigo-900">
                        {{ session('subdomain_url') }}
                    </a>
                </p>
                <p class="mt-1 text-xs text-indigo-600">
                    Add <code class="rounded bg-indigo-100 px-1 font-mono">127.0.0.1&nbsp;{{ parse_url(session('subdomain_url'), PHP_URL_HOST) }}</code>
                    to your <code class="rounded bg-indigo-100 px-1 font-mono">/etc/hosts</code> file to use the subdomain locally.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- ── KPI Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        <x-stat-card
            label="Total Products"
            :value="number_format($stats['total_products'])"
            color="indigo"
        >
            <x-slot name="icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            label="Total Users"
            :value="number_format($stats['total_users'])"
            color="purple"
        >
            <x-slot name="icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            label="Total Orders"
            :value="number_format($stats['total_orders'])"
            color="yellow"
        >
            <x-slot name="icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                </svg>
            </x-slot>
        </x-stat-card>

        <x-stat-card
            label="Total Revenue"
            :value="'$' . number_format($stats['total_revenue'], 2)"
            color="green"
        >
            <x-slot name="icon">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot>
        </x-stat-card>
    </div>

    {{-- ── Charts ──────────────────────────────────────────────────────────── --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Monthly Revenue Chart --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-700">Monthly Revenue (Last 6 Months)</h3>
            <canvas id="revenueChart" height="200"></canvas>
        </div>

        {{-- Orders by Status --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-gray-700">Orders by Status</h3>
            <canvas id="ordersChart" height="200"></canvas>
        </div>

        {{-- Top Selling Products --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm lg:col-span-2">
            <h3 class="mb-4 text-sm font-semibold text-gray-700">Top Selling Products</h3>
            <canvas id="topProductsChart" height="100"></canvas>
        </div>
    </div>

    @push('scripts')
    <script defer>
        document.addEventListener('DOMContentLoaded', function () {
        const Chart = window.Chart;
        const monthlyData = @json($charts['monthly_revenue']);
        const ordersData  = @json($charts['orders_by_status']);
        const topProducts = @json($charts['top_products']);

        // ── Revenue line chart ─────────────────────────────────────────────
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: {
                labels: Object.keys(monthlyData),
                datasets: [{
                    label: 'Revenue ($)',
                    data: Object.values(monthlyData),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.1)',
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } } }
        });

        // ── Orders doughnut ────────────────────────────────────────────────
        new Chart(document.getElementById('ordersChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(ordersData),
                datasets: [{
                    data: Object.values(ordersData),
                    backgroundColor: ['#6366f1','#22c55e','#f59e0b','#ef4444','#8b5cf6'],
                }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // ── Top products bar ───────────────────────────────────────────────
        new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: topProducts.map(p => p.name),
                datasets: [{
                    label: 'Units Sold',
                    data: topProducts.map(p => p.total_sold),
                    backgroundColor: '#6366f1',
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
        }); // DOMContentLoaded
    </script>
    @endpush
</x-app-layout>
