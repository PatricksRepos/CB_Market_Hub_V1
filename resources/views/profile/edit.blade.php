<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-6 sm:p-8 bg-white shadow-sm rounded-xl border text-center">
                <x-user-avatar :user="auth()->user()" size="mx-auto h-24 w-24" class="border-gray-300" />
                <h3 class="mt-4 text-xl font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                @if(auth()->user()->username)
                    <p class="text-sm text-gray-500">@{{ auth()->user()->username }}</p>
                @endif
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm rounded-xl border">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow-sm rounded-xl border">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
