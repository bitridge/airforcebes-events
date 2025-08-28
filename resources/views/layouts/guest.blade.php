<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ app_name() }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-indigo-50 to-blue-100">
            <!-- Logo -->
            <div class="mb-6">
                <a href="/" class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-900">{{ app_name() }}</span>
                </a>
            </div>

            <!-- Main Content -->
            <div class="w-full max-w-md sm:max-w-lg lg:max-w-xl mx-auto px-6">
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
                    <div class="px-6 py-8 sm:px-8 sm:py-10">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    &copy; {{ date('Y') }} {{ app_name() }}. All rights reserved.
                </p>
            </div>
        </div>
    </body>
</html>
