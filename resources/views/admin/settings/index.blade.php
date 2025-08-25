@extends('layouts.app')

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
    <div id="settings-container" x-data="{ 
        activeTab: 'general',
        selectedProvider: 'mailhog',
        testingSmtp: false,
        smtpTestResult: null
    }" 
    x-init="
        window.settingsComponent = $data;
        $watch('smtpTestResult', value => {
            if (value) {
                console.log('SMTP test result updated:', value);
            }
        });
        // Auto-select MailHog provider on page load
        selectSmtpProvider('mailhog');
    "
    class="bg-white rounded-lg shadow">
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
            <div x-show="activeTab === 'appearance'" class="space-y-6">
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

            <!-- SMTP Settings Tab -->
            <div x-show="activeTab === 'smtp'" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">SMTP Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Configure email server settings for notifications</p>
                </div>

                <!-- MailHog Information Box -->
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded">
                    <h4 class="font-medium">MailHog Setup (Local Development)</h4>
                    <p class="text-sm mt-1">For local email testing, use MailHog with these settings:</p>
                    <ul class="text-sm mt-2 list-disc list-inside space-y-1">
                        <li><strong>Host:</strong> localhost</li>
                        <li><strong>Port:</strong> 1025</li>
                        <li><strong>Encryption:</strong> None</li>
                        <li><strong>Authentication:</strong> None</li>
                    </ul>
                    <p class="text-sm mt-2">Access MailHog web interface at: <a href="http://localhost:8025" target="_blank" class="underline">http://localhost:8025</a></p>
                </div>
                
                <form id="smtp-settings-form" class="space-y-6" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- SMTP Provider Selection -->
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700">SMTP Provider</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($smtpProviders as $provider => $config)
                                <label class="relative flex cursor-pointer rounded-lg border border-gray-300 bg-white p-4 shadow-sm focus:outline-none">
                                    <input type="radio" name="smtp_provider" value="{{ $provider }}" 
                                           x-model="selectedProvider"
                                           class="sr-only" aria-labelledby="{{ $provider }}-label">
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span id="{{ $provider }}-label" class="block text-sm font-medium text-gray-900">
                                                {{ ucfirst($provider) }}
                                            </span>
                                            <span class="mt-1 flex items-center text-sm text-gray-500">
                                                {{ $config['description'] }}
                                            </span>
                                        </span>
                                    </span>
                                    <span class="pointer-events-none absolute -inset-px rounded-lg border-2" 
                                          :class="selectedProvider === '{{ $provider }}' ? 'border-blue-500' : 'border-transparent'"></span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- SMTP Configuration Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($settings['smtp'] ?? [] as $setting)
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
                                    
                                    @case('integer')
                                        <input 
                                            type="number" 
                                            name="settings[{{ $setting->key }}]" 
                                            value="{{ $setting->display_value }}"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            @if($setting->is_required) required @endif
                                        >
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
                    
                    <!-- Test Connection Button -->
                    <div class="flex items-center space-x-4">
                        <button type="button" 
                                @click="testSmtpConnection()"
                                :disabled="testingSmtp"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 active:bg-green-900 focus:outline-none focus:border-green-900 focus:ring ring-green-300 disabled:opacity-25 transition ease-in-out duration-150">
                            <svg x-show="!testingSmtp" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <svg x-show="testingSmtp" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="testingSmtp ? 'Testing...' : 'Test Connection'"></span>
                        </button>
                        
                        <!-- Test Result Display -->
                        <div x-show="smtpTestResult" class="flex items-center space-x-2">
                            <div x-show="smtpTestResult.success" class="flex items-center text-green-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span x-text="smtpTestResult.message"></span>
                            </div>
                            <div x-show="!smtpTestResult.success" class="flex items-center text-red-600">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <span x-text="smtpTestResult.message"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Save SMTP Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notifications Settings Tab -->
            <div x-show="activeTab === 'notifications'" class="space-y-6">
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
            <div x-show="activeTab === 'system'" class="space-y-6">
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
    // SMTP Provider Selection
    function selectSmtpProvider(provider) {
        const form = document.getElementById('smtp-settings-form');
        const inputs = form.querySelectorAll('input[name^="settings[smtp."]');
        
        inputs.forEach(input => {
            const key = input.name.match(/settings\[smtp\.([^\]]+)\]/)[1];
            const providerConfig = window.settingsComponent.smtpProviders[provider];
            
            if (providerConfig && providerConfig[key] !== undefined) {
                input.value = providerConfig[key];
            }
        });
    }

    // SMTP Connection Test
    async function testSmtpConnection() {
        const form = document.getElementById('smtp-settings-form');
        const formData = new FormData(form);
        
        // Update Alpine.js state
        window.settingsComponent.testingSmtp = true;
        window.settingsComponent.smtpTestResult = null;
        
        try {
            const response = await fetch('{{ route("settings.test-smtp") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            const result = await response.json();
            
            // Update Alpine.js state with result
            window.settingsComponent.smtpTestResult = result;
            window.settingsComponent.testingSmtp = false;
            
        } catch (error) {
            console.error('SMTP test failed:', error);
            window.settingsComponent.smtpTestResult = {
                success: false,
                message: 'Connection test failed. Please check your settings and try again.'
            };
            window.settingsComponent.testingSmtp = false;
        }
    }

    // Cache Management Functions
    async function clearCache(type) {
        try {
            const response = await fetch('{{ route("settings.clear-cache") }}', {
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
            const response = await fetch('{{ route("settings.update-group", "general") }}', {
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
            const response = await fetch('{{ route("settings.update-group", "appearance") }}', {
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

    document.getElementById('smtp-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("settings.update-group", "smtp") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: formData
            });

            const result = await response.json();
            
            if (result.success) {
                alert('SMTP settings saved successfully!');
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
            const response = await fetch('{{ route("settings.update-group", "notifications") }}', {
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
            const response = await fetch('{{ route("settings.update-group", "system") }}', {
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
</script>
@endsection
