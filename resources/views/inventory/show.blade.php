<x-app-layout>
    @section('page-title', 'Inventory: ' . $product->name)
    @section('header-actions')
        <div class="flex items-center gap-2">
            <a href="{{ route('products.show', $product) }}" class="btn-secondary btn-sm">Product Details</a>
            <a href="{{ route('inventory.index') }}" class="btn-secondary btn-sm">&larr; Inventory</a>
        </div>
    @endsection

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Movement history --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Stock Movement History</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-th">Type</th>
                                <th class="table-th text-center">Qty</th>
                                <th class="table-th text-center">Before → After</th>
                                <th class="table-th hidden md:table-cell">Notes</th>
                                <th class="table-th hidden md:table-cell">By</th>
                                <th class="table-th">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($movements as $movement)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="table-td">
                                        <span class="{{ match($movement->type) { 'in' => 'badge-green', 'out' => 'badge-red', 'adjustment' => 'badge-blue', default => 'badge-gray' } }}">
                                            {{ ucfirst($movement->type) }}
                                        </span>
                                    </td>
                                    <td class="table-td text-center font-medium text-gray-700">
                                        {{ $movement->quantity }}
                                    </td>
                                    <td class="table-td text-center text-sm text-gray-500">
                                        {{ $movement->before_quantity }} → {{ $movement->after_quantity }}
                                    </td>
                                    <td class="table-td hidden md:table-cell text-gray-500 text-sm max-w-xs truncate">
                                        {{ $movement->notes ?? '—' }}
                                    </td>
                                    <td class="table-td hidden md:table-cell text-gray-500 text-sm">
                                        {{ $movement->user->name }}
                                    </td>
                                    <td class="table-td text-sm text-gray-500">
                                        {{ $movement->created_at->format('M j, Y H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="table-td py-8 text-center text-gray-400 text-sm">
                                        No movements recorded yet.
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
        </div>

        {{-- Sidebar: stock summary + adjust form --}}
        <div class="space-y-6">

            {{-- Stock card --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Current Stock</h2>
                @php
                    $stockClass = $product->isOutOfStock()
                        ? 'text-red-600'
                        : ($product->isLowStock() ? 'text-yellow-600' : 'text-green-600');
                @endphp
                <div class="text-4xl font-bold {{ $stockClass }}">
                    {{ $product->stock_quantity }}
                </div>
                <p class="text-sm text-gray-500 mt-1">units</p>
                @if($product->isOutOfStock())
                    <p class="mt-2 text-sm text-red-600 font-medium">Out of stock</p>
                @elseif($product->isLowStock())
                    <p class="mt-2 text-sm text-yellow-600 font-medium">Below minimum level ({{ $product->min_stock_level }})</p>
                @endif
            </div>

            {{-- Adjust form --}}
            @can('update', $product)
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm"
                     x-data="{ type: '{{ old('adjustment_type', 'add') }}' }">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">Adjust Stock</h2>

                    @if(session('success'))
                        <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('inventory.adjust', $product) }}">
                        @csrf

                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Adjustment Type <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-3 gap-2 mt-1">
                                    @foreach(['add' => 'Add', 'subtract' => 'Remove', 'set' => 'Set to'] as $val => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="adjustment_type" value="{{ $val }}"
                                                   x-model="type"
                                                   class="sr-only peer"
                                                   {{ old('adjustment_type', 'add') === $val ? 'checked' : '' }}>
                                            <div class="rounded-lg border-2 border-gray-200 px-2 py-2 text-center text-sm font-medium text-gray-600
                                                        peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                                                        hover:border-gray-300 transition-colors">
                                                {{ $label }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('adjustment_type') <p class="form-error mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="quantity" class="form-label">
                                    Quantity <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="quantity" name="quantity"
                                       value="{{ old('quantity', 0) }}"
                                       min="0" step="1"
                                       class="form-input @error('quantity') border-red-400 @enderror">
                                <p class="mt-1 text-xs text-gray-400"
                                   x-show="type === 'subtract'">
                                    Cannot go below 0. Current: {{ $product->stock_quantity }}
                                </p>
                                @error('quantity') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" rows="2"
                                          class="form-input @error('notes') border-red-400 @enderror"
                                          placeholder="Reason for adjustment…">{{ old('notes') }}</textarea>
                                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="btn-primary w-full">Apply Adjustment</button>
                        </div>
                    </form>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>
