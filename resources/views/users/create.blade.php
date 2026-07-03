<x-app-layout>
    @section('page-title', 'Create User')

    <div class="max-w-2xl">

        {{-- Breadcrumb --}}
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('users.index') }}" class="hover:text-gray-700 transition-colors">Users</a>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="font-medium text-gray-900">Create User</span>
        </nav>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            {{-- Personal Information --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Personal Information</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}"
                               class="form-input" placeholder="Jane Doe" required autofocus>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               class="form-input" placeholder="jane@example.com" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="phone" class="form-label">Phone</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                               class="form-input" placeholder="+1 555 000 0000">
                        @error('phone') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Employment --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Employment</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="department" class="form-label">Department</label>
                        <input id="department" type="text" name="department" value="{{ old('department') }}"
                               class="form-input" placeholder="Engineering">
                        @error('department') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="position" class="form-label">Position</label>
                        <input id="position" type="text" name="position" value="{{ old('position') }}"
                               class="form-input" placeholder="Software Engineer">
                        @error('position') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Access --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Access & Status</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="role" class="form-label">Role <span class="text-red-500">*</span></label>
                        <select id="role" name="role" class="form-select" required>
                            <option value="">Select a role…</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="active"    {{ old('status', 'active') === 'active'    ? 'selected' : '' }}>Active</option>
                            <option value="inactive"  {{ old('status') === 'inactive'            ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status') === 'suspended'           ? 'selected' : '' }}>Suspended</option>
                        </select>
                        @error('status') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Password --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Initial Password</h3>
                <p class="mb-4 text-xs text-gray-500">
                    Set a temporary password. The user can change it from their profile after logging in.
                </p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="form-label">Password <span class="text-red-500">*</span></label>
                        <input id="password" type="password" name="password"
                               class="form-input" autocomplete="new-password" required>
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                        <input id="password_confirmation" type="password" name="password_confirmation"
                               class="form-input" autocomplete="new-password" required>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-400">
                    Min. 8 characters with uppercase, lowercase, number, and symbol.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create User</button>
            </div>
        </form>
    </div>
</x-app-layout>
