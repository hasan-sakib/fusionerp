<x-admin-layout>
    <x-slot name="pageTitle">Edit — {{ $tenant->name }}</x-slot>
    <x-slot name="headerActions">
        <a href="{{ route('admin.tenants.show', $tenant->id) }}"
           class="text-sm text-gray-500 hover:text-gray-800">
            ← Back
        </a>
    </x-slot>

    <div class="mx-auto max-w-2xl">
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
                @csrf @method('PATCH')

                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 sm:text-sm"
                               required>
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Slug <span class="text-xs text-gray-400">(changing this breaks existing subdomain links)</span>
                        </label>
                        <input type="text" name="slug" value="{{ old('slug', $tenant->slug) }}"
                               pattern="[a-z0-9-]+"
                               class="mt-1 block w-full rounded-lg border-gray-300 font-mono shadow-sm focus:border-violet-500 focus:ring-violet-500 sm:text-sm"
                               required>
                        @error('slug') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="status"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 sm:text-sm">
                            @foreach (['trial', 'active', 'inactive', 'suspended'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $tenant->status) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Plan</label>
                        <input type="text" name="plan" value="{{ old('plan', $tenant->plan) }}"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-violet-500 sm:text-sm">
                        @error('plan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.tenants.show', $tenant->id) }}"
                       class="text-sm text-gray-600 hover:text-gray-900">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-admin-layout>
