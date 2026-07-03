<x-app-layout>
    @section('page-title', ucfirst($role->name) . ' Role')
    @section('header-actions')
        @can('update', $role)
            <a href="{{ route('roles.edit', $role) }}" class="btn-secondary btn-sm">Edit Role</a>
        @endcan
    @endsection

    {{-- Breadcrumb --}}
    <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('roles.index') }}" class="hover:text-gray-700 transition-colors">Roles</a>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="font-medium text-gray-900 capitalize">{{ $role->name }}</span>
    </nav>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- Left: Role summary --}}
        <div class="space-y-4">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
                        {{ match($role->name) { 'admin' => 'bg-purple-100 text-purple-700', 'manager' => 'bg-blue-100 text-blue-700', default => 'bg-gray-100 text-gray-600' } }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold capitalize text-gray-900">{{ $role->name }}</h2>
                        @if(in_array($role->name, ['admin', 'manager', 'employee']))
                            <span class="badge-gray">Built-in</span>
                        @endif
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3 border-t border-gray-100 pt-4">
                    <div class="rounded-lg bg-gray-50 p-3 text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $role->permissions->count() }}</p>
                        <p class="text-xs text-gray-500">Permissions</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-3 text-center">
                        <p class="text-2xl font-bold text-indigo-600">{{ $users->count() }}</p>
                        <p class="text-xs text-gray-500">Users</p>
                    </div>
                </div>
            </div>

            {{-- Users with this role --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-gray-500">Users</h3>
                @if($users->isEmpty())
                    <p class="text-sm text-gray-400">No users have this role.</p>
                @else
                    <div class="space-y-2">
                        @foreach($users->take(10) as $user)
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                     class="h-7 w-7 rounded-full object-cover shrink-0">
                                <div class="min-w-0">
                                    <a href="{{ route('users.show', $user) }}"
                                       class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors truncate block">
                                        {{ $user->name }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                        @if($users->count() > 10)
                            <p class="text-xs text-gray-400 mt-1">+{{ $users->count() - 10 }} more</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Permissions by group --}}
        <div class="lg:col-span-2">
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Permissions</h3>

                @if($permissions->isEmpty())
                    <p class="text-sm text-gray-400">No permissions assigned to this role.</p>
                @else
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="rounded-lg border border-gray-100 p-4">
                                <h4 class="mb-3 text-sm font-semibold capitalize text-gray-700">{{ $group }}</h4>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($groupPermissions as $permission)
                                        <span class="badge-green font-mono text-xs">
                                            {{ \Illuminate\Support\Str::after($permission->name, '.') }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
