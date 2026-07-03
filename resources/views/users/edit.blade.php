<x-app-layout>
    @section('page-title', 'Edit User')

    <div class="max-w-2xl">

        {{-- Breadcrumb --}}
        <nav class="mb-5 flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('users.index') }}" class="hover:text-gray-700 transition-colors">Users</a>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <a href="{{ route('users.show', $user) }}" class="hover:text-gray-700 transition-colors">{{ $user->name }}</a>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
            <span class="font-medium text-gray-900">Edit</span>
        </nav>

        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf
            @method('PATCH')

            {{-- Personal Information --}}
            <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-500">Personal Information</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="name" class="form-label">Full Name <span class="text-red-500">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                               class="form-input" required autofocus>
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="email" class="form-label">Email Address <span class="text-red-500">*</span></label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                               class="form-input" required>
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        @if(! $user->email_verified_at)
                            <p class="mt-1 text-xs text-yellow-600">Email address is not verified.</p>
                        @endif
                    </div>

                    <div>
                        <label for="phone" class="form-label">Phone</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="form-input">
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
                        <input id="department" type="text" name="department" value="{{ old('department', $user->department) }}"
                               class="form-input">
                        @error('department') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="position" class="form-label">Position</label>
                        <input id="position" type="text" name="position" value="{{ old('position', $user->position) }}"
                               class="form-input">
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
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}"
                                        {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="form-label">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="active"    {{ old('status', $user->status) === 'active'    ? 'selected' : '' }}>Active</option>
                            <option value="inactive"  {{ old('status', $user->status) === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                            <option value="suspended" {{ old('status', $user->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        </select>
                        @error('status') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between">
                <a href="{{ route('users.show', $user) }}" class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                    ← Back to profile
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('users.show', $user) }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
