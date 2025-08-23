<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Welcome back! Please sign in to your AirforceBES Events account.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Enter your email address" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" 
                            placeholder="Enter your password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" name="remember">
                <span class="ml-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150" href="{{ route('password.request') }}">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <div>
            <x-primary-button class="w-full justify-center">
                {{ __('Sign In') }}
            </x-primary-button>
        </div>

        <div class="text-center">
            <span class="text-sm text-gray-600">{{ __("Don't have an account?") }}</span>
            <a href="{{ route('register') }}" class="text-sm text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                {{ __('Create one now') }}
            </a>
        </div>
    </form>
</x-guest-layout>
