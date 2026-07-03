<x-app-layout>
    @section('page-title', 'Create Role')

    <div class="max-w-3xl">

        {{-- Breadcrumb --}}
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('roles.index') }}" class="hover:text-gray-700 transition-colors">Roles</a>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="font-medium text-gray-900">Create Role</span>
        </nav>

        <form method="POST" action="{{ route('roles.store') }}" class="space-y-4">
            @csrf

            {{-- Role Name --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Role Details</h3>
                <div class="max-w-sm">
                    <label for="name" class="form-label">Role Name <span class="text-red-500">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}"
                           class="form-input" placeholder="e.g. support, accountant" required autofocus>
                    <p class="mt-1 text-xs text-gray-400">Lowercase letters, numbers, hyphens, and underscores only.</p>
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Permissions --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Permissions</h3>
                @error('permissions') <p class="form-error mb-3">{{ $message }}</p> @enderror

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($permissions as $group => $groupPermissions)
                        <div x-data="{
                                toggleAll() {
                                    const cbs = this.$el.querySelectorAll('[name=\'permissions[]\']');
                                    const allOn = [...cbs].every(c => c.checked);
                                    cbs.forEach(c => c.checked = !allOn);
                                }
                             }"
                             class="rounded-lg border border-gray-200 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <h4 class="text-sm font-semibold capitalize text-gray-800">{{ $group }}</h4>
                                <button type="button" @click="toggleAll()"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 transition-colors">
                                    Toggle all
                                </button>
                            </div>
                            <div class="space-y-2">
                                @foreach($groupPermissions as $permission)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="permissions[]"
                                               value="{{ $permission->name }}"
                                               {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm capitalize text-gray-700">
                                            {{ str_replace('_', ' ', \Illuminate\Support\Str::after($permission->name, '.')) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('roles.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Role</button>
            </div>
        </form>
    </div>
</x-app-layout>
