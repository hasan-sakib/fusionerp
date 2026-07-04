<x-app-layout>
    @section('page-title', 'New Order')
    @section('header-actions')
        <a href="{{ route('orders.index') }}" class="btn-secondary btn-sm">&larr; Back to Orders</a>
    @endsection

    <div class="mx-auto max-w-4xl"
         x-data="orderForm({{ $products->toJson() }})">

        @if(session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('orders.store') }}" @submit="prepareSubmit">
            @csrf

            <div class="space-y-6">

                {{-- Customer Info --}}
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-4">Customer Information</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="customer_name" class="form-label">Customer Name <span class="text-red-500">*</span></label>
                            <input type="text" id="customer_name" name="customer_name"
                                   value="{{ old('customer_name') }}"
                                   class="form-input @error('customer_name') border-red-400 @enderror">
                            @error('customer_name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" id="customer_email" name="customer_email"
                                   value="{{ old('customer_email') }}"
                                   class="form-input @error('customer_email') border-red-400 @enderror">
                            @error('customer_email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="customer_phone" class="form-label">Phone</label>
                            <input type="text" id="customer_phone" name="customer_phone"
                                   value="{{ old('customer_phone') }}"
                                   class="form-input @error('customer_phone') border-red-400 @enderror">
                            @error('customer_phone') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Line Items --}}
                <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Line Items</h2>
                        <button type="button" @click="addLine()" class="btn-secondary btn-sm">+ Add Item</button>
                    </div>

                    @error('items') <p class="form-error mb-3">{{ $message }}</p> @enderror

                    <div class="space-y-3">
                        <template x-for="(line, index) in lines" :key="index">
                            <div class="flex items-start gap-3 rounded-lg border border-gray-100 p-3">
                                <div class="flex-1 min-w-0">
                                    <label class="form-label">Product</label>
                                    <select x-model="line.product_id" @change="onProductChange(index)"
                                            class="form-select">
                                        <option value="">Select a product…</option>
                                        <template x-for="p in products" :key="p.id">
                                            <option :value="p.id"
                                                    :selected="line.product_id == p.id"
                                                    x-text="p.name + (p.sku ? ' — ' + p.sku : '') + ' ($' + parseFloat(p.price).toFixed(2) + ')'">
                                            </option>
                                        </template>
                                    </select>
                                </div>
                                <div class="w-28 shrink-0">
                                    <label class="form-label">Qty</label>
                                    <input type="number" x-model.number="line.quantity"
                                           @input="updateLine(index)"
                                           min="1" class="form-input">
                                </div>
                                <div class="w-28 shrink-0">
                                    <label class="form-label">Unit Price</label>
                                    <p class="mt-1 text-sm font-medium text-gray-700 py-2"
                                       x-text="'$' + parseFloat(line.unit_price || 0).toFixed(2)"></p>
                                </div>
                                <div class="w-28 shrink-0">
                                    <label class="form-label">Total</label>
                                    <p class="mt-1 text-sm font-semibold text-gray-900 py-2"
                                       x-text="'$' + parseFloat(line.total || 0).toFixed(2)"></p>
                                </div>
                                <div class="shrink-0 pt-6">
                                    <button type="button" @click="removeLine(index)"
                                            x-show="lines.length > 1"
                                            class="text-red-400 hover:text-red-600 p-1">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Hidden inputs for items --}}
                    <template x-for="(line, index) in lines" :key="'input-' + index">
                        <div>
                            <input type="hidden" :name="'items[' + index + '][product_id]'" :value="line.product_id">
                            <input type="hidden" :name="'items[' + index + '][quantity]'" :value="line.quantity">
                        </div>
                    </template>
                </div>

                {{-- Totals & Notes --}}
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm space-y-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Pricing</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                                <input type="number" id="tax_rate" name="tax_rate"
                                       x-model.number="taxRate" @input="computeTotals()"
                                       value="{{ old('tax_rate', 0) }}" min="0" max="100" step="0.01"
                                       class="form-input">
                            </div>
                            <div>
                                <label for="discount_amount" class="form-label">Discount ($)</label>
                                <input type="number" id="discount_amount" name="discount_amount"
                                       x-model.number="discount" @input="computeTotals()"
                                       value="{{ old('discount_amount', 0) }}" min="0" step="0.01"
                                       class="form-input">
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span x-text="'$' + subtotal.toFixed(2)">$0.00</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Tax</span>
                                <span x-text="'$' + taxAmount.toFixed(2)">$0.00</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Discount</span>
                                <span x-text="'-$' + parseFloat(discount || 0).toFixed(2)">-$0.00</span>
                            </div>
                            <div class="flex justify-between font-bold text-gray-900 text-base border-t border-gray-200 pt-2">
                                <span>Total</span>
                                <span x-text="'$' + total.toFixed(2)">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea id="notes" name="notes" rows="6"
                                  placeholder="Internal notes, special instructions…"
                                  class="form-input">{{ old('notes') }}</textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('orders.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Place Order</button>
                </div>

            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function orderForm(products) {
            return {
                products: products,
                lines: [{ product_id: '', quantity: 1, unit_price: 0, total: 0 }],
                taxRate: {{ old('tax_rate', 0) }},
                discount: {{ old('discount_amount', 0) }},
                subtotal: 0,
                taxAmount: 0,
                total: 0,

                addLine() {
                    this.lines.push({ product_id: '', quantity: 1, unit_price: 0, total: 0 });
                },

                removeLine(index) {
                    this.lines.splice(index, 1);
                    this.computeTotals();
                },

                onProductChange(index) {
                    const line = this.lines[index];
                    const product = this.products.find(p => p.id == line.product_id);
                    line.unit_price = product ? parseFloat(product.price) : 0;
                    line.total = line.unit_price * (line.quantity || 1);
                    this.computeTotals();
                },

                updateLine(index) {
                    const line = this.lines[index];
                    line.total = (line.unit_price || 0) * (line.quantity || 0);
                    this.computeTotals();
                },

                computeTotals() {
                    this.subtotal = this.lines.reduce((sum, l) => sum + (l.total || 0), 0);
                    this.taxAmount = this.subtotal * (this.taxRate || 0) / 100;
                    this.total = Math.max(0, this.subtotal + this.taxAmount - (this.discount || 0));
                },

                prepareSubmit() {
                    // Alpine already binds hidden inputs — nothing extra needed
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
