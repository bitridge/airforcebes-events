<x-app-layout>
    <x-slot name="title">Edit Registration - {{ $registration->registration_code }} - {{ config('app.name') }}</x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Registration</h1>
                    <p class="text-gray-600">Code: {{ $registration->registration_code }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.registrations.show', $registration) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Details
                    </a>
                    <a href="{{ route('admin.registrations.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        All Registrations
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
                <form method="POST" action="{{ route('admin.registrations.update', $registration) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Registration Status</label>
                            <select name="status" id="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ old('status', $registration->status) === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="registration_date" class="block text-sm font-medium text-gray-700">Registration Date</label>
                            <input type="datetime-local" name="registration_date" id="registration_date" 
                                   value="{{ old('registration_date', $registration->registration_date->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            @error('registration_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="4"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                                  placeholder="Add any notes about this registration...">{{ old('notes', $registration->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Registration Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Registration Code</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $registration->registration_code }}</p>
                                <p class="mt-1 text-xs text-gray-500">This cannot be changed</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Event</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $registration->event->title }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $registration->event->start_date->format('M j, Y') }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Attendee</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $registration->user->name }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $registration->user->email }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Created</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $registration->created_at->format('M j, Y g:i A') }}</p>
                                <p class="mt-1 text-xs text-gray-500">Last updated: {{ $registration->updated_at->format('M j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-6">
                        <a href="{{ route('admin.registrations.show', $registration) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Registration
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="bg-white rounded-lg shadow p-6 mt-6 border border-red-200">
                <h3 class="text-lg font-medium text-red-900 mb-4">Danger Zone</h3>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600">Once you delete a registration, there is no going back.</p>
                        <p class="text-sm text-gray-500 mt-1">This will permanently remove the registration record.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.registrations.destroy', $registration) }}" class="inline" 
                          onsubmit="return confirm('Are you sure you want to delete this registration? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Registration
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
