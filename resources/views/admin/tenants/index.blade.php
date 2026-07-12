<x-admin-layout>
    <x-slot name="pageTitle">Companies</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('admin.tenants.create') }}"
           class="inline-flex items-center gap-1.5 rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-500">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            New Company
        </a>
    </x-slot>

    <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-100">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Subdomain</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Plan</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Created</th>
                    <th class="relative px-5 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($tenants as $tenant)
                @php
                $statusColor = match($tenant->status) {
                    'active'    => 'bg-green-100 text-green-800',
                    'trial'     => 'bg-blue-100 text-blue-800',
                    'suspended' => 'bg-red-100 text-red-800',
                    default     => 'bg-gray-100 text-gray-600',
                };
                @endphp
                <tr class="{{ $tenant->trashed() ? 'opacity-50 bg-gray-50' : 'hover:bg-gray-50/50' }}">
                    <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="hover:text-violet-700">
                            {{ $tenant->name }}
                        </a>
                        @if ($tenant->trashed())
                            <span class="ml-1 text-xs text-gray-400">(archived)</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 text-sm">
                        <a href="{{ $tenant->subdomainUrl() }}"
                           class="font-mono text-violet-600 hover:text-violet-800 hover:underline"
                           target="_blank">
                            {{ $tenant->slug }}.localhost
                        </a>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 text-sm">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $statusColor }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-gray-500">
                        {{ $tenant->plan ?? '—' }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 text-sm text-gray-500">
                        {{ $tenant->created_at->format('Y-m-d') }}
                    </td>
                    <td class="whitespace-nowrap px-5 py-3.5 text-right text-sm">
                        @if ($tenant->trashed())
                            <form method="POST" action="{{ route('admin.tenants.restore', $tenant->id) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Restore this company?')"
                                        class="font-medium text-green-600 hover:text-green-900">
                                    Restore
                                </button>
                            </form>
                        @else
                            <a href="{{ route('admin.tenants.show', $tenant->id) }}"
                               class="font-medium text-violet-600 hover:text-violet-900">
                                View
                            </a>
                            <span class="mx-2 text-gray-200">|</span>
                            <a href="{{ route('admin.tenants.edit', $tenant) }}"
                               class="font-medium text-gray-600 hover:text-gray-900">
                                Edit
                            </a>
                            <span class="mx-2 text-gray-200">|</span>
                            <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Suspend and archive this company?')"
                                        class="font-medium text-red-600 hover:text-red-900">
                                    Suspend
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-10 text-center text-sm text-gray-400">
                        No companies yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tenants->links() }}
    </div>

</x-admin-layout>
