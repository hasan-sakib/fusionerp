<x-app-layout>
    @section('page-title', 'Inventory')
    @section('header-actions')
        <a href="{{ route('inventory.movements') }}" class="btn-secondary btn-sm">Movement Log</a>
    @endsection

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <p class="text-sm text-gray-500">Total Products</p>
            <p class="mt-1 text-3xl font-bold text-gray-900">{{ $stats['total_products'] }}</p>
        </div>
        <div class="rounded-xl border border-yellow-100 bg-yellow-50 p-5 shadow-sm">
            <p class="text-sm text-yellow-700">Low Stock</p>
            <p class="mt-1 text-3xl font-bold text-yellow-800">{{ $stats['low_stock_count'] }}</p>
        </div>
        <div class="rounded-xl border border-red-100 bg-red-50 p-5 shadow-sm">
            <p class="text-sm text-red-700">Out of Stock</p>
            <p class="mt-1 text-3xl font-bold text-red-800">{{ $stats['out_of_stock_count'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('inventory.index') }}" class="mb-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-48">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Product name or SKU…"
                       class="form-input">
            </div>

            <div class="flex items-end gap-4 pb-2">
                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
                    <input type="checkbox" name="low_stock" value="1"
                           {{ request('low_stock') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Low stock
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
                    <input type="checkbox" name="out_of_stock" value="1"
                           {{ request('out_of_stock') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Out of stock
                </label>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                <a href="{{ route('inventory.index') }}" class="btn-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-th">Product</th>
                        <th class="table-th hidden md:table-cell">SKU</th>
                        <th class="table-th hidden md:table-cell">Category</th>
                        <th class="table-th text-center">Stock</th>
                        <th class="table-th text-center">Min Level</th>
                        <th class="table-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        @php
                            $rowClass = $product->isOutOfStock()
                                ? 'bg-red-50/40'
                                : ($product->isLowStock() ? 'bg-yellow-50/40' : 'hover:bg-gray-50/50');
                            $stockClass = $product->isOutOfStock()
                                ? 'text-red-600 font-bold'
                                : ($product->isLowStock() ? 'text-yellow-600 font-semibold' : 'text-gray-700');
                        @endphp
                        <tr class="{{ $rowClass }} transition-colors">
                            <td class="table-td">
                                <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                @if($product->isOutOfStock())
                                    <span class="badge-red text-xs">Out of Stock</span>
                                @elseif($product->isLowStock())
                                    <span class="badge-yellow text-xs">Low Stock</span>
                                @endif
                            </td>
                            <td class="table-td hidden md:table-cell text-gray-500 font-mono text-sm">
                                {{ $product->sku ?? '—' }}
                            </td>
                            <td class="table-td hidden md:table-cell text-gray-500">
                                {{ $product->category?->name ?? '—' }}
                            </td>
                            <td class="table-td text-center {{ $stockClass }}">
                                {{ $product->stock_quantity }}
                            </td>
                            <td class="table-td text-center text-gray-500">
                                {{ $product->min_stock_level ?: '—' }}
                            </td>
                            <td class="table-td text-right">
                                <a href="{{ route('inventory.show', $product) }}" class="btn-secondary btn-sm">Manage</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="table-td py-12 text-center text-gray-400">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
