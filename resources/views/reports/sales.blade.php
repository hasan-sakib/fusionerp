<x-app-layout>
    @section('page-title', 'Sales Report')
    @section('header-actions')
        <div class="flex items-center gap-2">
            @can('reports.export')
                <a href="{{ route('reports.export', 'sales') }}?from={{ $from->format('Y-m-d') }}&to={{ $to->format('Y-m-d') }}"
                   class="btn-secondary btn-sm">Export CSV</a>
            @endcan
            <a href="{{ route('reports.index') }}" class="btn-secondary btn-sm">&larr; Overview</a>
        </div>
    @endsection

    {{-- Date Range Filter --}}
    <div class="mb-6 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="form-label">From</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="form-input" max="{{ now()->format('Y-m-d') }}">
            </div>
            <div>
                <label class="form-label">To</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="form-input" max="{{ now()->format('Y-m-d') }}">
            </div>
            <button type="submit" class="btn-primary">Apply</button>

            {{-- Presets --}}
            <div class="flex items-center gap-2 ml-2 flex-wrap">
                @php
                    $presets = [
                        'Today'   => [now()->format('Y-m-d'), now()->format('Y-m-d')],
                        '7 days'  => [now()->subDays(6)->format('Y-m-d'), now()->format('Y-m-d')],
                        '30 days' => [now()->subDays(29)->format('Y-m-d'), now()->format('Y-m-d')],
                        '90 days' => [now()->subDays(89)->format('Y-m-d'), now()->format('Y-m-d')],
                        'This year'=> [now()->startOfYear()->format('Y-m-d'), now()->format('Y-m-d')],
                    ];
                @endphp
                @foreach($presets as $label => [$pFrom, $pTo])
                    <a href="{{ route('reports.sales', ['from' => $pFrom, 'to' => $pTo]) }}"
                       class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50
                              {{ $from->format('Y-m-d') === $pFrom && $to->format('Y-m-d') === $pTo ? 'bg-indigo-50 border-indigo-300 text-indigo-700 font-medium' : '' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Revenue</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($summary['revenue'], 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ $from->format('M j') }} – {{ $to->format('M j, Y') }}</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Orders</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($summary['total_orders']) }}</p>
            <p class="mt-1 text-xs text-green-600">{{ $summary['completed'] }} completed</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Avg Order Value</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($summary['avg_order_value'], 2) }}</p>
            <p class="mt-1 text-xs text-gray-400">completed orders only</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Cancellation Rate</p>
            @php
                $cancelRate = $summary['total_orders'] > 0
                    ? round($summary['cancelled'] / $summary['total_orders'] * 100, 1)
                    : 0;
            @endphp
            <p class="mt-2 text-2xl font-bold {{ $cancelRate > 20 ? 'text-red-600' : 'text-gray-900' }}">{{ $cancelRate }}%</p>
            <p class="mt-1 text-xs text-gray-400">{{ $summary['cancelled'] }} cancelled</p>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">

        {{-- Revenue timeline --}}
        <div class="lg:col-span-2 rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Revenue Over Time</h2>
            @if(count($trend) > 0)
                <div style="height:240px">
                    <canvas id="trendChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-48 text-sm text-gray-400">No orders in this period</div>
            @endif
        </div>

        {{-- Status breakdown --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Orders by Status</h2>
            @if(array_sum($byStatus) > 0)
                <div style="height:240px" class="flex items-center justify-center">
                    <canvas id="statusChart"></canvas>
                </div>
            @else
                <div class="flex items-center justify-center h-48 text-sm text-gray-400">No orders yet</div>
            @endif
        </div>
    </div>

    {{-- Top products & customers --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">

        {{-- Top products --}}
        <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Top Products</h2>
            </div>
            @if($topProducts->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-th">Product</th>
                                <th class="table-th text-right">Units</th>
                                <th class="table-th text-right">Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($topProducts as $p)
                                <tr class="hover:bg-gray-50">
                                    <td class="table-td font-medium text-gray-900">{{ $p->product_name }}</td>
                                    <td class="table-td text-right text-gray-600">{{ number_format($p->units) }}</td>
                                    <td class="table-td text-right font-semibold text-gray-900">${{ number_format($p->revenue, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-8 text-center text-sm text-gray-400">No completed orders in this period</div>
            @endif
        </div>

        {{-- Top customers --}}
        <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Top Customers</h2>
            </div>
            @if($topCustomers->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-th">Customer</th>
                                <th class="table-th text-right">Orders</th>
                                <th class="table-th text-right">Spent</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($topCustomers as $c)
                                <tr class="hover:bg-gray-50">
                                    <td class="table-td">
                                        <p class="font-medium text-gray-900">{{ $c->customer_name }}</p>
                                        @if($c->customer_email)
                                            <p class="text-xs text-gray-400">{{ $c->customer_email }}</p>
                                        @endif
                                    </td>
                                    <td class="table-td text-right text-gray-600">{{ $c->order_count }}</td>
                                    <td class="table-td text-right font-semibold text-gray-900">${{ number_format($c->total_spent, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-8 text-center text-sm text-gray-400">No orders in this period</div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script defer>
    document.addEventListener('DOMContentLoaded', function () {
        const Chart = window.Chart;
        if (!Chart) return;

        const STATUS_COLORS = {
            pending:    '#f59e0b',
            confirmed:  '#3b82f6',
            processing: '#8b5cf6',
            completed:  '#22c55e',
            cancelled:  '#ef4444',
        };

        // Revenue timeline
        const trendCanvas = document.getElementById('trendChart');
        if (trendCanvas) {
            const trend = @json($trend);
            new Chart(trendCanvas, {
                type: 'bar',
                data: {
                    labels: trend.map(t => t.period),
                    datasets: [
                        {
                            label: 'Revenue ($)',
                            data: trend.map(t => t.revenue),
                            backgroundColor: 'rgba(99,102,241,0.75)',
                            borderRadius: 4,
                            order: 2,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Orders',
                            data: trend.map(t => t.orders),
                            type: 'line',
                            borderColor: '#22c55e',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            pointRadius: 3,
                            order: 1,
                            yAxisID: 'y2',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { font: { size: 11 }, padding: 12 } } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            grid: { color: 'rgba(0,0,0,0.04)' },
                            ticks: {
                                font: { size: 11 },
                                callback: v => '$' + (v >= 1000 ? (v/1000).toFixed(1) + 'k' : v),
                            },
                        },
                        y2: {
                            beginAtZero: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { font: { size: 11 }, stepSize: 1 },
                        },
                    },
                },
            });
        }

        // Status doughnut
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
                        legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } },
                    },
                    cutout: '65%',
                },
            });
        }
    });
    </script>
    @endpush
</x-app-layout>
