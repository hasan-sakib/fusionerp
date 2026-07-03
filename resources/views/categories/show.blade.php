<x-app-layout>
    @section('page-title', $category->name)
    @section('header-actions')
        <div class="flex items-center gap-2">
            @can('update', $category)
                <a href="{{ route('categories.edit', $category) }}" class="btn-secondary btn-sm">Edit</a>
            @endcan
            <a href="{{ route('categories.index') }}" class="btn-secondary btn-sm">&larr; Back</a>
        </div>
    @endsection

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Products in this Category</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-th">Product</th>
                                <th class="table-th">Price</th>
                                <th class="table-th text-center">Stock</th>
                                <th class="table-th">Status</th>
                                <th class="table-th text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($products as $product)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="table-td font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="table-td text-gray-700">${{ number_format($product->price, 2) }}</td>
                                    <td class="table-td text-center {{ $product->isOutOfStock() ? 'text-red-600' : ($product->isLowStock() ? 'text-yellow-600' : 'text-gray-700') }}">
                                        {{ $product->stock_quantity }}
                                    </td>
                                    <td class="table-td">
                                        <span class="{{ match($product->status) { 'active' => 'badge-green', 'inactive' => 'badge-yellow', default => 'badge-gray' } }}">
                                            {{ ucfirst($product->status) }}
                                        </span>
                                    </td>
                                    <td class="table-td text-right">
                                        <a href="{{ route('products.show', $product) }}" class="btn-secondary btn-sm">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="table-td py-8 text-center text-gray-400 text-sm">
                                        No products in this category.
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
        </div>

        <div class="space-y-6">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">Details</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="{{ $category->is_active ? 'badge-green' : 'badge-gray' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Slug</dt>
                        <dd class="font-mono text-gray-600 text-xs mt-1">{{ $category->slug }}</dd>
                    </div>
                    @if($category->description)
                        <div>
                            <dt class="text-gray-500">Description</dt>
                            <dd class="text-gray-700 mt-1">{{ $category->description }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-gray-500">Created</dt>
                        <dd class="text-gray-700 mt-1">{{ $category->created_at->format('M j, Y') }}</dd>
                    </div>
                </dl>
            </div>

            @can('delete', $category)
                <form method="POST" action="{{ route('categories.destroy', $category) }}"
                      onsubmit="return confirm('Delete this category?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm w-full">Delete Category</button>
                </form>
            @endcan
        </div>

    </div>
</x-app-layout>
