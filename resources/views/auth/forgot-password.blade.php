<x-guest-layout>
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Reset Your Password') }}</h2>
        <p class="mt-2 text-sm text-gray-600">
            {{ __('Forgot your password? No problem. Enter your email address and we\'ll send you a link to reset it.') }}
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email Address')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="Enter your email address" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-primary-button class="w-full justify-center">
                {{ __('Send Password Reset Link') }}
            </x-primary-button>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
                {{ __('← Back to login') }}
            </a>
        </div>
    </form>
</x-guest-layout>
