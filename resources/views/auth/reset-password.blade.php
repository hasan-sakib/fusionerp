<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Set a new password</h2>
        <p class="mt-1 text-sm text-gray-500">
            Choose a strong password — min 8 characters, upper & lower case, numbers and symbols.
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email', $request->email) }}"
                   class="form-input @error('email') border-red-400 @enderror"
                   required autofocus autocomplete="username" />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="form-label">New password</label>
            <input id="password" type="password" name="password"
                   class="form-input @error('password') border-red-400 @enderror"
                   required autocomplete="new-password"
                   placeholder="••••••••" />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   class="form-input"
                   required autocomplete="new-password"
                   placeholder="••••••••" />
            @error('password_confirmation')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full btn-primary justify-center py-2.5 text-sm font-semibold">
            Reset password
        </button>
    </form>
</x-guest-layout>
