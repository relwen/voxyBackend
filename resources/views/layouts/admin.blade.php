<!DOCTYPE html>
<html lang="fr" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VoXY Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: 'rgb(158, 2, 80)',
                            dark: 'rgb(120, 2, 60)',
                            light: 'rgb(190, 20, 110)',
                        },
                        secondary: 'rgb(78, 13, 4)',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .bg-primary-gradient { 
            background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); 
        }
        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .sidebar-item-active {
            background: rgba(255, 255, 255, 0.15);
            border-left: 4px solid white;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-900" x-data="{ sidebarOpen: false }">
    <div class="min-h-full">
        <!-- Off-canvas menu for mobile, show/hide based on off-canvas menu state. -->
        <div x-show="sidebarOpen" class="relative z-50 lg:hidden" x-description="Off-canvas menu for mobile" role="dialog" aria-modal="true" x-cloak>
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition-opacity ease-linear duration-300" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-gray-900/80"></div>

            <div class="fixed inset-0 flex">
                <div x-show="sidebarOpen" 
                     x-transition:enter="transition ease-in-out duration-300 transform" 
                     x-transition:enter-start="-translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transition ease-in-out duration-300 transform" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="-translate-x-full" 
                     class="relative mr-16 flex w-full max-w-xs flex-1"
                     @click.away="sidebarOpen = false">
                    
                    <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                        <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                            <span class="sr-only">Close sidebar</span>
                            <i class="fas fa-times text-white text-xl"></i>
                        </button>
                    </div>

                    <!-- Sidebar component for mobile -->
                    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-primary-gradient px-6 pb-4">
                        <div class="flex h-16 shrink-0 items-center mt-4">
                            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg">
                                <i class="fas fa-music text-primary text-lg"></i>
                            </div>
                            <span class="ml-3 text-2xl font-bold text-white tracking-tight">VoXY Admin</span>
                        </div>
                        <nav class="flex flex-1 flex-col">
                            <ul role="list" class="flex flex-1 flex-col gap-y-7 mt-4">
                                <li>
                                    <ul role="list" class="-mx-2 space-y-1">
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 shadow-lg' : '' }}">
                                            <i class="fas fa-chart-line w-5"></i> Dashboard
                                        </a>
                                        <a href="{{ route('admin.users') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.users*') ? 'bg-white/20 shadow-lg' : '' }}">
                                            <i class="fas fa-users w-5"></i> Utilisateurs
                                        </a>
                                        <a href="{{ route('admin.chorales') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.chorales*') ? 'bg-white/20 shadow-lg' : '' }}">
                                            <i class="fas fa-music w-5"></i> Chorales
                                        </a>
                                        <a href="{{ route('admin.partitions') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.partitions*') ? 'bg-white/20 shadow-lg' : '' }}">
                                            <i class="fas fa-file-audio w-5"></i> Partitions
                                        </a>
                                        <a href="{{ route('admin.messes.index') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.messes.index') ? 'bg-white/20 shadow-lg' : '' }}">
                                            <i class="fas fa-church w-5"></i> Messes
                                        </a>
                                        <a href="{{ route('admin.vocalises.index') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.vocalises.index') ? 'bg-white/20 shadow-lg' : '' }}">
                                            <i class="fas fa-microphone w-5"></i> Vocalises
                                        </a>
                                        <a href="{{ route('admin.chorale.config') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.chorale.config*') ? 'bg-white/20 shadow-lg' : '' }}">
                                            <i class="fas fa-cog w-5"></i> Configuration
                                        </a>
                                        <a href="{{ route('admin.categories') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.categories*') ? 'bg-white/20 shadow-lg' : '' }}">
                                            <i class="fas fa-tags w-5"></i> Catégories
                                        </a>
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Static sidebar for desktop -->
        <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-primary-gradient px-8 pb-4 shadow-2xl">
                <div class="flex h-20 shrink-0 items-center mt-6">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-2xl transform rotate-3">
                        <i class="fas fa-music text-primary text-xl"></i>
                    </div>
                    <span class="ml-4 text-2xl font-black text-white tracking-tighter">VoXY <span class="font-light opacity-80">Admin</span></span>
                </div>
                <nav class="flex flex-1 flex-col mt-8">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7">
                        <li>
                            <ul role="list" class="-mx-2 space-y-2">
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                    <i class="fas fa-chart-pie w-6 text-lg"></i> Tableau de bord
                                </a>
                                <a href="{{ route('admin.users') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.users*') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                    <i class="fas fa-users-gear w-6 text-lg"></i> Utilisateurs
                                </a>
                                <a href="{{ route('admin.chorales') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.chorales*') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                    <i class="fas fa-users-rectangle w-6 text-lg"></i> Chorales
                                </a>
                                <a href="{{ route('admin.partitions') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.partitions*') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                    <i class="fas fa-wand-magic-sparkles w-6 text-lg"></i> Partitions
                                </a>
                                <a href="{{ route('admin.messes.index') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.messes.index') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                    <i class="fas fa-church w-6 text-lg"></i> Messes
                                </a>
                                <a href="{{ route('admin.vocalises.index') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.vocalises.index') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                    <i class="fas fa-microphone w-6 text-lg"></i> Vocalises
                                </a>
                                <a href="{{ route('admin.chorale.config') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.chorale.config*') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                    <i class="fas fa-sliders w-6 text-lg"></i> Configuration
                                </a>
                                <a href="{{ route('admin.categories') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.categories*') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                    <i class="fas fa-layer-group w-6 text-lg"></i> Catégories
                                </a>

                        <li class="mt-auto">
                           <div class="rounded-2xl bg-white/10 p-4 border border-white/10 backdrop-blur-sm">
                               <p class="text-xs font-semibold text-white/60 uppercase tracking-widest">Version</p>
                               <p class="text-sm font-bold text-white mt-1">v2.0.4 Premium</p>
                           </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="lg:pl-72 flex flex-col min-h-screen">
            <div class="sticky top-0 z-40 flex h-20 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white/80 backdrop-blur-xl px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Separator -->
                <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true"></div>

                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1">
                        <h2 class="flex items-center text-xl font-bold tracking-tight text-gray-800">
                            @yield('page-title', 'Tableau de bord')
                        </h2>
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <!-- Notifications -->
                        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500 relative">
                            <span class="sr-only">View notifications</span>
                            <i class="fas fa-bell text-lg"></i>
                            <span class="absolute top-2 right-2 block h-2.5 w-2.5 rounded-full bg-primary ring-2 ring-white animate-pulse"></span>
                        </button>

                        <!-- Separator -->
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200" aria-hidden="true"></div>

                        <!-- Profile dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button type="button" class="-m-1.5 flex items-center p-1.5" @click="open = !open">
                                <span class="sr-only">Open user menu</span>
                                <div class="h-9 w-9 rounded-xl bg-primary-gradient p-[2px] shadow-lg">
                                    <div class="h-full w-full rounded-[10px] bg-white flex items-center justify-center">
                                        <span class="text-primary font-bold text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                    </div>
                                </div>
                                <span class="hidden lg:flex lg:items-center">
                                    <span class="ml-3 text-sm font-semibold leading-6 text-gray-900" aria-hidden="true">{{ Auth::user()->name }}</span>
                                    <i class="fas fa-chevron-down ml-2 text-xs text-gray-400"></i>
                                </span>
                            </button>

                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 z-10 mt-2.5 w-48 origin-top-right rounded-2xl bg-white p-1 shadow-2xl ring-1 ring-gray-900/5 focus:outline-none" 
                                 role="menu" aria-orientation="vertical" x-cloak>
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left group flex items-center px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                                        <i class="fas fa-power-off mr-3 text-red-400 group-hover:text-red-600"></i>
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <main class="py-10 flex-1">
                <div class="px-4 sm:px-6 lg:px-8">
                    @yield('content')
                </div>
            </main>

            <footer class="bg-white border-t border-gray-200 py-6 px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} VoXY Premium Admin. Fait avec <i class="fas fa-heart text-red-500"></i> pour la musique.
            </footer>
        </div>
    </div>
</body>
</html>