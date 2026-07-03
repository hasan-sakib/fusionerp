<x-app-layout>
    @section('page-title', 'Categories')
    @section('header-actions')
        @can('create', \App\Models\Category::class)
            <a href="{{ route('categories.create') }}" class="btn-primary btn-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New Category
            </a>
        @endcan
    @endsection

    <div x-data="{ deleteId: null, deleteName: '', showDeleteModal: false }">

        <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-th">Name</th>
                            <th class="table-th hidden md:table-cell">Description</th>
                            <th class="table-th text-center">Products</th>
                            <th class="table-th text-center">Status</th>
                            <th class="table-th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($categories as $category)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="table-td font-medium text-gray-900">{{ $category->name }}</td>
                                <td class="table-td hidden md:table-cell text-gray-500 truncate max-w-xs">
                                    {{ $category->description ?? '—' }}
                                </td>
                                <td class="table-td text-center text-gray-700">
                                    {{ $category->products_count }}
                                </td>
                                <td class="table-td text-center">
                                    <span class="{{ $category->is_active ? 'badge-green' : 'badge-gray' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="table-td text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @can('view', $category)
                                            <a href="{{ route('categories.show', $category) }}" class="btn-secondary btn-sm">View</a>
                                        @endcan
                                        @can('update', $category)
                                            <a href="{{ route('categories.edit', $category) }}" class="btn-secondary btn-sm">Edit</a>
                                        @endcan
                                        @can('delete', $category)
                                            <button type="button" class="btn-danger btn-sm"
                                                    @click="deleteId = {{ $category->id }}; deleteName = '{{ addslashes($category->name) }}'; showDeleteModal = true">
                                                Delete
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="table-td py-12 text-center text-gray-400">
                                    No categories yet. Create your first category to organise products.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($categories->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">
                    {{ $categories->links() }}
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
                        <h3 class="text-base font-semibold text-gray-900">Delete category</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Are you sure you want to delete <strong x-text="deleteName"></strong>?
                            Categories with products cannot be deleted.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
                    <form method="POST" :action="`/categories/${deleteId}`">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
