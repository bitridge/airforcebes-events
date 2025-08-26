<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Join AirforceBES Events to register for exciting events and stay connected with the community.') }}
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        <!-- Personal Information Section -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- First Name -->
                <div>
                    <x-input-label for="first_name" :value="__('First Name')" />
                    <x-text-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name')" required autofocus autocomplete="given-name" placeholder="Enter your first name" />
                    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                </div>

                <!-- Last Name -->
                <div>
                    <x-input-label for="last_name" :value="__('Last Name')" />
                    <x-text-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name')" required autocomplete="family-name" placeholder="Enter your last name" />
                    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                </div>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email Address')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="Enter your email address" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <!-- Phone Number -->
                <div>
                    <x-input-label for="phone" :value="__('Phone Number')" />
                    <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" autocomplete="tel" placeholder="+1 (555) 123-4567" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">Optional - for event notifications</p>
                </div>

                <!-- Organization Name -->
                <div class="md:col-span-2">
                    <x-input-label for="organization_name" :value="__('Organization Name')" />
                    <x-text-input id="organization_name" class="block mt-1 w-full" type="text" name="organization_name" :value="old('organization_name')" required autocomplete="organization" placeholder="Your organization or company" />
                    <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
                </div>

                <!-- Job Title -->
                <div class="md:col-span-2">
                    <x-input-label for="title" :value="__('Job Title')" />
                    <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" autocomplete="organization-title" placeholder="Your job title or position" />
                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">Optional</p>
                </div>
            </div>
        </div>

        <!-- Business Information Section -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Business Information</h4>
            <div class="space-y-4">
                <!-- NAICS Codes -->
                <div>
                    <x-input-label for="naics_codes" :value="__('NAICS Codes')" />
                    <textarea id="naics_codes" name="naics_codes" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter NAICS codes for your business sector">{{ old('naics_codes') }}</textarea>
                    <x-input-error :messages="$errors->get('naics_codes')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">Optional - North American Industry Classification System codes</p>
                </div>

                <!-- Industry Connections -->
                <div>
                    <x-input-label for="industry_connections" :value="__('Industry Connections')" />
                    <textarea id="industry_connections" name="industry_connections" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Describe your industry connections and partnerships">{{ old('industry_connections') }}</textarea>
                    <x-input-error :messages="$errors->get('industry_connections')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">Optional - Describe your industry network</p>
                </div>

                <!-- Core Specialty Area -->
                <div>
                    <x-input-label for="core_specialty_area" :value="__('Core Specialty Area')" />
                    <textarea id="core_specialty_area" name="core_specialty_area" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Describe your core specialty area or expertise">{{ old('core_specialty_area') }}</textarea>
                    <x-input-error :messages="$errors->get('core_specialty_area')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">Optional - Your main area of expertise</p>
                </div>

                <!-- Contract Vehicles -->
                <div>
                    <x-input-label for="contract_vehicles" :value="__('Contract Vehicles')" />
                    <textarea id="contract_vehicles" name="contract_vehicles" rows="2" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="List your contract vehicles or government contracts">{{ old('contract_vehicles') }}</textarea>
                    <x-input-error :messages="$errors->get('contract_vehicles')" class="mt-2" />
                    <p class="mt-1 text-xs text-gray-500">Optional - Government or commercial contracts</p>
                </div>
            </div>
        </div>

        <!-- Preferences Section -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Preferences</h4>
            <div>
                <x-input-label for="meeting_preference" :value="__('Meeting Preference')" />
                <x-text-input id="meeting_preference" name="meeting_preference" type="text" class="block mt-1 w-full" placeholder="e.g., In-person, Virtual, Hybrid, Morning preference, etc." value="{{ old('meeting_preference') }}" />
                <x-input-error :messages="$errors->get('meeting_preference')" class="mt-2" />
                <p class="mt-1 text-xs text-gray-500">How you prefer to attend events</p>
            </div>
        </div>

        <!-- Event Participation Section -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Event Participation</h4>
            <div class="space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-2 block">Small Business Forum: Increasing the Defense Industrial Base</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input id="small_business_forum_yes" name="small_business_forum" type="radio" value="Yes (In-person)" {{ old('small_business_forum') == 'Yes (In-person)' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 focus:ring-2">
                            <label for="small_business_forum_yes" class="ml-3 text-sm text-gray-700">Yes (In-person)</label>
                        </div>
                        <div class="flex items-center">
                            <input id="small_business_forum_no" name="small_business_forum" type="radio" value="No" {{ old('small_business_forum') == 'No' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 focus:ring-2">
                            <label for="small_business_forum_no" class="ml-3 text-sm text-gray-700">No</label>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-2 block">Small Business Matchmaker</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input id="small_business_matchmaker_yes" name="small_business_matchmaker" type="radio" value="Yes (In-person)" {{ old('small_business_matchmaker') == 'Yes (In-person)' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 focus:ring-2">
                            <label for="small_business_matchmaker_yes" class="ml-3 text-sm text-gray-700">Yes (In-person)</label>
                        </div>
                        <div class="flex items-center">
                            <input id="small_business_matchmaker_no" name="small_business_matchmaker" type="radio" value="No" {{ old('small_business_matchmaker') == 'No' ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 focus:ring-2">
                            <label for="small_business_matchmaker_no" class="ml-3 text-sm text-gray-700">No</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Selection -->
        <div>
            <x-input-label for="role" :value="__('Account Type')" />
            <div class="mt-2 space-y-3">
                <div class="flex items-center">
                    <input id="role_attendee" name="role" type="radio" value="attendee" 
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300" 
                           {{ old('role', 'attendee') === 'attendee' ? 'checked' : '' }} required checked>
                    <label for="role_attendee" class="ml-3">
                        <span class="block text-sm font-medium text-gray-700">Attendee</span>
                        <span class="block text-sm text-gray-500">Register for events and manage your registrations</span>
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
