@props(['title', 'description' => null])

<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h2 class="text-xl font-bold text-gray-900">{{ $title }}</h2>
        @if($description)
        <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @if(isset($actions))
    <div class="flex shrink-0 items-center gap-3">
        {{ $actions }}
    </div>
    @endif
</div>
