<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- SEO Meta Tags -->
        <title>{{ $title ?? config('app.name', 'AirforceBES Events') }}</title>
        <meta name="description" content="{{ $description ?? 'Discover and register for upcoming Air Force events, workshops, and networking opportunities.' }}">
        <meta name="keywords" content="{{ $keywords ?? 'Air Force, events, workshops, networking, professional development' }}">
        
        <!-- Open Graph Meta Tags -->
        <meta property="og:title" content="{{ $title ?? config('app.name') }}">
        <meta property="og:description" content="{{ $description ?? 'Discover and register for upcoming Air Force events, workshops, and networking opportunities.' }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:site_name" content="{{ config('app.name') }}">
        @isset($ogImage)
            <meta property="og:image" content="{{ $ogImage }}">
        @endisset
        
        <!-- Twitter Card Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title ?? config('app.name') }}">
        <meta name="twitter:description" content="{{ $description ?? 'Discover and register for upcoming Air Force events, workshops, and networking opportunities.' }}">
        
        <!-- Canonical URL -->
        <link rel="canonical" href="{{ url()->current() }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Additional Head Content -->
        @stack('head')
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Flash Messages -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 relative max-w-7xl mx-auto mt-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 relative max-w-7xl mx-auto mt-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.footer')
        </div>
        
        <!-- Additional Scripts -->
        @stack('scripts')
    </body>
</html>
