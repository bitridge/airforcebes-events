<section>
    <form method="post" action="{{ route('profile.password') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="current_password" :value="__('Current Password')" />
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" placeholder="Enter your current password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="password" :value="__('New Password')" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" placeholder="Enter new password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Must be at least 8 characters') }}</p>
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm New Password')" />
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" placeholder="Confirm new password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
            <x-primary-button>{{ __('Update Password') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="text-sm text-green-600 font-medium"
                >{{ __('Password updated successfully!') }}</p>
            @endif
        </div>
    </form>
</section>
