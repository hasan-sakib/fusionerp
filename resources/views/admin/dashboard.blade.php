<x-admin-layout>
    <x-slot name="pageTitle">Platform Overview</x-slot>

    {{-- ── Stat Cards ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

        @php
        $cards = [
            ['label' => 'Total Companies', 'value' => $stats['total'],     'color' => 'violet', 'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
            ['label' => 'Active',          'value' => $stats['active'],    'color' => 'green',  'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Trial',           'value' => $stats['trial'],     'color' => 'blue',   'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Suspended',       'value' => $stats['suspended'], 'color' => 'red',    'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
        ];
        $colorMap = [
            'violet' => ['bg' => 'bg-violet-50', 'icon' => 'bg-violet-100 text-violet-600', 'text' => 'text-violet-700'],
            'green'  => ['bg' => 'bg-green-50',  'icon' => 'bg-green-100 text-green-600',   'text' => 'text-green-700'],
            'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'bg-blue-100 text-blue-600',     'text' => 'text-blue-700'],
            'red'    => ['bg' => 'bg-red-50',    'icon' => 'bg-red-100 text-red-600',       'text' => 'text-red-700'],
        ];
        @endphp

        @foreach ($cards as $card)
        @php $c = $colorMap[$card['color']]; @endphp
        <div class="rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $c['icon'] }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($card['value']) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Total Users ─────────────────────────────────────────────────────── --}}
    <div class="mt-5 rounded-xl border border-gray-100 bg-white p-5 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-100 text-purple-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Total Users (all companies)</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</p>
            </div>
        </div>
    </div>

    {{-- ── Recent Companies ─────────────────────────────────────────────────── --}}
    <div class="mt-6">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">Recently Added Companies</h2>
            <a href="{{ route('admin.tenants.index') }}" class="text-xs font-medium text-violet-600 hover:text-violet-800">
                View all →
            </a>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Company</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Subdomain</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Created</th>
                        <th class="relative px-5 py-3"><span class="sr-only">View</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recent as $tenant)
                    @php
                    $statusColor = match($tenant->status) {
                        'active'    => 'bg-green-100 text-green-700',
                        'trial'     => 'bg-blue-100 text-blue-700',
                        'suspended' => 'bg-red-100 text-red-700',
                        default     => 'bg-gray-100 text-gray-600',
                    };
                    @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-gray-900">
                            {{ $tenant->name }}
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
                            {{ $tenant->created_at->format('Y-m-d') }}
                        </td>
                        <td class="whitespace-nowrap px-5 py-3.5 text-right text-sm">
                            <a href="{{ route('admin.tenants.show', $tenant->id) }}"
                               class="text-violet-600 hover:text-violet-900 font-medium">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">
                            No companies yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-admin-layout>
