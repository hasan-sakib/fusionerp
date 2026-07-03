<section x-data="{ open: false }">
    <h2 class="text-base font-semibold text-red-700">Delete Account</h2>
    <p class="mt-1 mb-4 text-sm text-gray-500">
        Permanently delete your account and all associated data. This action cannot be undone.
    </p>

    <button type="button" @click="open = true" class="btn-danger btn-sm">
        Delete my account
    </button>

    {{-- Confirmation modal --}}
    <div x-show="open" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
         style="display:none">
        <div @click.outside="open = false"
             class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-bold text-gray-900">Are you absolutely sure?</h3>
            <p class="mt-2 text-sm text-gray-500">
                Your account will be permanently deleted. Please enter your password to confirm.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-5 space-y-4">
                @csrf
                @method('delete')

                <div>
                    <label for="delete_password" class="form-label">Password</label>
                    <input id="delete_password" name="password" type="password"
                           class="form-input @error('password', 'userDeletion') border-red-400 @enderror"
                           placeholder="Enter your current password" />
                    @error('password', 'userDeletion')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false" class="btn-secondary btn-sm">
                        Cancel
                    </button>
                    <button type="submit" class="btn-danger btn-sm">
                        Yes, delete account
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
