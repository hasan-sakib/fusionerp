@props(['striped' => false])

<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left text-gray-600">
            @if(isset($head))
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500 border-b border-gray-200">
                <tr>{{ $head }}</tr>
            </thead>
            @endif
            <tbody class="divide-y divide-gray-100">
                {{ $slot }}
            </tbody>
            @if(isset($foot))
            <tfoot class="bg-gray-50 border-t border-gray-200">
                {{ $foot }}
            </tfoot>
            @endif
        </table>
    </div>
    @if(isset($pagination))
    <div class="border-t border-gray-200 px-6 py-4">
        {{ $pagination }}
    </div>
    @endif
</div>
