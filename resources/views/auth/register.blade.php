<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Join AirforceBES Events to register for exciting events and stay connected with the community.') }}
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Enter your full name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Enter your email address" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Phone Number -->
            <div>
                <x-input-label for="phone" :value="__('Phone Number')" />
                <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" autocomplete="tel" placeholder="+1 (555) 123-4567" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">Optional - for event notifications</p>
            </div>

            <!-- Organization -->
            <div>
                <x-input-label for="organization" :value="__('Organization')" />
                <x-text-input id="organization" class="block mt-1 w-full" type="text" name="organization" :value="old('organization')" autocomplete="organization" placeholder="Your organization or company" />
                <x-input-error :messages="$errors->get('organization')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">Optional</p>
            </div>
        </div>

        <!-- Role Selection -->
        <div>
            <x-input-label for="role" :value="__('Account Type')" />
            <div class="mt-2 space-y-3">
                <div class="flex items-center">
                    <input id="role_attendee" name="role" type="radio" value="attendee" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" 
                           {{ old('role', 'attendee') === 'attendee' ? 'checked' : '' }} required>
                    <label for="role_attendee" class="ml-3">
                        <span class="block text-sm font-medium text-gray-700">Attendee</span>
                        <span class="block text-sm text-gray-500">Register for events and manage your registrations</span>
                    </label>
                </div>
                <div class="flex items-center">
                    <input id="role_admin" name="role" type="radio" value="admin" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                           {{ old('role') === 'admin' ? 'checked' : '' }} required>
                    <label for="role_admin" class="ml-3">
                        <span class="block text-sm font-medium text-gray-700">Event Administrator</span>
                        <span class="block text-sm text-gray-500">Create and manage events, view registrations and analytics</span>
                    </label>
                </div>
            </div>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" 
                                placeholder="Enter a strong password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">Must be at least 8 characters</p>
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" 
                                placeholder="Confirm your password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0 pt-4 border-t border-gray-200">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already have an account? Sign in') }}
            </a>

            <x-primary-button class="w-full sm:w-auto">
                {{ __('Create Account') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
