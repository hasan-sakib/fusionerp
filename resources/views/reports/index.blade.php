<x-app-layout>
    @section('page-title', 'Reports Overview')
    @section('header-actions')
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.sales') }}" class="btn-secondary btn-sm">Sales Report</a>
            <a href="{{ route('reports.inventory') }}" class="btn-secondary btn-sm">Inventory Report</a>
        </div>
    @endsection

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">

        {{-- Revenue this month --}}
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Revenue This Month</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($stats['revenue']['this_month'], 0) }}</p>
            @php $rChg = $stats['revenue']['change_pct']; @endphp
            <p class="mt-1 text-xs {{ $rChg >= 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ $rChg >= 0 ? '▲' : '▼' }} {{ abs($rChg) }}% vs last month
            </p>
        </div>

        {{-- Orders this month --}}
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Orders This Month</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['orders']['this_month']) }}</p>
            @php $oChg = $stats['orders']['change_pct']; @endphp
            <p class="mt-1 text-xs {{ $oChg >= 0 ? 'text-green-600' : 'text-red-500' }}">
                {{ $oChg >= 0 ? '▲' : '▼' }} {{ abs($oChg) }}% vs last month
            </p>
        </div>

        {{-- Products --}}
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Active Products</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['products']['total']) }}</p>
            <p class="mt-1 text-xs text-amber-600">
                {{ $stats['products']['low_stock'] + $stats['products']['out_of_stock'] }} need attention
            </p>
        </div>

        {{-- Stock value --}}
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Stock Value</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($stats['products']['stock_value'], 0) }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $stats['users']['total'] }} active users</p>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">

        {{-- Revenue trend (spans 2 cols) --}}
        <div class="lg:col-span-2 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Revenue Trend — Last 12 Months</h2>
            @if(collect($trend)->sum('revenue') > 0)
                <div style="height:220px">
                    <canvas id="trendChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-48 text-sm text-gray-400">No completed orders yet</div>
            @endif
        </div>

        {{-- Orders by status --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Orders by Status</h2>
            @if(array_sum($byStatus) > 0)
                <div style="height:220px" class="flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-48 text-sm text-gray-400">No orders yet</div>
            @endif
        </div>
    </div>

    {{-- Top products --}}
    <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Top Products by Revenue</h2>
            <a href="{{ route('reports.sales') }}" class="text-xs text-indigo-600 hover:underline">Full sales report →</a>
        </div>
        @if($topProducts->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-th">#</th>
                            <th class="table-th">Product</th>
                            <th class="table-th text-right">Units Sold</th>
                            <th class="table-th text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($topProducts as $i => $p)
                            <tr class="hover:bg-gray-50">
                                <td class="table-td text-gray-400 font-mono text-sm">{{ $i + 1 }}</td>
                                <td class="table-td font-medium text-gray-900">{{ $p->product_name }}</td>
                                <td class="table-td text-right text-gray-600">{{ number_format($p->units_sold) }}</td>
                                <td class="table-td text-right font-semibold text-gray-900">${{ number_format($p->revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-10 text-center text-sm text-gray-400">No completed orders yet</div>
        @endif
    </div>

    {{-- Order stats summary --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm text-center">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Orders</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ number_format($stats['orders']['total']) }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm text-center">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Completed</p>
            <p class="mt-1 text-3xl font-bold text-green-600">{{ number_format($stats['orders']['completed']) }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm text-center">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Lifetime Revenue</p>
            <p class="mt-1 text-3xl font-bold text-indigo-600">${{ number_format($stats['revenue']['total'], 0) }}</p>
        </div>
    </div>

    @push('scripts')
    <script defer>
    document.addEventListener('DOMContentLoaded', function () {
        const Chart = window.Chart;
        if (!Chart) return;

        const PALETTE = ['#6366f1','#22c55e','#f59e0b','#ef4444','#8b5cf6','#3b82f6','#ec4899','#14b8a6'];
        const STATUS_COLORS = {
            pending:    '#f59e0b',
            confirmed:  '#3b82f6',
            processing: '#8b5cf6',
            completed:  '#22c55e',
            cancelled:  '#ef4444',
        };

        // Revenue trend
        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas) {
            const trend = @json($trend);
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: trend.map(t => t.label),
                    datasets: [{
                        label: 'Revenue',
                        data: trend.map(t => t.revenue),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.08)',
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        fill: true,
                        tension: 0.4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                font: { size: 11 },
                                callback: v => '$' + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : v),
                            },
                        },
                    },
                },
            });
        }

        // Orders by status doughnut
        const statusCanvas = document.getElementById('statusChart');
        if (statusCanvas) {
            const byStatus = @json($byStatus);
            const labels = Object.keys(byStatus).map(s => s.charAt(0).toUpperCase() + s.slice(1));
            const values = Object.values(byStatus);
            const colors = Object.keys(byStatus).map(s => STATUS_COLORS[s] ?? '#94a3b8');
            new Chart(statusCanvas, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff' }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 12 } },
                    },
                    cutout: '65%',
                },
            });
        }
    });
    </script>
    @endpush
</x-app-layout>
