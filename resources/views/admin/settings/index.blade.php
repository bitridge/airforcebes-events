@extends('layouts.app')

@section('title', 'Settings Management')

@section('content')
<style>
    /* Tab system CSS */
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }
    /* Show general tab by default */
    .tab-content[data-tab="general"] {
        display: block !important;
    }
    /* Ensure tab buttons are clickable */
    .tab-button {
        cursor: pointer;
        user-select: none;
    }
    /* Active tab styles */
    .tab-button.active {
        border-bottom-color: #3b82f6 !important;
        color: #2563eb !important;
    }
    /* Ensure general tab is always visible initially */
    .tab-content[data-tab="general"] {
        display: block !important;
    }
</style>

<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Settings Management</h1>
        <p class="text-gray-600 mt-2">Manage application configuration and system preferences</p>
    </div>

    @if(isset($error))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            {{ $error }}
        </div>
    @endif



    <!-- Settings Tabs -->
    <div id="settings-container" 
    class="bg-white rounded-lg shadow">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                @foreach(['general', 'appearance', 'notifications', 'system'] as $tab)
                    <button
                        onclick="showTab('{{ $tab }}')"
                        class="tab-button whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300"
                        data-tab="{{ $tab }}"
                    >
                        {{ ucfirst(str_replace('_', ' ', $tab)) }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- General Settings Tab -->
            <div data-tab="general" class="tab-content space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">General Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Basic application configuration</p>
                </div>
                
                <form id="general-settings-form" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($settings['general'] ?? [] as $setting)
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ $setting->label }}
                                    @if($setting->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                
                                @if($setting->description)
                                    <p class="text-xs text-gray-500">{{ $setting->description }}</p>
                                @endif

                                @switch($setting->type->value)
                                    @case('text')
                                    @case('email')
                                    @case('url')
                                        <input 
                                            type="{{ $setting->type->value }}" 
                                            name="settings[{{ $setting->key }}]" 
                                            value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                        @break
                                    
                                    @case('select')
                                        <select 
                                            name="settings[{{ $setting->key }}]" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                            @foreach($setting->options ?? [] as $value => $label)
                                                <option value="{{ $value }}" {{ $setting->display_value == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @break
                                    
                                    @case('integer')
                                        <input 
                                            type="number" 
                                            name="settings[{{ $setting->key }}]" 
                                            value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                        @break
                                    
                                    @case('file')
                                        <div class="space-y-3">
                                            @if($setting->display_value)
                                                <div class="flex items-center space-x-3">
                                                    @if(in_array(strtolower(pathinfo($setting->display_value, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'svg']))
                                                        <img src="{{ asset('storage/' . $setting->display_value) }}" alt="Current {{ $setting->label }}" class="w-16 h-16 object-cover rounded border">
                                                    @else
                                                        <div class="w-16 h-16 bg-gray-100 rounded border flex items-center justify-center">
                                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <div class="text-sm text-gray-600">
                                                        <p class="font-medium">Current File:</p>
                                                        <p class="text-xs">{{ $setting->display_value }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                            <input 
                                                type="file" 
                                                name="settings[{{ $setting->key }}]" 
                                                accept="{{ $setting->key === 'app.logo' ? 'image/*' : ($setting->key === 'app.favicon' ? 'image/x-icon,image/png' : '*') }}"
                                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                @if($setting->is_required) required @endif
                                            >
                                            <p class="text-xs text-gray-500">
                                                @if($setting->key === 'app.logo')
                                                    PNG, JPG, GIF up to 2MB. Recommended size: 300x200px
                                                @elseif($setting->key === 'app.favicon')
                                                    ICO, PNG up to 1MB. Recommended size: 32x32px
                                                @else
                                                    Any file type up to 5MB
                                                @endif
                                            </p>
                                        </div>
                                        @break
                                    
                                    @default
                                        <input 
                                            type="text" 
                                            name="settings[{{ $setting->key }}]" 
                                            value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Save General Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Appearance Settings Tab -->
            <div data-tab="appearance" class="tab-content space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Appearance Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Customize the look and feel of your application</p>
                </div>
                
                <form id="appearance-settings-form" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($settings['appearance'] ?? [] as $setting)
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ $setting->label }}
                                    @if($setting->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                
                                @if($setting->description)
                                    <p class="text-xs text-gray-500">{{ $setting->description }}</p>
                                @endif

                                @switch($setting->type->value)
                                    @case('text')
                                    @case('color')
                                            <input 
                                            type="{{ $setting->type->value }}" 
                                                name="settings[{{ $setting->key }}]" 
                                                value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                        @break
                                    
                                    @case('select')
                                        <select 
                                            name="settings[{{ $setting->key }}]" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                            @foreach($setting->options ?? [] as $value => $label)
                                                <option value="{{ $value }}" {{ $setting->display_value == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @break
                                    
                                    @default
                                        <input 
                                            type="text" 
                                            name="settings[{{ $setting->key }}]" 
                                            value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Save Appearance Settings
                        </button>
                    </div>
                </form>
            </div>





                


            <!-- Notifications Settings Tab -->
            <div data-tab="notifications" class="tab-content space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Notification Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Configure email notifications and alerts</p>
                </div>
                
                <form id="notifications-settings-form" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($settings['notifications'] ?? [] as $setting)
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ $setting->label }}
                                    @if($setting->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                
                                @if($setting->description)
                                    <p class="text-xs text-gray-500">{{ $setting->description }}</p>
                                @endif

                                @switch($setting->type->value)
                                    @case('text')
                                    @case('email')
                                        <input 
                                            type="{{ $setting->type->value }}" 
                                            name="settings[{{ $setting->key }}]" 
                                            value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                        @break
                                    
                                    @case('select')
                                        <select 
                                            name="settings[{{ $setting->key }}]" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                            @foreach($setting->options ?? [] as $value => $label)
                                                <option value="{{ $value }}" {{ $setting->display_value == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @break
                                    
                                    @case('boolean')
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="settings[{{ $setting->key }}]" 
                                                   value="1"
                                                   {{ $setting->display_value ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600">Enable {{ $setting->label }}</span>
                                        </label>
                                        @break
                                    
                                    @default
                                        <input 
                                            type="text" 
                                            name="settings[{{ $setting->key }}]" 
                                            value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Save Notification Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- System Settings Tab -->
            <div data-tab="system" class="tab-content space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">System Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">System configuration and maintenance</p>
                </div>
                
                <!-- Cache Management -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Cache Management</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-white rounded-lg p-4 border">
                            <div class="text-sm font-medium text-gray-500">Application Cache</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $cacheStats['app'] ?? 'N/A' }}</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border">
                            <div class="text-sm font-medium text-gray-500">Route Cache</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $cacheStats['route'] ?? 'N/A' }}</div>
                        </div>
                        <div class="bg-white rounded-lg p-4 border">
                            <div class="text-sm font-medium text-gray-500">Config Cache</div>
                            <div class="mt-1 text-2xl font-semibold text-gray-900">{{ $cacheStats['config'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" onclick="clearCache('app')" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Clear App Cache
                        </button>
                        <button type="button" onclick="clearCache('route')" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Clear Route Cache
                        </button>
                        <button type="button" onclick="clearCache('config')" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Clear Config Cache
                        </button>
                        <button type="button" onclick="clearAllCache()" class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Clear All Caches
                        </button>
                    </div>
                </div>
                
                <!-- System Settings Form -->
                <form id="system-settings-form" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($settings['system'] ?? [] as $setting)
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ $setting->label }}
                                    @if($setting->is_required)
                                        <span class="text-red-500">*</span>
                                    @endif
                                </label>
                                
                                @if($setting->description)
                                    <p class="text-xs text-gray-500">{{ $setting->description }}</p>
                                @endif

                                @switch($setting->type->value)
                                    @case('text')
                                    @case('email')
                                                                                    <input 
                                            type="{{ $setting->type->value }}" 
                                                name="settings[{{ $setting->key }}]" 
                                                value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                            >
                                        @break
                                    
                                    @case('select')
                                        <select 
                                            name="settings[{{ $setting->key }}]" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                            @foreach($setting->options ?? [] as $value => $label)
                                                <option value="{{ $value }}" {{ $setting->display_value == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @break
                                    
                                    @case('boolean')
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="settings[{{ $setting->key }}]" 
                                                   value="1"
                                                   {{ $setting->display_value ? 'checked' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-600">Enable {{ $setting->label }}</span>
                                        </label>
                                        @break
                                    
                                    @default
                                        <input 
                                            type="text" 
                                            name="settings[{{ $setting->key }}]" 
                                            value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Save System Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>


    // Cache Management Functions
    async function clearCache(type) {
        try {
            const response = await fetch('{{ route("admin.settings.clear-cache") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
            },
                body: JSON.stringify({ type: type })
        });

        const result = await response.json();
        
        if (result.success) {
                alert(`${type.charAt(0).toUpperCase() + type.slice(1)} cache cleared successfully!`);
                location.reload();
        } else {
                alert(`Failed to clear ${type} cache: ${result.message}`);
            }
        } catch (error) {
            console.error('Cache clear failed:', error);
            alert('Failed to clear cache. Please try again.');
        }
    }

    async function clearAllCache() {
        if (!confirm('Are you sure you want to clear all caches? This may temporarily slow down the application.')) {
            return;
        }
        
        try {
            // Clear app cache
            await clearCache('app');
            // Clear route cache
            await clearCache('route');
            // Clear config cache
            await clearCache('config');
            
            alert('All caches cleared successfully!');
    } catch (error) {
            console.error('Cache clear failed:', error);
            alert('Failed to clear all caches. Please try again.');
        }
    }

    // Form Submission Handlers
    document.getElementById('general-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("admin.settings.update-group", "general") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                alert('General settings saved successfully!');
            } else {
                alert('Failed to save settings: ' + result.message);
            }
        } catch (error) {
            console.error('Save failed:', error);
            alert('Failed to save settings');
        }
    });

    document.getElementById('appearance-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("admin.settings.update-group", "appearance") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                alert('Appearance settings saved successfully!');
            } else {
                alert('Failed to save settings: ' + result.message);
            }
        } catch (error) {
            console.error('Save failed:', error);
            alert('Failed to save settings');
        }
    });



    document.getElementById('notifications-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("admin.settings.update-group", "notifications") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                alert('Notification settings saved successfully!');
            } else {
                alert('Failed to save settings: ' + result.message);
            }
        } catch (error) {
            console.error('Save failed:', error);
            alert('Failed to save settings');
        }
    });

    document.getElementById('system-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("admin.settings.update-group", "system") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                alert('System settings saved successfully!');
            } else {
                alert('Failed to save settings: ' + result.message);
            }
        } catch (error) {
            console.error('Save failed:', error);
            alert('Failed to save settings');
        }
    });

    // Tab System for Production
    function initFallbackTabs() {
        console.log('Initializing tab system');
        
        // Hide all tab content initially
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
        });
        
        // Show general tab by default
        const generalTab = document.querySelector('[data-tab="general"]');
        if (generalTab) {
            generalTab.style.display = 'block';
        }
        
        // Add click handlers to tab buttons
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const tabName = this.getAttribute('data-tab');
                if (tabName) {
                    showTab(tabName);
                }
            });
        });
        
        // Update initial tab button state
        updateTabButtonState('general');
        
        console.log('Tab system initialized successfully');
    }

    function showTab(tabName) {
        console.log('Showing tab:', tabName);
        
        // Hide all tab content
        const tabContents = document.querySelectorAll('.tab-content');
        console.log('Found tab contents:', tabContents.length);
        tabContents.forEach(content => {
            content.style.display = 'none';
        });
        
        // Show selected tab content
        const selectedTab = document.querySelector(`[data-tab="${tabName}"]`);
        if (selectedTab) {
            selectedTab.style.display = 'block';
            console.log('Tab content shown:', tabName);
        } else {
            console.error('Tab content not found:', tabName);
        }
        
        // Update tab button styles
        updateTabButtonState(tabName);
    }

    function updateTabButtonState(activeTabName) {
        const tabButtons = document.querySelectorAll('.tab-button');
        tabButtons.forEach(button => {
            const buttonTab = button.getAttribute('data-tab');
            if (buttonTab === activeTabName) {
                button.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                button.classList.add('border-blue-500', 'text-blue-600', 'active');
            } else {
                button.classList.remove('border-blue-500', 'text-blue-600', 'active');
                button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            }
        });
    }

        // Initialize tab system immediately
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing tab system');
        
        // Small delay to ensure DOM is fully ready
        setTimeout(function() {
            initFallbackTabs();
        }, 100);
});
</script>
@endsection
