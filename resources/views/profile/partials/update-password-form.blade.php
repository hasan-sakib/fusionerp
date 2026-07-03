<section>
    <h2 class="text-base font-semibold text-gray-900">Update Password</h2>
    <p class="mt-1 mb-4 text-sm text-gray-500">
        Use a strong password of at least 8 characters with upper & lower case, numbers, and symbols.
    </p>

    <form method="post" action="{{ route('password.update') }}" class="space-y-4">
        @csrf
        @method('put')

        <div>
            <label for="current_password" class="form-label">Current password</label>
            <input id="current_password" name="current_password" type="password"
                   class="form-input @error('current_password', 'updatePassword') border-red-400 @enderror"
                   autocomplete="current-password" />
            @error('current_password', 'updatePassword')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="new_password" class="form-label">New password</label>
            <input id="new_password" name="password" type="password"
                   class="form-input @error('password', 'updatePassword') border-red-400 @enderror"
                   autocomplete="new-password" />
            @error('password', 'updatePassword')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="form-input"
                   autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="btn-primary">Update password</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 3000)"
                   class="text-sm text-green-600 font-medium">
                    Password updated.
                </p>
            @endif
        </div>
    </form>
</section>
