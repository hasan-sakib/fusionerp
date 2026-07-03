<x-app-layout>
    @section('page-title', $product->name)
    @section('header-actions')
        <div class="flex items-center gap-2">
            @can('update', $product)
                <a href="{{ route('products.edit', $product) }}" class="btn-secondary btn-sm">Edit</a>
            @endcan
            @can('viewAny', \App\Models\Product::class)
                <a href="{{ route('inventory.show', $product) }}" class="btn-secondary btn-sm">Inventory</a>
            @endcan
            <a href="{{ route('products.index') }}" class="btn-secondary btn-sm">&larr; Back</a>
        </div>
    @endsection

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Main details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    @if($product->image)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                             class="h-24 w-24 rounded-xl object-cover border border-gray-200 shrink-0">
                    @else
                        <div class="h-24 w-24 rounded-xl bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0">
                            <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                        </div>
                    @endif

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h1 class="text-xl font-bold text-gray-900">{{ $product->name }}</h1>
                            @php
                                $statusBadge = match($product->status) {
                                    'active'   => 'badge-green',
                                    'inactive' => 'badge-yellow',
                                    'draft'    => 'badge-gray',
                                    default    => 'badge-gray',
                                };
                            @endphp
                            <span class="{{ $statusBadge }}">{{ ucfirst($product->status) }}</span>
                            @if($product->is_featured)
                                <span class="badge-blue">Featured</span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $product->category?->name ?? 'No category' }}
                            @if($product->sku)
                                &middot; SKU: <span class="font-mono">{{ $product->sku }}</span>
                            @endif
                            @if($product->barcode)
                                &middot; Barcode: <span class="font-mono">{{ $product->barcode }}</span>
                            @endif
                        </p>
                        @if($product->description)
                            <p class="mt-3 text-sm text-gray-600">{{ $product->description }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Recent movements --}}
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="font-semibold text-gray-900">Recent Stock Movements</h2>
                    <a href="{{ route('inventory.show', $product) }}" class="text-sm text-indigo-600 hover:text-indigo-800">View all</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($movements as $movement)
                        @php
                            $typeBadge = match($movement->type) {
                                'in'         => 'badge-green',
                                'out'        => 'badge-red',
                                'adjustment' => 'badge-blue',
                                default      => 'badge-gray',
                            };
                        @endphp
                        <div class="px-6 py-3 flex items-center justify-between text-sm">
                            <div class="flex items-center gap-3">
                                <span class="{{ $typeBadge }}">{{ ucfirst($movement->type) }}</span>
                                <span class="text-gray-600">{{ $movement->notes ?? '—' }}</span>
                                <span class="text-gray-400 text-xs">by {{ $movement->user->name }}</span>
                            </div>
                            <div class="text-right shrink-0 ml-4">
                                <p class="font-medium text-gray-700">Qty: {{ $movement->quantity }}</p>
                                <p class="text-xs text-gray-400">{{ $movement->before_quantity }} → {{ $movement->after_quantity }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-400 text-sm">No movements recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">Pricing</h2>
                <dl class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <dt class="text-gray-500">Selling Price</dt>
                        <dd class="font-semibold text-gray-900">${{ number_format($product->price, 2) }}</dd>
                    </div>
                    @if($product->cost)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Cost Price</dt>
                            <dd class="font-medium text-gray-700">${{ number_format($product->cost, 2) }}</dd>
                        </div>
                        <div class="flex justify-between text-sm border-t border-gray-100 pt-2">
                            <dt class="text-gray-500">Margin</dt>
                            <dd class="font-medium text-green-600">
                                ${{ number_format($product->price - $product->cost, 2) }}
                                @if($product->price > 0)
                                    ({{ number_format((($product->price - $product->cost) / $product->price) * 100, 1) }}%)
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">Stock</h2>
                @php
                    $stockClass = $product->isOutOfStock()
                        ? 'text-red-600'
                        : ($product->isLowStock() ? 'text-yellow-600' : 'text-green-600');
                @endphp
                <div class="text-3xl font-bold {{ $stockClass }}">
                    {{ $product->stock_quantity }}
                </div>
                <p class="text-sm text-gray-500 mt-1">units in stock</p>
                @if($product->min_stock_level > 0)
                    <p class="text-xs text-gray-400 mt-2">Min level: {{ $product->min_stock_level }}</p>
                @endif

                @can('update', $product)
                    <div class="mt-4">
                        <a href="{{ route('inventory.show', $product) }}" class="btn-primary btn-sm w-full text-center">
                            Adjust Stock
                        </a>
                    </div>
                @endcan
            </div>

            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Details</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Created</dt>
                        <dd class="text-gray-700">{{ $product->created_at->format('M j, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Updated</dt>
                        <dd class="text-gray-700">{{ $product->updated_at->diffForHumans() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Slug</dt>
                        <dd class="font-mono text-gray-600 text-xs">{{ $product->slug }}</dd>
                    </div>
                </dl>
            </div>

            @can('delete', $product)
                <form method="POST" action="{{ route('products.destroy', $product) }}"
                      onsubmit="return confirm('Delete this product?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm w-full">Delete Product</button>
                </form>
            @endcan
        </div>

    </div>
</x-app-layout>
