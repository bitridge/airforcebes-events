<x-app-layout>
    <x-slot name="title">Edit Attendee - {{ $attendee->name }} - {{ config('app.name') }}</x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Attendee Profile</h1>
                    <p class="text-gray-600">{{ $attendee->name }} ({{ $attendee->email }})</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.attendees.show', $attendee) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Profile
                    </a>
                    <a href="{{ route('admin.attendees.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        All Attendees
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Edit Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <form method="POST" action="{{ route('admin.attendees.update', $attendee) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $attendee->name) }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $attendee->email) }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone', $attendee->phone) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                   placeholder="+1 (555) 123-4567">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="organization" class="block text-sm font-medium text-gray-700">Organization</label>
                            <input type="text" name="organization" id="organization" value="{{ old('organization', $attendee->organization) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                   placeholder="Company or Organization">
                            @error('organization')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role" id="role" disabled
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100">
                                <option value="attendee" {{ $attendee->role === 'attendee' ? 'selected' : '' }}>Attendee</option>
                                <option value="admin" {{ $attendee->role === 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Role cannot be changed from this interface</p>
                        </div>

                        <div>
                            <label for="is_active" class="block text-sm font-medium text-gray-700">Account Status</label>
                            <div class="mt-1">
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="is_active" value="1" 
                                           {{ old('is_active', $attendee->is_active) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-900">Active Account</span>
                                </label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Uncheck to deactivate this account</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Member Since</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $attendee->created_at->format('M j, Y g:i A') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $attendee->updated_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <a href="{{ route('admin.attendees.show', $attendee) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-lg shadow p-6 mt-6 border border-red-200">
                <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600">Once you delete an attendee account, there is no going back.</p>
                        <p class="text-sm text-gray-500 mt-1">This will permanently remove all their data and registrations.</p>
                    </div>
                    <button type="button" 
                            onclick="if(confirm('Are you sure you want to delete this attendee? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Attendee
                    </button>
                </div>
                
                <form id="delete-form" method="POST" action="{{ route('admin.attendees.destroy', $attendee) }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
