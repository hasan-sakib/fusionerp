<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Sign in to your account</h2>
        <p class="mt-1 text-sm text-gray-500">Enter your credentials to access FusionERP</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}"
                   class="form-input @error('email') border-red-400 focus:border-red-500 @enderror"
                   required autofocus autocomplete="username"
                   placeholder="you@company.com" />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="form-label mb-0">Password</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs font-medium text-indigo-600 hover:text-indigo-500">
                        Forgot password?
                    </a>
                @endif
            </div>
            <input id="password" type="password" name="password"
                   class="form-input @error('password') border-red-400 @enderror"
                   required autocomplete="current-password"
                   placeholder="••••••••" />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember"
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="remember_me" class="text-sm text-gray-600">Keep me signed in</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full btn-primary justify-center py-2.5 text-sm font-semibold">
            Sign in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
            Create one
        </a>
    </p>
</x-guest-layout>
