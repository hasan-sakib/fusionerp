<x-app-layout>
    @section('page-title', 'Inventory Report')
    @section('header-actions')
        <div class="flex items-center gap-2">
            @can('reports.export')
                <a href="{{ route('reports.export', 'inventory') }}" class="btn-secondary btn-sm">Export CSV</a>
            @endcan
            <a href="{{ route('inventory.index') }}" class="btn-secondary btn-sm">Manage Inventory</a>
            <a href="{{ route('reports.index') }}" class="btn-secondary btn-sm">&larr; Overview</a>
        </div>
    @endsection

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-6">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Products</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ number_format($stats['total_units']) }} total units</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Out of Stock</p>
            <p class="mt-2 text-2xl font-bold {{ $stats['out_of_stock'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ number_format($stats['out_of_stock']) }}
            </p>
            <p class="mt-1 text-xs text-gray-400">products at 0 units</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Low Stock</p>
            <p class="mt-2 text-2xl font-bold {{ $stats['low_stock'] > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                {{ number_format($stats['low_stock']) }}
            </p>
            <p class="mt-1 text-xs text-gray-400">below min level</p>
        </div>
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Total Stock Value</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($stats['stock_value'], 0) }}</p>
            <p class="mt-1 text-xs text-gray-400">at current prices</p>
        </div>
    </div>

    {{-- Stock by category chart --}}
    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Stock Value by Category</h2>
        @if($byCategory->isNotEmpty() && $byCategory->sum('stock_value') > 0)
            <div style="height:240px">
                <canvas id="categoryChart"></canvas>
            </div>
        @else
            <div class="flex items-center justify-center h-40 text-sm text-gray-400">No products with stock yet</div>
        @endif
    </div>

    {{-- Low stock alerts --}}
    <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">
                Stock Alerts
                @if($lowStock->isNotEmpty())
                    <span class="ml-2 inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                        {{ $lowStock->count() }}
                    </span>
                @endif
            </h2>
            <a href="{{ route('inventory.index') }}" class="text-xs text-indigo-600 hover:underline">Manage inventory →</a>
        </div>
        @if($lowStock->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-th">Product</th>
                            <th class="table-th hidden sm:table-cell">SKU</th>
                            <th class="table-th hidden md:table-cell">Category</th>
                            <th class="table-th text-right">Current Stock</th>
                            <th class="table-th text-right hidden sm:table-cell">Min Level</th>
                            <th class="table-th text-right">Value</th>
                            <th class="table-th">Status</th>
                            <th class="table-th"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($lowStock as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="table-td font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="table-td hidden sm:table-cell text-gray-500 font-mono text-xs">{{ $product->sku ?? '—' }}</td>
                                <td class="table-td hidden md:table-cell text-gray-500">{{ $product->category }}</td>
                                <td class="table-td text-right font-semibold {{ $product->stock_quantity <= 0 ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ number_format($product->stock_quantity) }}
                                </td>
                                <td class="table-td text-right text-gray-500 hidden sm:table-cell">
                                    {{ $product->min_stock_level > 0 ? number_format($product->min_stock_level) : '—' }}
                                </td>
                                <td class="table-td text-right text-gray-600">
                                    ${{ number_format($product->stock_quantity * $product->price, 2) }}
                                </td>
                                <td class="table-td">
                                    @if($product->stock_quantity <= 0)
                                        <span class="badge-red">Out of Stock</span>
                                    @else
                                        <span class="badge-yellow">Low Stock</span>
                                    @endif
                                </td>
                                <td class="table-td">
                                    <a href="{{ route('inventory.show', $product->id) }}"
                                       class="text-xs text-indigo-600 hover:underline">Adjust</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-10 text-center">
                <p class="text-sm font-medium text-green-600">All products are adequately stocked</p>
                <p class="mt-1 text-xs text-gray-400">No items are out of stock or below minimum levels</p>
            </div>
        @endif
    </div>

    {{-- Recent movements --}}
    <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Recent Movements <span class="text-gray-400 font-normal text-sm">(last 30 days)</span></h2>
            <a href="{{ route('inventory.movements') }}" class="text-xs text-indigo-600 hover:underline">Full movement log →</a>
        </div>
        @if($movements->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-th">Date</th>
                            <th class="table-th">Product</th>
                            <th class="table-th">Type</th>
                            <th class="table-th text-right">Qty</th>
                            <th class="table-th text-right hidden md:table-cell">Before → After</th>
                            <th class="table-th hidden lg:table-cell">By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($movements as $m)
                            <tr class="hover:bg-gray-50">
                                <td class="table-td text-gray-500 text-xs whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($m->created_at)->format('M j, H:i') }}
                                </td>
                                <td class="table-td">
                                    <p class="font-medium text-gray-900">{{ $m->product_name }}</p>
                                    @if($m->product_sku)
                                        <p class="text-xs text-gray-400 font-mono">{{ $m->product_sku }}</p>
                                    @endif
                                </td>
                                <td class="table-td">
                                    @php
                                        $typeClasses = [
                                            'in'         => 'badge-green',
                                            'out'        => 'badge-red',
                                            'adjustment' => 'badge-blue',
                                            'return'     => 'badge-purple',
                                        ];
                                    @endphp
                                    <span class="{{ $typeClasses[$m->type] ?? 'badge-gray' }}">{{ ucfirst($m->type) }}</span>
                                </td>
                                <td class="table-td text-right font-semibold
                                    {{ in_array($m->type, ['in', 'return']) ? 'text-green-600' : 'text-red-600' }}">
                                    {{ in_array($m->type, ['in', 'return']) ? '+' : '-' }}{{ number_format($m->quantity) }}
                                </td>
                                <td class="table-td text-right text-gray-500 text-sm hidden md:table-cell">
                                    {{ number_format($m->before_quantity) }} → {{ number_format($m->after_quantity) }}
                                </td>
                                <td class="table-td text-gray-500 text-sm hidden lg:table-cell">
                                    {{ $m->user_name ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-10 text-center text-sm text-gray-400">No inventory movements in the last 30 days</div>
        @endif
    </div>

    @push('scripts')
    <script defer>
    document.addEventListener('DOMContentLoaded', function () {
        const Chart = window.Chart;
        if (!Chart) return;

        const categoryCanvas = document.getElementById('categoryChart');
        if (!categoryCanvas) return;

        const cats = @json($byCategory);
        const PALETTE = ['#6366f1','#22c55e','#f59e0b','#ef4444','#8b5cf6','#3b82f6','#ec4899','#14b8a6','#f97316','#06b6d4'];

        new Chart(categoryCanvas, {
            type: 'bar',
            data: {
                labels: cats.map(c => c.category),
                datasets: [
                    {
                        label: 'Stock Value ($)',
                        data: cats.map(c => parseFloat(c.stock_value) || 0),
                        backgroundColor: cats.map((_, i) => PALETTE[i % PALETTE.length] + 'cc'),
                        borderColor: cats.map((_, i) => PALETTE[i % PALETTE.length]),
                        borderWidth: 1,
                        borderRadius: 4,
                        yAxisID: 'y',
                        order: 2,
                    },
                    {
                        label: 'Units',
                        data: cats.map(c => parseInt(c.total_stock) || 0),
                        type: 'line',
                        borderColor: '#94a3b8',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        pointRadius: 3,
                        borderDash: [4, 3],
                        yAxisID: 'y2',
                        order: 1,
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
                        ticks: { font: { size: 11 } },
                    },
                },
            },
        });
    });
    </script>
    @endpush
</x-app-layout>
