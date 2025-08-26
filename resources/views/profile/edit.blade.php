@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">Edit Profile</h1>
            <a href="{{ route('profile.show') }}" class="text-indigo-600 hover:text-indigo-800">
                ← Back to Profile
            </a>
        </div>

        <!-- Profile Information Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Profile Information</h2>
            
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- Profile Picture -->
                <div class="flex items-center space-x-6">
                    <div class="flex-shrink-0">
                        @if($user->profile_picture)
                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                 alt="Profile Picture" 
                                 class="h-24 w-24 rounded-full object-cover">
                        @else
                            <div class="h-24 w-24 bg-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-2xl font-medium text-white">{{ $user->initials }}</span>
                            </div>
                        @endif
                    </div>
                    <div>
                        <label for="profile_picture" class="block text-sm font-medium text-gray-700">
                            Profile Picture
                        </label>
                        <input type="file" 
                               id="profile_picture" 
                               name="profile_picture" 
                               accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                    </div>
                </div>

                <!-- First Name and Last Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="{{ old('first_name', $user->first_name) }}" 
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               value="{{ old('last_name', $user->last_name) }}" 
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:border-indigo-500">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           value="{{ old('phone', $user->phone) }}" 
                           placeholder="(555) 123-4567"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- First Name and Last Name -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" 
                               id="first_name" 
                               name="first_name" 
                               value="{{ old('first_name', $user->first_name) }}" 
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" 
                               id="last_name" 
                               name="last_name" 
                               value="{{ old('last_name', $user->last_name) }}" 
                               required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Organization Name and Job Title -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="organization_name" class="block text-sm font-medium text-gray-700">Organization Name</label>
                        <input type="text" 
                               id="organization_name" 
                               name="organization_name" 
                               value="{{ old('organization_name', $user->organization_name) }}" 
                               placeholder="Your company or organization"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('organization_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Job Title</label>
                        <input type="text" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $user->title) }}" 
                               placeholder="Your job title or position"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Business Information -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Business Information</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="naics_codes" class="block text-sm font-medium text-gray-700">NAICS Codes</label>
                            <input type="text" 
                                   id="naics_codes" 
                                   name="naics_codes" 
                                   value="{{ old('naics_codes', $user->naics_codes) }}" 
                                   placeholder="e.g., 541511, 541512"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('naics_codes')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="industry_connections" class="block text-sm font-medium text-gray-700">Industry Connections</label>
                            <input type="text" 
                                   id="industry_connections" 
                                   name="industry_connections" 
                                   value="{{ old('industry_connections', $user->industry_connections) }}" 
                                   placeholder="e.g., Technology, Defense, Healthcare"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('industry_connections')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="core_specialty_area" class="block text-sm font-medium text-gray-700">Core Specialty Area</label>
                            <input type="text" 
                                   id="core_specialty_area" 
                                   name="core_specialty_area" 
                                   value="{{ old('core_specialty_area', $user->core_specialty_area) }}" 
                                   placeholder="e.g., Software Development, Cybersecurity"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('core_specialty_area')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contract_vehicles" class="block text-sm font-medium text-gray-700">Contract Vehicles</label>
                            <input type="text" 
                                   id="contract_vehicles" 
                                   name="contract_vehicles" 
                                   value="{{ old('contract_vehicles', $user->contract_vehicles) }}" 
                                   placeholder="e.g., GSA MAS, IDIQ, 8(a)"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @error('contract_vehicles')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Preferences -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Preferences</h3>
                    
                    <div>
                        <label for="meeting_preference" class="block text-sm font-medium text-gray-700">Meeting Preference</label>
                        <select id="meeting_preference" 
                                name="meeting_preference" 
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="no_preference" {{ old('meeting_preference', $user->meeting_preference) == 'no_preference' ? 'selected' : '' }}>No Preference</option>
                            <option value="in_person" {{ old('meeting_preference', $user->meeting_preference) == 'in_person' ? 'selected' : '' }}>In Person</option>
                            <option value="virtual" {{ old('meeting_preference', $user->meeting_preference) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                            <option value="hybrid" {{ old('meeting_preference', $user->meeting_preference) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                            <option value="prefer_morning" {{ old('meeting_preference', $user->meeting_preference) == 'prefer_morning' ? 'selected' : '' }}>Prefer Morning</option>
                            <option value="prefer_afternoon" {{ old('meeting_preference', $user->meeting_preference) == 'prefer_afternoon' ? 'selected' : '' }}>Prefer Afternoon</option>
                            <option value="prefer_evening" {{ old('meeting_preference', $user->meeting_preference) == 'prefer_evening' ? 'selected' : '' }}>Prefer Evening</option>
                        </select>
                        @error('meeting_preference')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Event Participation -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Event Participation</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="small_business_forum" 
                                   name="small_business_forum" 
                                   value="1" 
                                   {{ old('small_business_forum', $user->small_business_forum) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="small_business_forum" class="ml-2 block text-sm text-gray-900">
                                Small Business Forum: Increasing the Defense Industrial Base
                            </label>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="small_business_matchmaker" 
                                   name="small_business_matchmaker" 
                                   value="1" 
                                   {{ old('small_business_matchmaker', $user->small_business_matchmaker) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="small_business_matchmaker" class="ml-2 block text-sm text-gray-900">
                                Small Business Matchmaker
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Bio -->
                <div>
                    <label for="bio" class="block text-sm font-medium text-gray-700">Bio</label>
                    <textarea id="bio" 
                              name="bio" 
                              rows="4" 
                              placeholder="Tell us about yourself..."
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200">
                        Update Profile
                    </button>
                </div>
            </form>
            </div>

        <!-- Change Password Form -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Change Password</h2>
            
            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                @method('PATCH')

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                    <input type="password" 
                           id="current_password" 
                           name="current_password" 
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
            </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                    <input type="password" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200">
                        Change Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Delete Account Section -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Delete Account</h2>
            <p class="text-sm text-gray-600 mb-4">
                Once your account is deleted, all of its resources and data will be permanently deleted. 
                Before deleting your account, please download any data or information that you wish to retain.
            </p>
            
            <form method="POST" action="{{ route('profile.destroy') }}" 
                  onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                
                <div class="flex justify-end">
                    <button type="submit" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md font-medium transition-colors duration-200">
                        Delete Account
                    </button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection
