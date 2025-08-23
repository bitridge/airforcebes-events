<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('Full Name')" />
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" placeholder="Enter your full name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Email -->
            <div>
                <x-input-label for="email" :value="__('Email Address')" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" placeholder="Enter your email address" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-sm text-yellow-600">
                            {{ __('Your email address is unverified.') }}
                            <button form="send-verification" class="underline text-sm text-yellow-700 hover:text-yellow-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Phone -->
            <div>
                <x-input-label for="phone" :value="__('Phone Number')" />
                <x-text-input id="phone" name="phone" type="tel" class="mt-1 block w-full" :value="old('phone', $user->phone)" autocomplete="tel" placeholder="+1 (555) 123-4567" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Optional - for event notifications') }}</p>
            </div>

            <!-- Organization -->
            <div>
                <x-input-label for="organization" :value="__('Organization')" />
                <x-text-input id="organization" name="organization" type="text" class="mt-1 block w-full" :value="old('organization', $user->organization)" autocomplete="organization" placeholder="Your organization or company" />
                <x-input-error class="mt-2" :messages="$errors->get('organization')" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Optional') }}</p>
            </div>
        </div>

        <!-- User Role Display -->
        <div>
            <x-input-label for="role_display" :value="__('Account Type')" />
            <div class="mt-1 flex items-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                    @if($user->isAdmin()) bg-blue-100 text-blue-800 @else bg-green-100 text-green-800 @endif">
                    {{ $user->getRoleDisplayName() }}
                </span>
                <span class="ml-2 text-sm text-gray-500">
                    @if($user->isAdmin())
                        {{ __('You have administrative privileges') }}
                    @else
                        {{ __('Standard user account') }}
                    @endif
                </span>
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
            <x-primary-button>{{ __('Save Changes') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600 font-medium"
                >{{ __('Profile updated successfully!') }}</p>
            @endif
        </div>
    </form>
</section>
