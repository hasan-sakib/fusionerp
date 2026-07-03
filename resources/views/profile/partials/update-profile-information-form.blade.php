<section>
    <div class="flex items-start gap-6 mb-6">
        {{-- Avatar preview --}}
        <div class="shrink-0" x-data="{ preview: '{{ auth()->user()->avatar_url }}' }">
            <img :src="preview" alt="Avatar"
                 class="h-20 w-20 rounded-full object-cover ring-4 ring-gray-100">
            <input type="file" name="avatar" id="avatar_input" accept="image/*" class="hidden"
                   @change="preview = URL.createObjectURL($event.target.files[0])">
            <button type="button"
                    onclick="document.getElementById('avatar_input').click()"
                    class="mt-2 w-full text-center text-xs text-indigo-600 hover:text-indigo-500 font-medium">
                Change photo
            </button>
        </div>

        <div>
            <h2 class="text-base font-semibold text-gray-900">Profile Information</h2>
            <p class="mt-1 text-sm text-gray-500">
                Update your name, email, and contact details. Changing your email requires re-verification.
            </p>
        </div>
    </div>

    {{-- Hidden re-send verification form --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}"
          enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="form-label">Full name</label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $user->name) }}"
                       class="form-input @error('name') border-red-400 @enderror"
                       required autocomplete="name" />
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="form-label">Email address</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $user->email) }}"
                       class="form-input @error('email') border-red-400 @enderror"
                       required autocomplete="username" />
                @error('email') <p class="form-error">{{ $message }}</p> @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="mt-1 text-xs text-amber-600">
                        Email not verified.
                        <button form="send-verification"
                                class="font-medium underline hover:text-amber-700">
                            Resend link
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 text-xs text-green-600 font-medium">Verification link sent!</p>
                    @endif
                @endif
            </div>

            <div>
                <label for="phone" class="form-label">Phone number</label>
                <input id="phone" name="phone" type="text"
                       value="{{ old('phone', $user->phone) }}"
                       class="form-input @error('phone') border-red-400 @enderror"
                       placeholder="+1 (555) 000-0000" />
                @error('phone') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="department" class="form-label">Department</label>
                <input id="department" name="department" type="text"
                       value="{{ old('department', $user->department) }}"
                       class="form-input @error('department') border-red-400 @enderror"
                       placeholder="e.g. Engineering" />
                @error('department') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="position" class="form-label">Job title</label>
                <input id="position" name="position" type="text"
                       value="{{ old('position', $user->position) }}"
                       class="form-input @error('position') border-red-400 @enderror"
                       placeholder="e.g. Senior Developer" />
                @error('position') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="btn-primary">Save changes</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 3000)"
                   class="text-sm text-green-600 font-medium">
                    Saved successfully.
                </p>
            @endif
        </div>
    </form>
</section>
