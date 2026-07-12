<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Create an account</h2>
        <p class="mt-1 text-sm text-gray-500">Join FusionERP to manage your business operations</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Company Name --}}
        <div>
            <label for="company_name" class="form-label">Company name</label>
            <input id="company_name" type="text" name="company_name"
                   value="{{ old('company_name') }}"
                   class="form-input @error('company_name') border-red-400 @enderror"
                   required autofocus autocomplete="organization"
                   placeholder="Acme Corp" />
            @error('company_name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Name --}}
        <div>
            <label for="name" class="form-label">Full name</label>
            <input id="name" type="text" name="name"
                   value="{{ old('name') }}"
                   class="form-input @error('name') border-red-400 @enderror"
                   required autofocus autocomplete="name"
                   placeholder="John Smith" />
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}"
                   class="form-input @error('email') border-red-400 @enderror"
                   required autocomplete="username"
                   placeholder="you@company.com" />
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password"
                   class="form-input @error('password') border-red-400 @enderror"
                   required autocomplete="new-password"
                   placeholder="Min 8 chars, upper, lower, number, symbol" />
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="form-label">Confirm password</label>
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
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
            Sign in
        </a>
    </p>
</x-guest-layout>
