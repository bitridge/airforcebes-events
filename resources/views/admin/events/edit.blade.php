@extends('layouts.app')

@section('title', 'Edit Event - ' . config('app.name'))

@section('content')
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Event</h1>
                    <p class="text-gray-600">{{ $event->title }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Event
                    </a>
                    <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Events
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

            <!-- Edit Event Form -->
            <div class="bg-white shadow rounded-lg">
                <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Event Information</h3>
                    </div>
                    
                    <div class="px-6 py-4 space-y-6">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Event Title *</label>
                                <input type="text" id="title" name="title" value="{{ old('title', $event->title) }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700">Slug</label>
                                <input type="text" id="slug" name="slug" value="{{ old('slug', $event->slug) }}"
                                       placeholder="auto-generated if empty"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                            <textarea id="description" name="description" rows="6" required
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('description', $event->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Date and Time -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date', $event->start_date->format('Y-m-d')) }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date *</label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date', $event->end_date->format('Y-m-d')) }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                                <input type="time" id="start_time" name="start_time" value="{{ old('start_time', $event->start_time) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="09:00">
                            </div>
                            
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                                <input type="time" id="end_time" name="end_time" value="{{ old('end_time', $event->end_time) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="17:00">
                            </div>
                        </div>

                        <!-- Venue and Capacity -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="venue" class="block text-sm font-medium text-gray-700">Venue *</label>
                                <input type="text" id="venue" name="venue" value="{{ old('venue', $event->venue) }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('venue')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="max_capacity" class="block text-sm font-medium text-gray-700">Maximum Capacity</label>
                                <input type="number" id="max_capacity" name="max_capacity" value="{{ old('max_capacity', $event->max_capacity) }}" min="1"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('max_capacity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Registration Settings -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="registration_deadline" class="block text-sm font-medium text-gray-700">Registration Deadline</label>
                                <input type="datetime-local" id="registration_deadline" name="registration_deadline" 
                                       value="{{ old('registration_deadline', $event->registration_deadline ? $event->registration_deadline->format('Y-m-d\TH:i') : '') }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('registration_deadline')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Featured Image -->
                        <div>
                            <label for="featured_image" class="block text-sm font-medium text-gray-700">Featured Image</label>
                            @if($event->featured_image)
                                <div class="mt-2 mb-4">
                                    <img src="{{ Storage::url($event->featured_image) }}" alt="Current featured image" class="w-32 h-24 object-cover rounded-lg">
                                    <p class="text-sm text-gray-500 mt-1">Current image</p>
                                </div>
                            @endif
                            <input type="file" id="featured_image" name="featured_image" accept="image/*" onchange="previewImage(this)"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="mt-1 text-sm text-gray-500">PNG, JPG, GIF up to 5MB. Minimum dimensions: 300x200px. Leave empty to keep current image.</p>
                            
                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3 hidden">
                                <img id="previewImg" src="" alt="Preview" class="max-w-xs rounded-lg shadow-sm">
                                <button type="button" onclick="removeImage()" class="mt-2 text-sm text-red-600 hover:text-red-800">
                                    Remove New Image
                                </button>
                            </div>
                            @error('featured_image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tags -->
                        <div>
                            <label for="tags" class="block text-sm font-medium text-gray-700">Tags</label>
                            <input type="text" id="tags" name="tags" value="{{ old('tags', $event->tags) }}"
                                   placeholder="Enter tags separated by commas"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="mt-1 text-sm text-gray-500">Separate multiple tags with commas</p>
                            @error('tags')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 text-right rounded-b-lg">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Event
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            const slug = title.toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            document.getElementById('slug').value = slug;
        });

        // Ensure time inputs use 24-hour format
        document.addEventListener('DOMContentLoaded', function() {
            const timeInputs = document.querySelectorAll('input[type="time"]');
            
            timeInputs.forEach(function(input) {
                // Force 24-hour format
                input.setAttribute('data-format', '24h');
                
                // Ensure value is in 24-hour format on blur
                input.addEventListener('blur', function() {
                    if (this.value) {
                        // Convert any 12-hour format to 24-hour
                        let time = this.value;
                        if (time.includes('AM') || time.includes('PM')) {
                            const [hours, minutes] = time.split(':');
                            let hour = parseInt(hours);
                            const minute = minutes.replace(/\s*(AM|PM)/i, '');
                            
                            if (time.includes('PM') && hour !== 12) {
                                hour += 12;
                            } else if (time.includes('AM') && hour === 12) {
                                hour = 0;
                            }
                            
                            time = `${hour.toString().padStart(2, '0')}:${minute}`;
                            this.value = time;
                        }
                    }
                });
            });
        });

        // Image preview functionality
        function previewImage(input) {
            const file = input.files[0];
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (file) {
                // Validate file size (5MB = 5 * 1024 * 1024 bytes)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    input.value = '';
                    return;
                }
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                preview.classList.add('hidden');
            }
        }

        function removeImage() {
            document.getElementById('featured_image').value = '';
            document.getElementById('imagePreview').classList.add('hidden');
        }
    </script>
@endsection
