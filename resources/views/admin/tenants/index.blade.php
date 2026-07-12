<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Platform Admin — Tenants
            </h2>
            <a href="{{ route('admin.tenants.create') }}"
               class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                New Tenant
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="overflow-hidden rounded-lg shadow ring-1 ring-black/5 dark:ring-white/10">
                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Created</th>
                            <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @forelse ($tenants as $tenant)
                            <tr class="{{ $tenant->trashed() ? 'opacity-50' : '' }}">
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $tenant->name }}
                                    @if ($tenant->trashed())
                                        <span class="ml-1 text-xs text-gray-400">(archived)</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400 font-mono">
                                    {{ $tenant->slug }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm">
                                    @php
                                        $colors = [
                                            'active'    => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                            'trial'     => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                            'inactive'  => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                            'suspended' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $colors[$tenant->status] ?? '' }}">
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $tenant->plan ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $tenant->created_at->format('Y-m-d') }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                    @unless ($tenant->trashed())
                                        <a href="{{ route('admin.tenants.edit', $tenant) }}"
                                           class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            Edit
                                        </a>
                                        <span class="mx-2 text-gray-300 dark:text-gray-600">|</span>
                                        <form method="POST" action="{{ route('admin.tenants.destroy', $tenant) }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Suspend and archive this tenant?')"
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400">
                                                Suspend
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No tenants yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tenants->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
