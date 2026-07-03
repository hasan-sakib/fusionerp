<x-app-layout>
    @section('title', 'My Profile')
    @section('page-title', 'My Profile')

    <div class="max-w-3xl space-y-6">

        {{-- Profile info card --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- Password card --}}
        <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
            @include('profile.partials.update-password-form')
        </div>

        {{-- Delete account card --}}
        <div class="rounded-xl border border-red-100 bg-white p-6 shadow-sm">
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>
