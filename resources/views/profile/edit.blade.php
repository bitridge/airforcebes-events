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

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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

                <!-- Organization -->
                <div>
                    <label for="organization" class="block text-sm font-medium text-gray-700">Organization</label>
                    <input type="text" 
                           id="organization" 
                           name="organization" 
                           value="{{ old('organization', $user->organization) }}" 
                           placeholder="Your company or organization"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('organization')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
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
