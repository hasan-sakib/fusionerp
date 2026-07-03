<x-app-layout>
    @section('page-title', 'Roles & Permissions')
    @section('header-actions')
        @can('create', \Spatie\Permission\Models\Role::class)
            <a href="{{ route('roles.create') }}" class="btn-primary btn-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New Role
            </a>
        @endcan
    @endsection

    <div x-data="{ deleteRole: null, showDeleteModal: false }">

        <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-th">Role</th>
                            <th class="table-th text-center">Permissions</th>
                            <th class="table-th text-center">Users</th>
                            <th class="table-th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($roles as $role)
                            @php $isBuiltIn = in_array($role->name, ['admin', 'manager', 'employee']); @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="table-td">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg
                                            {{ match($role->name) { 'admin' => 'bg-purple-100 text-purple-700', 'manager' => 'bg-blue-100 text-blue-700', default => 'bg-gray-100 text-gray-600' } }}">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="font-semibold capitalize text-gray-900">{{ $role->name }}</p>
                                            @if($isBuiltIn)
                                                <span class="badge-gray text-xs">Built-in</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="table-td text-center">
                                    <span class="badge-blue">{{ $role->permissions_count }}</span>
                                </td>
                                <td class="table-td text-center">
                                    <span class="badge-gray">{{ $role->user_count }}</span>
                                </td>
                                <td class="table-td text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('roles.show', $role) }}" class="btn-secondary btn-sm">View</a>

                                        @can('update', $role)
                                            <a href="{{ route('roles.edit', $role) }}" class="btn-secondary btn-sm">Edit</a>
                                        @endcan

                                        @can('delete', $role)
                                            @if(! $isBuiltIn)
                                                <button type="button" class="btn-danger btn-sm"
                                                        @click="deleteRole = { id: {{ $role->id }}, name: '{{ $role->name }}' }; showDeleteModal = true">
                                                    Delete
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Delete confirmation modal --}}
        <div x-show="showDeleteModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4"
             style="display:none;"
             @keydown.escape.window="showDeleteModal = false">
            <div @click.outside="showDeleteModal = false"
                 class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold text-gray-900">Delete role?</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Delete the <strong x-text="deleteRole?.name"></strong> role?
                    This cannot be undone. Users with this role will lose their access.
                </p>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
                    <form method="POST" :action="`/roles/${deleteRole?.id}`">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
