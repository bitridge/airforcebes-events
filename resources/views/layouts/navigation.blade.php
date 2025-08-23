<nav class="bg-slate-800 border-b border-slate-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo and Brand -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('welcome') }}" class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-red-600 rounded flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="text-white">
                            <div class="font-semibold text-lg">AF Event Management</div>
                            <div class="text-xs text-slate-300">Life Cycle Management Center</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="hidden md:flex items-center space-x-1">
                <!-- Events Link -->
                <a href="{{ route('events.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('events.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Events
                </a>

                @auth
                    <!-- Check-in Link (for authenticated users) -->
                    <a href="{{ route('checkin.index') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('checkin.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Check-in
                    </a>

                    @if(auth()->user()->isAdmin())
                        <!-- Dashboard Link (for admins) -->
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 {{ request()->routeIs('admin.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:text-white hover:bg-slate-700' }}">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                            </svg>
                            Dashboard
                        </a>
                    @endif
                @endauth
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 text-slate-300 hover:text-white focus:outline-none focus:text-white transition-colors duration-200">
                            <div class="w-8 h-8 bg-slate-600 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-white">{{ auth()->user()->initials }}</span>
                            </div>
                            <span class="hidden md:block text-sm font-medium">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-50">
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
                        <a href="{{ route('register') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                            Register
                        </a>
                    </div>
                @endauth

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="mobileOpen = !mobileOpen" class="text-slate-400 hover:text-white focus:outline-none focus:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-data="{ mobileOpen: false }" x-show="mobileOpen" class="md:hidden bg-slate-900">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="{{ route('events.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Events</a>
            @auth
                <a href="{{ route('checkin.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Check-in</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Dashboard</a>
                @endif
                <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Profile</a>
                <a href="{{ route('registrations.index') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">My Registrations</a>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Logout</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block px-3 py-2 text-base font-medium text-slate-300 hover:text-white hover:bg-slate-700 rounded-md">Sign In</a>
                <a href="{{ route('register') }}" class="block px-3 py-2 text-base font-medium bg-red-600 hover:bg-red-700 text-white rounded-md">Register</a>
            @endauth
        </div>
    </div>
</nav>