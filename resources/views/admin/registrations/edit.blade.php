<x-app-layout>
    <x-slot name="title">Edit Registration - {{ $registration->registration_code }} - {{ config('app.name') }}</x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Edit Registration</h1>
                <p class="mt-2 text-sm text-gray-600">Update registration details for "{{ $registration->user->name }}" at "{{ $registration->event->title }}".</p>
            </div>

            <form method="POST" action="{{ route('admin.registrations.update', $registration) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="bg-white rounded-lg shadow p-6">
                    <!-- Registration Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Registration Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registration Code</label>
                                <input type="text" value="{{ $registration->registration_code }}" disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Registration Date</label>
                                <input type="text" value="{{ $registration->registration_date->format('M j, Y g:i A') }}" disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                            </div>
                        </div>
                    </div>

                    <!-- Status & Notes -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Status & Notes</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status *</label>
                                <select id="status" name="status" required
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
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
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea id="notes" name="notes" rows="4" maxlength="1000"
                                          placeholder="Add any notes about this registration..."
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">{{ old('notes', $registration->notes) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Max 1000 characters</p>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Read-only Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Event & Attendee Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event</label>
                                <input type="text" value="{{ $registration->event->title }}" disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Event Date</label>
                                <input type="text" value="{{ $registration->event->formatted_date_range }}" disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Attendee Name</label>
                                <input type="text" value="{{ $registration->user->name }}" disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Attendee Email</label>
                                <input type="text" value="{{ $registration->user->email }}" disabled
                                       class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                            </div>
                        </div>
                    </div>

                    <!-- Check-in Status -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Check-in Status</h3>
                        
                        @if($registration->checkIn)
                            <div class="bg-green-50 border border-green-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">Checked In</h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p>Check-in time: {{ $registration->checkIn->checked_in_at->format('M j, Y g:i A') }}</p>
                                            <p>Method: {{ ucfirst($registration->checkIn->check_in_method) }}</p>
                                            @if($registration->checkIn->checkedInBy)
                                                <p>Checked in by: {{ $registration->checkIn->checkedInBy->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Not Checked In</h3>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>This attendee has not checked in yet.</p>
                                            <div class="mt-3">
                                                <a href="{{ route('admin.check-in.manual') }}?code={{ $registration->registration_code }}" 
                                                   class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-800 bg-yellow-100 hover:bg-yellow-200">
                                                    Check In Now
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.registrations.show', $registration) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Update Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
