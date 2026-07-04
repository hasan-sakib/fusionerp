<x-app-layout>
    @section('page-title', 'Orders')
    @section('header-actions')
        @can('create', \App\Models\Order::class)
            <a href="{{ route('orders.create') }}" class="btn-primary btn-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                New Order
            </a>
        @endcan
    @endsection

    {{-- Filters --}}
    <form method="GET" action="{{ route('orders.index') }}" class="mb-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-48">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Order #, customer name or email…"
                       class="form-input">
            </div>
            <div class="w-40">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(\App\Enums\OrderStatus::cases() as $s)
                        <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                            {{ $s->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary btn-sm">Filter</button>
                <a href="{{ route('orders.index') }}" class="btn-secondary btn-sm">Clear</a>
            </div>
        </div>
    </form>

    <div class="rounded-xl border border-gray-100 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-th">Order #</th>
                        <th class="table-th">Customer</th>
                        <th class="table-th hidden md:table-cell">Placed By</th>
                        <th class="table-th text-center">Items</th>
                        <th class="table-th text-right">Total</th>
                        <th class="table-th">Status</th>
                        <th class="table-th hidden lg:table-cell">Date</th>
                        <th class="table-th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="table-td font-mono font-semibold text-indigo-700 text-sm">
                                {{ $order->order_number }}
                            </td>
                            <td class="table-td">
                                <p class="font-medium text-gray-900">{{ $order->customer_name }}</p>
                                @if($order->customer_email)
                                    <p class="text-xs text-gray-400">{{ $order->customer_email }}</p>
                                @endif
                            </td>
                            <td class="table-td hidden md:table-cell text-gray-500 text-sm">
                                {{ $order->user->name }}
                            </td>
                            <td class="table-td text-center text-gray-700">
                                {{ $order->items->count() }}
                            </td>
                            <td class="table-td text-right font-semibold text-gray-900">
                                ${{ number_format($order->total_amount, 2) }}
                            </td>
                            <td class="table-td">
                                <span class="{{ $order->status->badgeClass() }}">{{ $order->status->label() }}</span>
                            </td>
                            <td class="table-td hidden lg:table-cell text-gray-500 text-sm">
                                {{ $order->created_at->format('M j, Y') }}
                            </td>
                            <td class="table-td text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('orders.show', $order) }}" class="btn-secondary btn-sm">View</a>
                                    @can('update', $order)
                                        <a href="{{ route('orders.edit', $order) }}" class="btn-secondary btn-sm">Edit</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="table-td py-12 text-center text-gray-400">
                                <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                                </svg>
                                No orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
