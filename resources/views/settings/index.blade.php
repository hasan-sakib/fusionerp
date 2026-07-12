<x-app-layout>
    @section('page-title', 'Settings')

    <div x-data="{ tab: '{{ old('_tab', 'general') }}' }">

        {{-- Tab navigation --}}
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex gap-6">
                <button type="button" @click="tab = 'general'"
                        :class="tab === 'general' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="border-b-2 pb-3 text-sm font-medium transition-colors">
                    General
                </button>
                <button type="button" @click="tab = 'orders'"
                        :class="tab === 'orders' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="border-b-2 pb-3 text-sm font-medium transition-colors">
                    Orders
                </button>
                <button type="button" @click="tab = 'preferences'"
                        :class="tab === 'preferences' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="border-b-2 pb-3 text-sm font-medium transition-colors">
                    Preferences
                </button>
            </nav>
        </div>

        @can('settings.edit')
        <form method="POST" action="{{ route('settings.update') }}">
            @csrf
            @method('PATCH')
            {{-- Remember which tab was active on validation error --}}
            <input type="hidden" name="_tab" x-model="tab">
        @endcan

            {{-- ── General ──────────────────────────────────────────────────────── --}}
            <div x-show="tab === 'general'" x-cloak>
                <div class="rounded-xl border border-gray-100 bg-white shadow-sm divide-y divide-gray-100">

                    {{-- Company info --}}
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Company Information</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <x-input-label for="company_name" value="Company Name *" />
                                <x-text-input id="company_name" name="company_name" type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('company_name', $settings['company_name']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <x-input-error :messages="$errors->get('company_name')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="company_email" value="Company Email" />
                                <x-text-input id="company_email" name="company_email" type="email"
                                    class="mt-1 block w-full"
                                    value="{{ old('company_email', $settings['company_email']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <x-input-error :messages="$errors->get('company_email')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="company_phone" value="Company Phone" />
                                <x-text-input id="company_phone" name="company_phone" type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('company_phone', $settings['company_phone']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <x-input-error :messages="$errors->get('company_phone')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="company_website" value="Website" />
                                <x-text-input id="company_website" name="company_website" type="url"
                                    class="mt-1 block w-full"
                                    placeholder="https://example.com"
                                    value="{{ old('company_website', $settings['company_website']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <x-input-error :messages="$errors->get('company_website')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="company_address" value="Address" />
                                <textarea id="company_address" name="company_address" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }}>{{ old('company_address', $settings['company_address']) }}</textarea>
                                <x-input-error :messages="$errors->get('company_address')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    {{-- Localisation --}}
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Localisation</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <x-input-label for="timezone" value="Timezone *" />
                                <select id="timezone" name="timezone"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }}>
                                    @foreach($timezones as $tz)
                                        <option value="{{ $tz }}" {{ old('timezone', $settings['timezone']) === $tz ? 'selected' : '' }}>
                                            {{ $tz }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('timezone')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="currency" value="Currency Code *" />
                                <x-text-input id="currency" name="currency" type="text"
                                    class="mt-1 block w-full"
                                    placeholder="USD"
                                    maxlength="10"
                                    value="{{ old('currency', $settings['currency']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <x-input-error :messages="$errors->get('currency')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="currency_symbol" value="Currency Symbol *" />
                                <x-text-input id="currency_symbol" name="currency_symbol" type="text"
                                    class="mt-1 block w-full"
                                    placeholder="$"
                                    maxlength="5"
                                    value="{{ old('currency_symbol', $settings['currency_symbol']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <x-input-error :messages="$errors->get('currency_symbol')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Orders ───────────────────────────────────────────────────────── --}}
            <div x-show="tab === 'orders'" x-cloak>
                <div class="rounded-xl border border-gray-100 bg-white shadow-sm divide-y divide-gray-100">

                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Order Defaults</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <x-input-label for="order_prefix" value="Order Number Prefix *" />
                                <x-text-input id="order_prefix" name="order_prefix" type="text"
                                    class="mt-1 block w-full"
                                    placeholder="ORD-"
                                    maxlength="20"
                                    value="{{ old('order_prefix', $settings['order_prefix']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <p class="mt-1 text-xs text-gray-400">Prepended to order numbers, e.g. ORD-0001</p>
                                <x-input-error :messages="$errors->get('order_prefix')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="default_tax_rate" value="Default Tax Rate (%)" />
                                <x-text-input id="default_tax_rate" name="default_tax_rate" type="number"
                                    class="mt-1 block w-full"
                                    min="0" max="100" step="0.01"
                                    value="{{ old('default_tax_rate', $settings['default_tax_rate']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <p class="mt-1 text-xs text-gray-400">Applied when creating new orders</p>
                                <x-input-error :messages="$errors->get('default_tax_rate')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="low_stock_threshold" value="Low Stock Threshold *" />
                                <x-text-input id="low_stock_threshold" name="low_stock_threshold" type="number"
                                    class="mt-1 block w-full"
                                    min="0" max="10000" step="1"
                                    value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}"
                                    {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }} />
                                <p class="mt-1 text-xs text-gray-400">Products below this quantity are flagged as low stock</p>
                                <x-input-error :messages="$errors->get('low_stock_threshold')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Preferences ──────────────────────────────────────────────────── --}}
            <div x-show="tab === 'preferences'" x-cloak>
                <div class="rounded-xl border border-gray-100 bg-white shadow-sm divide-y divide-gray-100">

                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Display Preferences</h3>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                            <div>
                                <x-input-label for="date_format" value="Date Format *" />
                                <select id="date_format" name="date_format"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }}>
                                    @foreach(['Y-m-d' => '2026-07-12 (ISO)', 'd/m/Y' => '12/07/2026', 'm/d/Y' => '07/12/2026', 'd-m-Y' => '12-07-2026'] as $fmt => $label)
                                        <option value="{{ $fmt }}" {{ old('date_format', $settings['date_format']) === $fmt ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('date_format')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="items_per_page" value="Items Per Page *" />
                                <select id="items_per_page" name="items_per_page"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }}>
                                    @foreach([10, 15, 25, 50] as $n)
                                        <option value="{{ $n }}" {{ (int) old('items_per_page', $settings['items_per_page']) === $n ? 'selected' : '' }}>
                                            {{ $n }} per page
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('items_per_page')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="fiscal_year_start" value="Fiscal Year Start *" />
                                <select id="fiscal_year_start" name="fiscal_year_start"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        {{ !auth()->user()->can('settings.edit') ? 'disabled' : '' }}>
                                    @foreach(['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'] as $num => $month)
                                        <option value="{{ $num }}" {{ old('fiscal_year_start', $settings['fiscal_year_start']) === $num ? 'selected' : '' }}>
                                            {{ $month }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('fiscal_year_start')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Save button --}}
            @can('settings.edit')
            <div class="mt-6 flex justify-end">
                <button type="submit" class="btn-primary">
                    Save Settings
                </button>
            </div>
            @endcan

        @can('settings.edit')
        </form>
        @endcan

    </div>
</x-app-layout>
