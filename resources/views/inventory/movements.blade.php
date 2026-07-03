<x-app-layout>
    @section('page-title', 'Stock Movement Log')
    @section('header-actions')
        <a href="{{ route('inventory.index') }}" class="btn-secondary btn-sm">&larr; Inventory</a>
    @endsection

    {{-- Filters --}}
    <form method="GET" action="{{ route('inventory.movements') }}" class="mb-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div class="w-56">
                <label class="form-label">Product</label>
                <select name="product_id" class="form-select">
                    <option value="">All products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}{{ $product->sku ? " ({$product->sku})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-36">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">All types</option>
                    <option value="in"         {{ request('type') === 'in'         ? 'selected' : '' }}>In</option>
                    <option value="out"        {{ request('type') === 'out'        ? 'selected' : '' }}>Out</option>
                    <option value="adjustment" {{ request('type') === 'adjustment' ? 'selected' : '' }}>Adjustment</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                <a href="{{ route('inventory.movements') }}" class="btn-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-th">Product</th>
                        <th class="table-th">Type</th>
                        <th class="table-th text-center">Qty</th>
                        <th class="table-th text-center hidden md:table-cell">Before → After</th>
                        <th class="table-th hidden lg:table-cell">Notes</th>
                        <th class="table-th hidden md:table-cell">By</th>
                        <th class="table-th">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $movement)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="table-td">
                                <a href="{{ route('inventory.show', $movement->product) }}"
                                   class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                    {{ $movement->product->name }}
                                </a>
                                @if($movement->product->sku)
                                    <p class="text-xs text-gray-400 font-mono">{{ $movement->product->sku }}</p>
                                @endif
                            </td>
                            <td class="table-td">
                                <span class="{{ match($movement->type) { 'in' => 'badge-green', 'out' => 'badge-red', 'adjustment' => 'badge-blue', default => 'badge-gray' } }}">
                                    {{ ucfirst($movement->type) }}
                                </span>
                            </td>
                            <td class="table-td text-center font-medium text-gray-700">
                                {{ $movement->quantity }}
                            </td>
                            <td class="table-td text-center text-sm text-gray-500 hidden md:table-cell">
                                {{ $movement->before_quantity }} → {{ $movement->after_quantity }}
                            </td>
                            <td class="table-td text-sm text-gray-500 hidden lg:table-cell max-w-xs truncate">
                                {{ $movement->notes ?? '—' }}
                            </td>
                            <td class="table-td text-sm text-gray-500 hidden md:table-cell">
                                {{ $movement->user->name }}
                            </td>
                            <td class="table-td text-sm text-gray-500 whitespace-nowrap">
                                {{ $movement->created_at->format('M j, Y') }}
                                <span class="text-xs text-gray-400">{{ $movement->created_at->format('H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="table-td py-12 text-center text-gray-400">
                                No stock movements recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
