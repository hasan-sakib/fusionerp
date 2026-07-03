<x-app-layout>
    @section('page-title', 'Products')
    @section('header-actions')
        @can('create', \App\Models\Product::class)
            <a href="{{ route('products.create') }}" class="btn-primary btn-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New Product
            </a>
        @endcan
    @endsection

    <div x-data="{ deleteId: null, deleteName: '', showDeleteModal: false }">

        {{-- Filters --}}
        <form method="GET" action="{{ route('products.index') }}" class="mb-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-48">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Name, SKU, barcode…"
                           class="form-input">
                </div>

                <div class="w-44">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="w-36">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="draft"    {{ request('status') === 'draft'    ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
                        <input type="checkbox" name="low_stock" value="1"
                               {{ request('low_stock') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Low stock only
                    </label>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary btn-sm">Filter</button>
                    <a href="{{ route('products.index') }}" class="btn-secondary btn-sm">Clear</a>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-th">Product</th>
                            <th class="table-th hidden md:table-cell">SKU</th>
                            <th class="table-th hidden md:table-cell">Category</th>
                            <th class="table-th">Price</th>
                            <th class="table-th">Stock</th>
                            <th class="table-th">Status</th>
                            <th class="table-th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($products as $product)
                            @php
                                $statusBadge = match($product->status) {
                                    'active'   => 'badge-green',
                                    'inactive' => 'badge-yellow',
                                    'draft'    => 'badge-gray',
                                    default    => 'badge-gray',
                                };
                                $stockClass = $product->isOutOfStock()
                                    ? 'text-red-600 font-semibold'
                                    : ($product->isLowStock() ? 'text-yellow-600 font-semibold' : 'text-gray-700');
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="table-td">
                                    <div class="flex items-center gap-3">
                                        @if($product->image)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                                 class="h-10 w-10 rounded-lg object-cover shrink-0 border border-gray-200">
                                        @else
                                            <div class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0">
                                                <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 truncate">{{ $product->name }}</p>
                                            @if($product->is_featured)
                                                <span class="badge-blue text-xs">Featured</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="table-td hidden md:table-cell text-gray-500 font-mono text-sm">
                                    {{ $product->sku ?? '—' }}
                                </td>
                                <td class="table-td hidden md:table-cell text-gray-500">
                                    {{ $product->category?->name ?? '—' }}
                                </td>
                                <td class="table-td text-gray-700">
                                    ${{ number_format($product->price, 2) }}
                                </td>
                                <td class="table-td {{ $stockClass }}">
                                    {{ $product->stock_quantity }}
                                    @if($product->isOutOfStock())
                                        <span class="ml-1 badge-red text-xs">Out</span>
                                    @elseif($product->isLowStock())
                                        <span class="ml-1 badge-yellow text-xs">Low</span>
                                    @endif
                                </td>
                                <td class="table-td">
                                    <span class="{{ $statusBadge }}">{{ ucfirst($product->status) }}</span>
                                </td>
                                <td class="table-td text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('products.show', $product) }}" class="btn-secondary btn-sm">View</a>
                                        @can('update', $product)
                                            <a href="{{ route('products.edit', $product) }}" class="btn-secondary btn-sm">Edit</a>
                                        @endcan
                                        @can('delete', $product)
                                            <button type="button" class="btn-danger btn-sm"
                                                    @click="deleteId = {{ $product->id }}; deleteName = '{{ addslashes($product->name) }}'; showDeleteModal = true">
                                                Delete
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="table-td py-12 text-center text-gray-400">
                                    <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                                    </svg>
                                    No products found.
                                </td>
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

        {{-- Delete modal --}}
        <div x-show="showDeleteModal"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4"
             style="display:none;"
             @keydown.escape.window="showDeleteModal = false">
            <div @click.outside="showDeleteModal = false" class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900">Delete product</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Are you sure you want to delete <strong x-text="deleteName"></strong>?
                            The product will be soft-deleted and can be restored.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
                    <form method="POST" :action="`/products/${deleteId}`">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
