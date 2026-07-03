<x-app-layout>
    @section('page-title', $user->name)
    @section('header-actions')
        @can('update', $user)
            <a href="{{ route('users.edit', $user) }}" class="btn-secondary btn-sm">Edit</a>
        @endcan
    @endsection

    <div x-data="{ showDeleteModal: false, showResetModal: false }">

        {{-- Breadcrumb --}}
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('users.index') }}" class="hover:text-gray-700 transition-colors">Users</a>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="font-medium text-gray-900">{{ $user->name }}</span>
        </nav>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            {{-- Left: Profile Card --}}
            <div class="lg:col-span-1">
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm text-center">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                         class="mx-auto h-24 w-24 rounded-full object-cover ring-4 ring-gray-100">
                    <h2 class="mt-4 text-xl font-bold text-gray-900">{{ $user->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $user->email }}</p>

                    <div class="mt-3 flex items-center justify-center gap-2 flex-wrap">
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
                        <span class="{{ $roleBadge }}">{{ ucfirst($roleName) }}</span>
                        <span class="{{ $statusBadge }}">{{ ucfirst($user->status) }}</span>
                    </div>

                    <div class="mt-5 space-y-2 text-left border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Last login</span>
                            <span class="font-medium text-gray-700">
                                {{ $user->last_login_at?->diffForHumans() ?? 'Never' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Member since</span>
                            <span class="font-medium text-gray-700">{{ $user->created_at->format('M j, Y') }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Email verified</span>
                            @if($user->email_verified_at)
                                <span class="badge-green">Verified</span>
                            @else
                                <span class="badge-yellow">Pending</span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-5 space-y-2 border-t border-gray-100 pt-4">
                        @can('update', $user)
                            <button type="button" @click="showResetModal = true"
                                    class="btn-secondary w-full justify-center">
                                Send Password Reset
                            </button>
                        @endcan

                        @can('delete', $user)
                            @if(auth()->id() !== $user->id)
                                <button type="button" @click="showDeleteModal = true"
                                        class="btn-danger w-full justify-center">
                                    Delete User
                                </button>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>

            {{-- Right: Details --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Contact & Employment --}}
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Details</h3>
                    <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Phone</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Department</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->department ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Position</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->position ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-400 uppercase tracking-wide">Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Permissions summary --}}
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Permissions</h3>
                    @php
                        $permissions = $user->getAllPermissions()->pluck('name')->sort()->values();
                    @endphp
                    @if($permissions->isEmpty())
                        <p class="text-sm text-gray-400">No permissions assigned.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach($permissions as $permission)
                                <span class="badge-gray font-mono text-xs">{{ $permission }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

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
                <h3 class="text-base font-semibold text-gray-900">Delete {{ $user->name }}?</h3>
                <p class="mt-2 text-sm text-gray-500">
                    Their data will be retained and can be restored later by an administrator.
                </p>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="showDeleteModal = false" class="btn-secondary">Cancel</button>
                    <form method="POST" action="{{ route('users.destroy', $user) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Reset password confirmation modal --}}
        <div x-show="showResetModal"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 px-4"
             style="display:none;"
             @keydown.escape.window="showResetModal = false">
            <div @click.outside="showResetModal = false"
                 class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="text-base font-semibold text-gray-900">Send password reset?</h3>
                <p class="mt-2 text-sm text-gray-500">
                    A password reset link will be emailed to <strong>{{ $user->email }}</strong>.
                    The link expires in 60 minutes.
                </p>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="showResetModal = false" class="btn-secondary">Cancel</button>
                    <form method="POST" action="{{ route('users.reset-password', $user) }}">
                        @csrf
                        <button type="submit" class="btn-primary">Send Email</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
