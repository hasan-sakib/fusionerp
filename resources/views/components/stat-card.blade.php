@props([
    'label'  => '',
    'value'  => '0',
    'change' => null,
    'trend'  => 'neutral',   // up | down | neutral
    'color'  => 'indigo',    // indigo | green | yellow | red | purple
    'icon'   => null,
])

@php
    $colors = [
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'green'  => 'bg-green-50 text-green-600',
        'yellow' => 'bg-yellow-50 text-yellow-600',
        'red'    => 'bg-red-50 text-red-600',
        'purple' => 'bg-purple-50 text-purple-600',
    ];
    $bgClass = $colors[$color] ?? $colors['indigo'];
    $trendColor = $trend === 'up' ? 'text-green-600' : ($trend === 'down' ? 'text-red-600' : 'text-gray-500');
@endphp

<div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="flex items-start justify-between">
        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium text-gray-500 truncate">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $value }}</p>
            @if($change)
            <p class="mt-1 text-xs font-medium {{ $trendColor }}">
                @if($trend === 'up')↑ @elseif($trend === 'down')↓ @endif
                {{ $change }}
            </p>
            @endif
        </div>
        @if($icon)
        <div class="ml-4 flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $bgClass }}">
            {!! $icon !!}
        </div>
        @endif
    </div>
    @if(isset($footer))
    <div class="mt-4 border-t border-gray-50 pt-4 text-sm text-gray-500">
        {{ $footer }}
    </div>
    @endif
</div>
