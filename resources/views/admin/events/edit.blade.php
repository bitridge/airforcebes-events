<x-app-layout>
    <x-slot name="title">Edit Event - {{ $event->title }} - {{ config('app.name') }}</x-slot>

    @push('head')
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable { min-height: 200px; }
        .image-preview { max-width: 300px; max-height: 200px; }
    </style>
    @endpush

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Edit Event</h1>
                <p class="mt-2 text-sm text-gray-600">Update the details for "{{ $event->title }}".</p>
            </div>

            <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="bg-white rounded-lg shadow p-6">
                    <!-- Basic Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Event Title *</label>
                                <input type="text" id="title" name="title" value="{{ old('title', $event->title) }}" required
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">URL Slug</label>
                                <input type="text" id="slug" name="slug" value="{{ old('slug', $event->slug) }}" 
                                       placeholder="Auto-generated from title"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate from title</p>
                                @error('slug')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                            <textarea id="description" name="description" required>{{ old('description', $event->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Date & Time</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                                <input type="date" id="start_date" name="start_date" 
                                       value="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}" required
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                                <input type="date" id="end_date" name="end_date" 
                                       value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}" required
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="registration_deadline" class="block text-sm font-medium text-gray-700 mb-1">Registration Deadline</label>
                                <input type="date" id="registration_deadline" name="registration_deadline" 
                                       value="{{ old('registration_deadline', $event->registration_deadline?->format('Y-m-d')) }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                @error('registration_deadline')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                                <input type="time" id="start_time" name="start_time" 
                                       value="{{ old('start_time', $event->start_time) }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                @error('start_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                                <input type="time" id="end_time" name="end_time" 
                                       value="{{ old('end_time', $event->end_time) }}"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                @error('end_time')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Location & Capacity -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Location & Capacity</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">Venue *</label>
                                <input type="text" id="venue" name="venue" value="{{ old('venue', $event->venue) }}" required
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                @error('venue')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="max_capacity" class="block text-sm font-medium text-gray-700 mb-1">Maximum Capacity</label>
                                <input type="number" id="max_capacity" name="max_capacity" 
                                       value="{{ old('max_capacity', $event->max_capacity) }}" min="1"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                <p class="mt-1 text-xs text-gray-500">Leave empty for unlimited capacity</p>
                                @error('max_capacity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Media & SEO -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Media & SEO</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="featured_image" class="block text-sm font-medium text-gray-700 mb-1">Featured Image</label>
                                
                                @if($event->featured_image)
                                    <div class="mb-3">
                                        <p class="text-sm text-gray-600 mb-2">Current image:</p>
                                        <img src="{{ Storage::url($event->featured_image) }}" 
                                             alt="Current featured image" class="image-preview rounded-lg">
                                    </div>
                                @endif
                                
                                <input type="file" id="featured_image" name="featured_image" accept="image/*"
                                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                <p class="mt-1 text-xs text-gray-500">Recommended: 1200x630px, max 2MB. Leave empty to keep current image.</p>
                                @error('featured_image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                
                                <div id="imagePreview" class="mt-2 hidden">
                                    <p class="text-sm text-gray-600 mb-2">New image preview:</p>
                                    <img id="previewImg" class="image-preview rounded-lg" alt="Preview">
                                </div>
                            </div>
                            
                            <div>
                                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                                <textarea id="meta_description" name="meta_description" rows="3" maxlength="160"
                                          class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">{{ old('meta_description', $event->meta_description) }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">Max 160 characters for SEO</p>
                                @error('meta_description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Publication Settings -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Publication Settings</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select id="status" name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                    <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Published</option>
                                    <option value="completed" {{ old('status', $event->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status', $event->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="is_featured" class="flex items-center">
                                    <input type="checkbox" id="is_featured" name="is_featured" value="1" 
                                           {{ old('is_featured', $event->is_featured) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span class="ml-2 text-sm text-gray-700">Featured Event</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500">Show on homepage and featured sections</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.events.show', $event) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Update Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Initialize CKEditor
        ClassicEditor
            .create(document.querySelector('#description'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'undo', 'redo']
            })
            .catch(error => {
                console.error(error);
            });

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            const slug = this.value
                .toLowerCase()
                .replace(/[^a-z0-9 -]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            
            if (!document.getElementById('slug').value) {
                document.getElementById('slug').value = slug;
            }
        });

        // Image preview
        document.getElementById('featured_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('imagePreview').classList.add('hidden');
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            const regDeadline = document.getElementById('registration_deadline').value;
            
            if (endDate <= startDate) {
                e.preventDefault();
                alert('End date must be after start date');
                return;
            }
            
            if (regDeadline && new Date(regDeadline) >= startDate) {
                e.preventDefault();
                alert('Registration deadline must be before event start date');
                return;
            }
        });
    </script>
    @endpush
</x-app-layout>
