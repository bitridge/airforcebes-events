<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Set New Password') }}</h2>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('Please enter your new password. Make sure it\'s secure and easy for you to remember.') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full bg-gray-50" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" readonly />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('New Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="Enter new password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                    type="password"
                                    name="password_confirmation" required autocomplete="new-password" 
                                    placeholder="Confirm new password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-primary-button class="w-full justify-center">
                {{ __('Reset Password') }}
            </x-primary-button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                {{ __('← Back to login') }}
            </a>
        </div>
    </form>
</x-guest-layout>
