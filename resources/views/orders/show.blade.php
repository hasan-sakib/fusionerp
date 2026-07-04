<x-app-layout>
    @section('page-title', $order->order_number)
    @section('header-actions')
        <div class="flex items-center gap-2">
            @can('update', $order)
                <a href="{{ route('orders.edit', $order) }}" class="btn-secondary btn-sm">Edit</a>
            @endcan
            <a href="{{ route('orders.index') }}" class="btn-secondary btn-sm">&larr; Orders</a>
        </div>
    @endsection

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Main: items --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Header card --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h1 class="text-xl font-bold text-gray-900 font-mono">{{ $order->order_number }}</h1>
                            <span class="{{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">
                            Placed by {{ $order->user->name }} on {{ $order->created_at->format('M j, Y \a\t H:i') }}
                        </p>
                        @if($order->cancelled_at)
                            <p class="mt-1 text-sm text-red-500">
                                Cancelled {{ $order->cancelled_at->diffForHumans() }} by {{ $order->cancelledBy?->name ?? 'Unknown' }}
                            </p>
                        @endif
                    </div>

                    {{-- Status change --}}
                    @php $transitions = $order->status->allowedTransitions(); @endphp
                    @if(count($transitions) > 0)
                        <div x-data="{ open: false }" class="relative shrink-0">
                            <button @click="open = !open" class="btn-secondary btn-sm flex items-center gap-1">
                                Change Status
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </button>
                            <div x-show="open" @click.outside="open = false"
                                 x-transition:enter="ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-gray-100 py-1 z-20"
                                 style="display:none;">
                                @foreach($transitions as $newStatus)
                                    @php
                                        $canDo = $newStatus === 'cancelled'
                                            ? auth()->user()->can('cancel', $order)
                                            : auth()->user()->can('process', $order);
                                    @endphp
                                    @if($canDo)
                                        <form method="POST" action="{{ route('orders.status', $order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $newStatus }}">
                                            <button type="submit"
                                                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50
                                                           {{ $newStatus === 'cancelled' ? 'text-red-600 hover:bg-red-50' : 'text-gray-700' }}">
                                                Mark as {{ ucfirst($newStatus) }}
                                            </button>
                                        </form>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Line items --}}
            <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-900">Order Items</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="table-th">Product</th>
                                <th class="table-th hidden md:table-cell">SKU</th>
                                <th class="table-th text-center">Qty</th>
                                <th class="table-th text-right">Unit Price</th>
                                <th class="table-th text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($order->items as $item)
                                <tr>
                                    <td class="table-td">
                                        <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                                        @if($item->product)
                                            <a href="{{ route('products.show', $item->product) }}"
                                               class="text-xs text-indigo-600 hover:underline">View product</a>
                                        @else
                                            <span class="text-xs text-gray-400">Product deleted</span>
                                        @endif
                                    </td>
                                    <td class="table-td hidden md:table-cell text-gray-500 font-mono text-sm">
                                        {{ $item->sku ?? '—' }}
                                    </td>
                                    <td class="table-td text-center text-gray-700">{{ $item->quantity }}</td>
                                    <td class="table-td text-right text-gray-700">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="table-td text-right font-semibold text-gray-900">${{ number_format($item->total_price, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">

            {{-- Customer --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Customer</h2>
                <dl class="space-y-2 text-sm">
                    <div>
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium text-gray-900 mt-0.5">{{ $order->customer_name }}</dd>
                    </div>
                    @if($order->customer_email)
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd class="text-gray-700 mt-0.5">{{ $order->customer_email }}</dd>
                        </div>
                    @endif
                    @if($order->customer_phone)
                        <div>
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="text-gray-700 mt-0.5">{{ $order->customer_phone }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Totals --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <dt>Subtotal</dt>
                        <dd>${{ number_format($order->subtotal, 2) }}</dd>
                    </div>
                    @if($order->tax_rate > 0)
                        <div class="flex justify-between text-gray-600">
                            <dt>Tax ({{ number_format($order->tax_rate, 1) }}%)</dt>
                            <dd>${{ number_format($order->tax_amount, 2) }}</dd>
                        </div>
                    @endif
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-gray-600">
                            <dt>Discount</dt>
                            <dd class="text-red-600">-${{ number_format($order->discount_amount, 2) }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-200 pt-2">
                        <dt>Total</dt>
                        <dd>${{ number_format($order->total_amount, 2) }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Notes --}}
            @if($order->notes)
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-2">Notes</h2>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $order->notes }}</p>
                </div>
            @endif

            {{-- Danger zone --}}
            @can('delete', $order)
                <form method="POST" action="{{ route('orders.destroy', $order) }}"
                      onsubmit="return confirm('Permanently remove this order record?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger btn-sm w-full">Delete Order Record</button>
                </form>
            @endcan

        </div>
    </div>
</x-app-layout>
