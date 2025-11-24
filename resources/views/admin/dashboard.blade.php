<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VoXY Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-primary { background: rgb(158, 2, 80); }
        .bg-primary-gradient { background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); }
        .text-primary { color: rgb(158, 2, 80); }
        .border-primary { border-color: rgb(158, 2, 80); }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0" 
         :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        
        <!-- Logo -->
        <div class="flex items-center justify-center h-16 bg-primary-gradient">
            <h1 class="text-xl font-bold text-white">VoXY Admin</h1>
        </div>
        
        <!-- Navigation -->
        <nav class="mt-8">
            <div class="px-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors bg-gray-100 text-primary border-r-4 border-primary">
                    <i class="fas fa-chart-line w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('admin.users') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    Utilisateurs
                    @if($stats['pending_users'] > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $stats['pending_users'] }}</span>
                    @endif
                </a>
                
                <a href="{{ route('admin.chorales') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-music w-5 h-5 mr-3"></i>
                    Chorales
                </a>
                
                <a href="{{ route('admin.partitions') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-file-music w-5 h-5 mr-3"></i>
                    Partitions
                </a>
                
                <a href="{{ route('admin.messes.index') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-church w-5 h-5 mr-3"></i>
                    Messes
                </a>
                
                <a href="{{ route('admin.vocalises.index') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-microphone w-5 h-5 mr-3"></i>
                    Vocalises
                </a>
                
                <a href="{{ route('admin.categories') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-tags w-5 h-5 mr-3"></i>
                    Catégories
                </a>
            </div>
        </nav>
    </div>
    
    <!-- Overlay mobile -->
    <div x-show="sidebarOpen" 
         class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
         @click="sidebarOpen = false"></div>
    
    <!-- Contenu principal -->
    <div class="lg:ml-64">
        <!-- Navbar -->
        <header class="bg-white shadow-sm border-b">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-600 hover:text-gray-900">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h2 class="ml-4 lg:ml-0 text-2xl font-bold text-gray-800">Dashboard</h2>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900">
                            <i class="fas fa-bell text-lg"></i>
                            @if($stats['pending_users'] > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ $stats['pending_users'] }}</span>
                            @endif
                        </button>
                    </div>
                    
                    <!-- Profil -->
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                            <span class="text-white text-sm font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                        </div>
                        <span class="font-medium text-gray-700">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenu -->
        <main class="p-6">
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

            <!-- Statistiques -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-users text-blue-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Utilisateurs</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">En attente</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-music text-green-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Chorales</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_chorales'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-music text-purple-600 text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Partitions</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_partitions'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Utilisateurs récents -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Utilisateurs récents</h3>
                    <p class="text-sm text-gray-600">Les derniers utilisateurs inscrits</p>
                </div>
                <div class="divide-y divide-gray-200">
                    @foreach($recentUsers as $user)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-medium">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                @if($user->chorale)
                                    <div class="text-xs text-gray-400">{{ $user->chorale->name }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                @if($user->status === 'approved') bg-green-100 text-green-800
                                @elseif($user->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                @if($user->status === 'approved') Approuvé
                                @elseif($user->status === 'pending') En attente
                                @else Rejeté @endif
                            </span>
                            @if($user->role === 'admin')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    Admin
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <a href="{{ route('admin.users') }}" class="text-primary hover:text-primary font-medium text-sm">
                        Voir tous les utilisateurs →
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>