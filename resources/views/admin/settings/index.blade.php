@extends('layouts.admin')

@section('title', 'Settings Management')

@section('content')
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
    <div x-data="{ activeTab: 'general' }" class="bg-white rounded-lg shadow">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                @foreach(['general', 'appearance', 'smtp', 'notifications', 'system'] as $tab)
                    <button
                        @click="activeTab = '{{ $tab }}'"
                        :class="activeTab === '{{ $tab }}' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                    >
                        {{ ucfirst(str_replace('_', ' ', $tab)) }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- General Settings Tab -->
            <div x-show="activeTab === 'general'" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">General Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Basic application configuration</p>
                </div>
                
                <form id="general-settings-form" class="space-y-6">
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
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save General Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Other tabs -->
            <div x-show="activeTab === 'appearance'" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Appearance Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Customize the look and feel</p>
                </div>
                <p class="text-gray-500">Appearance settings coming soon...</p>
            </div>

            <div x-show="activeTab === 'smtp'" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">SMTP Configuration</h3>
                    <p class="text-sm text-gray-600 mt-1">Email server settings</p>
                </div>
                <p class="text-gray-500">SMTP settings coming soon...</p>
            </div>

            <div x-show="activeTab === 'notifications'" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Notification Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Configure notifications</p>
                </div>
                <p class="text-gray-500">Notification settings coming soon...</p>
            </div>

            <div x-show="activeTab === 'system'" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">System Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">System configuration</p>
                </div>
                <p class="text-gray-500">System settings coming soon...</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // General Settings Form
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
});
</script>
@endsection
