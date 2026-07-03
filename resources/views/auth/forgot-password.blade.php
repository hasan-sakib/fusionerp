<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Forgot your password?</h2>
        <p class="mt-1 text-sm text-gray-500">
            Enter your email and we'll send a reset link within a few minutes.
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}"
                   class="form-input @error('email') border-red-400 @enderror"
                   required autofocus
                   placeholder="you@company.com" />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full btn-primary justify-center py-2.5 text-sm font-semibold">
            Send reset link
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Remember your password?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
            Sign in
        </a>
    </p>
</x-guest-layout>
