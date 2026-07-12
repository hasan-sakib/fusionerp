<x-admin-layout>
    <x-slot name="pageTitle">{{ $tenant->name }}</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('admin.tenants.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
            </svg>
            Back to Companies
        </a>
        @unless ($tenant->trashed())
        <a href="{{ route('admin.tenants.edit', $tenant->id) }}"
           class="inline-flex items-center rounded-md bg-violet-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-violet-500">
            Edit
        </a>
        @endunless
    </x-slot>

    <div class="space-y-6">

        {{-- ── Tenant Info Card ─────────────────────────────────────────────── --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-100 text-violet-700 text-lg font-bold">
                            {{ strtoupper(substr($tenant->name, 0, 2)) }}
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">
                                {{ $tenant->name }}
                                @if ($tenant->trashed())
                                    <span class="ml-2 text-sm font-normal text-gray-400">(archived)</span>
                                @endif
                            </h2>
                            <a href="{{ $tenant->subdomainUrl() }}"
                               class="font-mono text-sm text-violet-600 hover:underline"
                               target="_blank">
                                {{ $tenant->subdomainUrl() }}
                            </a>
                        </div>
                    </div>
                </div>

                @php
                $statusColor = match($tenant->status) {
                    'active'    => 'bg-green-100 text-green-800',
                    'trial'     => 'bg-blue-100 text-blue-800',
                    'suspended' => 'bg-red-100 text-red-800',
                    default     => 'bg-gray-100 text-gray-600',
                };
                @endphp
                <span class="inline-flex rounded-full px-3 py-1 text-sm font-semibold {{ $statusColor }}">
                    {{ ucfirst($tenant->status) }}
                </span>
            </div>

            <dl class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Slug</dt>
                    <dd class="mt-1 font-mono text-gray-700">{{ $tenant->slug }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Plan</dt>
                    <dd class="mt-1 text-gray-700">{{ $tenant->plan ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Created</dt>
                    <dd class="mt-1 text-gray-700">{{ $tenant->created_at->format('Y-m-d') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-400">Users</dt>
                    <dd class="mt-1 text-gray-700">{{ $users->count() }}</dd>
                </div>
            </dl>

            @if ($tenant->trashed())
            <div class="mt-5 border-t border-gray-100 pt-4">
                <form method="POST" action="{{ route('admin.tenants.restore', $tenant->id) }}">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Restore this tenant and set status to active?')"
                            class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-500">
                        Restore Tenant
                    </button>
                </form>
            </div>
            @endif

            @if ($tenant->settings)
            <div class="mt-5 border-t border-gray-100 pt-4" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-gray-800">
                    <svg class="h-3.5 w-3.5 transition-transform" :class="open ? 'rotate-90' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                    Tenant Settings
                </button>
                <div x-show="open" x-cloak class="mt-3 overflow-x-auto rounded-lg bg-gray-50 p-4">
                    <pre class="text-xs text-gray-600">{{ json_encode($tenant->settings, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>

        {{-- ── Users Table ──────────────────────────────────────────────────── --}}
        <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Users</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Role(s)</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                    <tr class="hover:bg-gray-50/50">
                        <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-gray-900">
                            {{ $user->name }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-sm text-gray-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-5 py-3.5 text-sm text-gray-500">
                            @forelse ($user->roles as $role)
                                <span class="inline-flex rounded-full bg-violet-50 px-2 py-0.5 text-xs font-medium text-violet-700 mr-1">
                                    {{ $role->name }}
                                </span>
                            @empty
                                <span class="text-gray-400">—</span>
                            @endforelse
                        </td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-sm">
                            @php
                            $sc = match($user->status ?? 'active') {
                                'active'    => 'bg-green-100 text-green-700',
                                'inactive'  => 'bg-gray-100 text-gray-600',
                                'suspended' => 'bg-red-100 text-red-700',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                            @endphp
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $sc }}">
                                {{ ucfirst($user->status ?? 'active') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-gray-400">
                            No users in this company.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</x-admin-layout>
