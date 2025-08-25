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
                                                    Recommended: PNG, JPG, or SVG. Max size: 2MB.
                                                @elseif($setting->key === 'app.favicon')
                                                    Recommended: ICO or PNG. Max size: 1MB.
                                                @else
                                                    Max file size: 5MB.
                                                @endif
                                            </p>
                                        </div>
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

            <!-- Appearance Settings Tab -->
            <div x-show="activeTab === 'appearance'" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Appearance Settings</h3>
                    <p class="text-sm text-gray-600 mt-1">Customize the look and feel of your application</p>
                </div>
                
                <form id="appearance-settings-form" class="space-y-6">
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
                                    @case('color')
                                        <div class="flex items-center space-x-2">
                                            <input 
                                                type="color" 
                                                name="settings[{{ $setting->key }}]" 
                                                value="{{ $setting->display_value }}"
                                                class="h-10 w-20 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                            >
                                            <input 
                                                type="text" 
                                                value="{{ $setting->display_value }}"
                                                class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                                readonly
                                            >
                                        </div>
                                        @break
                                    
                                    @case('select')
                                        <select 
                                            name="settings[{{ $setting->key }}]" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                        >
                                            @foreach($setting->options ?? [] as $value => $label)
                                                <option value="{{ $value }}" {{ $setting->display_value == $value ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @break
                                    
                                    @case('text')
                                        <textarea 
                                            name="settings[{{ $setting->key }}]" 
                                            rows="4"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                            placeholder="Enter custom CSS..."
                                        >{{ $setting->display_value }}</textarea>
                                        @break
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save Appearance Settings
                        </button>
                    </div>
                </form>
            </div>

            <!-- SMTP Settings Tab -->
            <div x-show="activeTab === 'smtp'" class="space-y-6">
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-lg font-medium text-gray-900">SMTP Configuration</h3>
                    <p class="text-sm text-gray-600 mt-1">Configure email server settings and test connection</p>
                </div>
                
                <!-- MailHog Information -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-blue-800">MailHog for Local Development</h4>
                            <div class="mt-2 text-sm text-blue-700">
                                <p>For local email testing, we recommend using <strong>MailHog</strong>. It captures all emails sent from your application and displays them in a web interface.</p>
                                <div class="mt-2 space-y-1">
                                    <p><strong>Setup:</strong></p>
                                    <ul class="list-disc list-inside ml-2 space-y-1">
                                        <li>Install MailHog: <code class="bg-blue-100 px-1 rounded">brew install mailhog</code> (macOS) or download from <a href="https://github.com/mailhog/MailHog" target="_blank" class="underline">GitHub</a></li>
                                        <li>Start MailHog: <code class="bg-blue-100 px-1 rounded">mailhog</code></li>
                                        <li>Access web interface: <a href="http://localhost:8025" target="_blank" class="underline">http://localhost:8025</a></li>
                                        <li>SMTP port: <code class="bg-blue-100 px-1 rounded">1025</code></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form id="smtp-settings-form" class="space-y-6">
                    @csrf
                    
                    <!-- SMTP Provider Selection -->
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700">SMTP Provider</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach($smtpProviders as $key => $provider)
                                <button 
                                    type="button"
                                    onclick="selectSmtpProvider('{{ $key }}')"
                                    class="p-4 border-2 border-gray-200 rounded-lg hover:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    id="provider-{{ $key }}"
                                >
                                    <div class="text-center">
                                        <div class="text-sm font-medium text-gray-900">{{ $provider['name'] }}</div>
                                    </div>
                                </button>
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
                                    
                                    @case('password')
                                                                                    <input 
                                                type="password" 
                                                name="settings[{{ $setting->key }}]" 
                                                value="{{ $setting->display_value }}"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 sm:text-sm"
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
                    
                    <!-- Test Email Section -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Test SMTP Connection</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Test Email Address</label>
                                <input 
                                    type="email" 
                                    id="test-email"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Enter email to send test to"
                                >
                            </div>
                            <div class="flex items-end">
                                <button 
                                    type="button"
                                    onclick="testSmtpConnection()"
                                    class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                    id="test-connection-btn"
                                >
                                    <span id="test-btn-text">Test Connection</span>
                                    <span id="test-btn-loading" class="hidden flex items-center">
                                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Testing...
                                    </span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Test Results -->
                        <div x-show="smtpTestResult" class="mt-4 p-3 rounded-md" :class="smtpTestResult.success ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg x-show="smtpTestResult.success" class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                    <svg x-show="!smtpTestResult.success" class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium" x-text="smtpTestResult.message"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="testSmtpConnection()" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Test Connection
                        </button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save SMTP Settings
                        </button>
                    </div>
                </form>
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
// Global variables for SMTP testing
let selectedProvider = 'custom';
const smtpProviders = @json($smtpProviders);

// Function to select SMTP provider
function selectSmtpProvider(provider) {
    selectedProvider = provider;
    
    // Update visual selection
    document.querySelectorAll('[id^="provider-"]').forEach(btn => {
        btn.classList.remove('border-blue-500', 'bg-blue-50');
        btn.classList.add('border-gray-200');
    });
    
    const selectedBtn = document.getElementById('provider-' + provider);
    if (selectedBtn) {
        selectedBtn.classList.remove('border-gray-200');
        selectedBtn.classList.add('border-blue-500', 'bg-blue-50');
    }
    
    if (smtpProviders[provider]) {
        const data = smtpProviders[provider];
        document.querySelector('input[name="settings[mail.smtp_host]"]').value = data.host;
        document.querySelector('input[name="settings[mail.smtp_port]"]').value = data.port;
        document.querySelector('select[name="settings[mail.smtp_encryption]"]').value = data.encryption;
        document.querySelector('select[name="settings[mail.driver]"]').value = data.driver;
    }
}

// Function to manually update SMTP test result UI (fallback)
function updateSmtpTestResult(result) {
    const testResultsContainer = document.querySelector('[x-show="smtpTestResult"]');
    if (testResultsContainer) {
        // Remove x-show directive temporarily
        testResultsContainer.removeAttribute('x-show');
        testResultsContainer.style.display = 'block';
        
        // Update the content
        const messageElement = testResultsContainer.querySelector('p');
        if (messageElement) {
            messageElement.textContent = result.message;
        }
        
        // Update styling
        if (result.success) {
            testResultsContainer.className = 'mt-4 p-3 rounded-md bg-green-50 text-green-800';
        } else {
            testResultsContainer.className = 'mt-4 p-3 rounded-md bg-red-50 text-red-800';
        }
    }
}

// Function to test SMTP connection
async function testSmtpConnection() {
    console.log('Starting SMTP test...');
    
    const testBtn = document.getElementById('test-connection-btn');
    const testBtnText = document.getElementById('test-btn-text');
    const testBtnLoading = document.getElementById('test-btn-loading');
    
    if (!testBtn || !testBtnText || !testBtnLoading) {
        console.error('Required button elements not found');
        return;
    }
    
    // Show loading state
    testBtn.disabled = true;
    testBtnText.classList.add('hidden');
    testBtnLoading.classList.remove('hidden');
    
    // Hide previous results
    const existingResult = document.querySelector('[x-show="smtpTestResult"]');
    if (existingResult) {
        existingResult.style.display = 'none';
    }
    
    const form = document.getElementById('smtp-settings-form');
    if (!form) {
        console.error('SMTP form not found');
        return;
    }
    
    const formData = new FormData(form);
    
    // Add test email
    const testEmail = document.getElementById('test-email').value;
    if (testEmail) {
        formData.append('smtp_settings[test_email]', testEmail);
        console.log('Test email:', testEmail);
    }

    try {
        // Add timeout to prevent hanging
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
        
        const response = await fetch('{{ route("admin.settings.test-smtp") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: formData,
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);

        const result = await response.json();
        console.log('SMTP test response:', result);
        
        // Update Alpine.js data using global reference
        if (window.settingsComponent) {
            console.log('Updating Alpine.js component with result');
            window.settingsComponent.smtpTestResult = result;
        } else {
            console.log('Alpine.js component not found, using fallback');
            // Fallback: manually update the UI
            updateSmtpTestResult(result);
        }
        
    } catch (error) {
        // Show error results
        let errorMessage = 'Failed to test SMTP connection: ';
        
        if (error.name === 'AbortError') {
            errorMessage += 'Request timed out after 30 seconds';
        } else {
            errorMessage += error.message;
        }
        
        const errorResult = {
            success: false,
            message: errorMessage
        };
        
        // Update Alpine.js data using global reference
        if (window.settingsComponent) {
            console.log('Updating Alpine.js component with error result');
            window.settingsComponent.smtpTestResult = errorResult;
        } else {
            console.log('Alpine.js component not found, using fallback for error');
            // Fallback: manually update the UI
            updateSmtpTestResult(errorResult);
        }
    } finally {
        // Reset button state
        testBtn.disabled = false;
        testBtnText.classList.remove('hidden');
        testBtnLoading.classList.add('hidden');
    }
}

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

    // Appearance Settings Form
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

    // SMTP Settings Form
    document.getElementById('smtp-settings-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        try {
            const response = await fetch('{{ route("admin.settings.update-group", "smtp") }}', {
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
        });
    </script>

    <!-- Fallback JavaScript for production -->
    <script>
        // Debug information
        document.addEventListener('DOMContentLoaded', function() {
            // Update debug info
            if (document.getElementById('alpine-status')) {
                document.getElementById('alpine-status').textContent = typeof window.Alpine !== 'undefined' ? 'Yes' : 'No';
            }
            if (document.getElementById('vite-status')) {
                document.getElementById('vite-status').textContent = 'Loaded';
            }

            // Check if Alpine.js is loaded
            if (typeof window.Alpine === 'undefined') {
                console.warn('Alpine.js not loaded, using fallback JavaScript');
                initializeFallbackTabs();
            }
        });

        function initializeFallbackTabs() {
            const container = document.getElementById('settings-container');
            if (!container) return;

            // Get all tab buttons
            const tabButtons = container.querySelectorAll('nav button');
            const tabContents = container.querySelectorAll('[x-show]');

            // Add click handlers to tab buttons
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.textContent.trim().toLowerCase().replace(/\s+/g, '_');
                    
                    // Update active tab state
                    tabButtons.forEach(btn => {
                        btn.classList.remove('border-blue-500', 'text-blue-600');
                        btn.classList.add('border-transparent', 'text-gray-500');
                    });
                    
                    this.classList.remove('border-transparent', 'text-gray-500');
                    this.classList.add('border-blue-500', 'text-blue-600');

                    // Show/hide tab content
                    tabContents.forEach(content => {
                        if (content.getAttribute('x-show') === `activeTab === '${targetTab}'`) {
                            content.style.display = 'block';
                        } else {
                            content.style.display = 'none';
                        }
                    });
                });
            });

            // Show first tab by default
            if (tabButtons.length > 0) {
                tabButtons[0].click();
            }
        }

        // Enhanced error handling for SMTP test
        window.addEventListener('error', function(e) {
            console.error('JavaScript error:', e.error);
            if (e.error && e.error.message && e.error.message.includes('Alpine')) {
                console.warn('Alpine.js error detected, attempting fallback initialization');
                setTimeout(initializeFallbackTabs, 100);
            }
        });
    </script>
    @endsection
