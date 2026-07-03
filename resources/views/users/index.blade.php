<x-app-layout>
    @section('page-title', 'Users')
    @section('header-actions')
        @can('users.create')
            <a href="{{ route('users.create') }}" class="btn-primary btn-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New User
            </a>
        @endcan
    @endsection

    <div x-data="{ deleteId: null, deleteName: '', showDeleteModal: false }">

        {{-- Filters --}}
        <form method="GET" action="{{ route('users.index') }}" class="mb-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-48">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Name, email, department…"
                           class="form-input">
                </div>

                <div class="w-40">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                        <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <div class="w-40">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user()->hasRole('admin'))
                <div class="flex items-end pb-2">
                    <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-600">
                        <input type="checkbox" name="trashed" value="1"
                               {{ request('trashed') ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Show deleted
                    </label>
                </div>
                @endif

                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary btn-sm">Filter</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary btn-sm">Clear</a>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-th">User</th>
                            <th class="table-th">Role</th>
                            <th class="table-th">Status</th>
                            <th class="table-th hidden md:table-cell">Department</th>
                            <th class="table-th hidden lg:table-cell">Last Login</th>
                            <th class="table-th text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($users as $user)
                            @php
                                $roleName = $user->roles->first()?->name ?? 'none';
                                $roleBadge = match($roleName) {
                                    'admin'   => 'badge-purple',
                                    'manager' => 'badge-blue',
                                    default   => 'badge-gray',
                                };
                                $statusBadge = match($user->status) {
                                    'active'    => 'badge-green',
                                    'inactive'  => 'badge-yellow',
                                    'suspended' => 'badge-red',
                                    default     => 'badge-gray',
                                };
                            @endphp
                            <tr class="{{ $user->trashed() ? 'bg-red-50/40' : 'hover:bg-gray-50/50' }} transition-colors">
                                {{-- Avatar + Name + Email --}}
                                <td class="table-td">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                             class="h-9 w-9 rounded-full object-cover shrink-0
                                                    {{ $user->trashed() ? 'opacity-50 grayscale' : '' }}">
                                        <div class="min-w-0">
                                            <p class="font-medium text-gray-900 truncate">
                                                {{ $user->name }}
                                                @if($user->trashed())
                                                    <span class="badge-red ml-1">deleted</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>

                                {{-- Role --}}
                                <td class="table-td">
                                    <span class="{{ $roleBadge }}">{{ ucfirst($roleName) }}</span>
                                </td>

                                {{-- Status --}}
                                <td class="table-td">
                                    <span class="{{ $statusBadge }}">{{ ucfirst($user->status) }}</span>
                                </td>

                                {{-- Department --}}
                                <td class="table-td hidden md:table-cell text-gray-500">
                                    {{ $user->department ?? '—' }}
                                </td>

                                {{-- Last Login --}}
                                <td class="table-td hidden lg:table-cell text-gray-500">
                                    {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                                </td>

                                {{-- Actions --}}
                                <td class="table-td text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($user->trashed())
                                            @can('restore', $user)
                                                <form method="POST" action="{{ route('users.restore', $user->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn-secondary btn-sm">Restore</button>
                                                </form>
                                            @endcan
                                        @else
                                            <a href="{{ route('users.show', $user) }}" class="btn-secondary btn-sm">View</a>

                                            @can('update', $user)
                                                <a href="{{ route('users.edit', $user) }}" class="btn-secondary btn-sm">Edit</a>
                                            @endcan

                                            @can('delete', $user)
                                                @if(auth()->id() !== $user->id)
                                                    <button type="button" class="btn-danger btn-sm"
                                                            @click="deleteId = {{ $user->id }}; deleteName = '{{ addslashes($user->name) }}'; showDeleteModal = true">
                                                        Delete
                                                    </button>
                                                @endif
                                            @endcan
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="table-td py-12 text-center text-gray-400">
                                    <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                    </svg>
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">
                    {{ $users->links() }}
                </div>
            @endif
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
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900">Delete user</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Are you sure you want to delete <strong x-text="deleteName"></strong>?
                            Their data will be retained and can be restored by an administrator.
                        </p>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
                    <form method="POST" :action="`/users/${deleteId}`">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
