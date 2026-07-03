<x-app-layout>
    @section('page-title', $title ?? 'Coming Soon')

    <div class="flex flex-col items-center justify-center py-24">
        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 mb-4">
            <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $title ?? 'Module' }} — Coming Soon</h2>
        <p class="text-sm text-gray-500">This module is being built. Check back after the next module is confirmed.</p>
    </div>
</x-app-layout>
