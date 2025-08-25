<nav class="bg-slate-800 border-b border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- First Row: Company Name and User Info -->
        <div class="flex justify-between items-center py-3">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}" class="flex items-center space-x-3">
                        @if(app_logo())
                            <img src="{{ asset('storage/' . app_logo()) }}" alt="{{ app_name() }}" class="w-8 h-8 rounded">
                        @else
                            <div class="w-8 h-8 rounded flex items-center justify-center" style="background-color: {{ primary_color() }}">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        <div class="text-white">
                            <div class="font-semibold text-lg">{{ app_name() }}</div>
                            <div class="text-xs text-slate-300">{{ app_description() }}</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- User Dropdown -->
                    <div class="relative" id="user-dropdown" x-data="{ open: false }">
                        <button @click="open = !open" id="user-dropdown-button" class="flex items-center space-x-2 text-slate-300 hover:text-white focus:outline-none focus:text-white transition-colors duration-200">
                            <div class="w-8 h-8 bg-slate-600 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-white">{{ auth()->user()->initials }}</span>
                            </div>
                            <span class="hidden md:block text-sm font-medium">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" id="user-dropdown-menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
                            <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </a>
                            <a href="{{ route('registrations.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                My Registrations
                            </a>
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-200">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Guest Links -->
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('login') }}" class="text-slate-300 hover:text-white text-sm font-medium transition-colors duration-200">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200" style="background-color: {{ primary_color() }}; hover: {{ primary_color() }}; filter: brightness(0.9);">
                            Register
                        </a>
                    </div>
                @endauth

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="mobileOpen = !mobileOpen" id="mobile-menu-button" class="text-slate-400 hover:text-white focus:outline-none focus:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Second Row: Main Navigation -->
        @auth
            @if(auth()->user()->isAdmin())
                <div class="flex items-center justify-center space-x-1 py-3 border-t border-slate-700">
                    <!-- Admin Check-in Link -->
                    <a href="{{ route('admin.check-in.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.check-in.*') ? 'text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}" style="{{ request()->routeIs('admin.check-in.*') ? 'background-color: ' . primary_color() : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Check-in Station
                    </a>

                    <!-- Dashboard Link (for admins) -->
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}" style="{{ request()->routeIs('admin.dashboard') ? 'background-color: ' . primary_color() : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Events Management Link -->
                    <a href="{{ route('admin.events.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.events.*') ? 'text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}" style="{{ request()->routeIs('admin.events.*') ? 'background-color: ' . primary_color() : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Events
                    </a>

                    <!-- Registrations Management Link -->
                    <a href="{{ route('admin.registrations.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.registrations.*') ? 'text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}" style="{{ request()->routeIs('admin.registrations.*') ? 'background-color: ' . primary_color() : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Registrations
                    </a>

                    <!-- Attendees Management Link -->
                    <a href="{{ route('admin.attendees.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.attendees.*') ? 'text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}" style="{{ request()->routeIs('admin.attendees.*') ? 'background-color: ' . primary_color() : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        Attendees
                    </a>

                    <!-- Reports & Analytics Link -->
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.reports.*') ? 'text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}" style="{{ request()->routeIs('admin.reports.*') ? 'background-color: ' . primary_color() : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Reports
                    </a>

                    <!-- Settings Link -->
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.settings.*') ? 'text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}" style="{{ request()->routeIs('admin.settings.*') ? 'background-color: ' . primary_color() : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                </div>
            @else
                <!-- Regular User Navigation -->
                <div class="flex items-center justify-center space-x-1 py-3 border-t border-slate-700">
                    <!-- Check-in Link for regular users -->
                    <a href="{{ route('self-checkin.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('self-checkin.*') ? 'text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}" style="{{ request()->routeIs('self-checkin.*') ? 'background-color: ' . primary_color() : '' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Check In
                    </a>
                </div>
            @endif
        @endauth
    </div>

    <!-- Mobile menu -->
    <div x-data="{ mobileOpen: false }" id="mobile-menu" x-show="mobileOpen" class="md:hidden bg-slate-900">
        <div class="px-2 pt-2 pb-3 space-y-1">
            @auth
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.check-in.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Check-in Station</a>
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Dashboard</a>
                    <a href="{{ route('admin.events.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Events</a>
                    <a href="{{ route('admin.registrations.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Registrations</a>
                    <a href="{{ route('admin.attendees.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Attendees</a>
                    <a href="{{ route('admin.reports.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Reports</a>
                    <a href="{{ route('admin.settings.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Settings</a>
                @endif
                <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Profile</a>
                <a href="{{ route('registrations.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">My Registrations</a>
                <a href="{{ route('self-checkin.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Check In</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Sign In</a>
                <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium text-white rounded-md" style="background-color: {{ primary_color() }}; hover: {{ primary_color() }}; filter: brightness(0.9);">Register</a>
            @endauth
        </div>
    </div>
</nav>

<!-- Fallback JavaScript for navigation dropdowns when Alpine.js is not loaded -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check if Alpine.js is loaded
        if (typeof window.Alpine === 'undefined') {
            console.warn('Alpine.js not loaded, using fallback JavaScript for navigation');
            initializeNavigationFallbacks();
        }
    });

    function initializeNavigationFallbacks() {
        // User dropdown fallback
        const userDropdownButton = document.getElementById('user-dropdown-button');
        const userDropdownMenu = document.getElementById('user-dropdown-menu');
        
        if (userDropdownButton && userDropdownMenu) {
            // Initially hide the dropdown
            userDropdownMenu.style.display = 'none';
            
            // Toggle dropdown on button click
            userDropdownButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isVisible = userDropdownMenu.style.display === 'block';
                userDropdownMenu.style.display = isVisible ? 'none' : 'block';
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdownButton.contains(e.target) && !userDropdownMenu.contains(e.target)) {
                    userDropdownMenu.style.display = 'none';
                }
            });
            
            // Close dropdown on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    userDropdownMenu.style.display = 'none';
                }
            });
        }
        
        // Mobile menu fallback
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuButton && mobileMenu) {
            // Initially hide the mobile menu
            mobileMenu.style.display = 'none';
            
            // Toggle mobile menu on button click
            mobileMenuButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const isVisible = mobileMenu.style.display === 'block';
                mobileMenu.style.display = isVisible ? 'none' : 'block';
            });
            
            // Close mobile menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!mobileMenuButton.contains(e.target) && !mobileMenu.contains(e.target)) {
                    mobileMenu.style.display = 'none';
                }
            });
            
            // Close mobile menu on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    mobileMenu.style.display = 'none';
                }
            });
        }
    }

    // Enhanced error handling for navigation
    window.addEventListener('error', function(e) {
        console.error('JavaScript error in navigation:', e.error);
        if (e.error && e.error.message && e.error.message.includes('Alpine')) {
            console.warn('Alpine.js error detected in navigation, attempting fallback initialization');
            setTimeout(initializeNavigationFallbacks, 100);
        }
    });
</script>