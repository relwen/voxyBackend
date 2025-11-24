<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VoXY Admin')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .form-input {
            transition: all 0.3s ease;
        }
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-item:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex h-screen bg-gray-50">
        <!-- Sidebar -->
        <div class="hidden md:flex md:w-64 md:flex-col">
            <div class="flex flex-col flex-grow pt-5 overflow-y-auto gradient-bg">
                <div class="flex items-center flex-shrink-0 px-4">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center">
                            <i class="fas fa-music text-purple-600 text-lg"></i>
                        </div>
                        <h1 class="ml-3 text-xl font-bold text-white">VoXY Admin</h1>
                    </div>
                </div>
                <div class="mt-8 flex-grow flex flex-col">
                    <nav class="flex-1 px-2 pb-4 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="sidebar-item group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('admin.dashboard') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-tachometer-alt mr-3 text-white"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('admin.users') }}" 
                           class="sidebar-item group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('admin.users*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-users mr-3 text-white"></i>
                            Utilisateurs
                        </a>
                        <a href="{{ route('admin.chorales') }}" 
                           class="sidebar-item group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('admin.chorales*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-users-line mr-3 text-white"></i>
                            Chorales
                        </a>
                        <a href="{{ route('admin.partitions') }}" 
                           class="sidebar-item group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('admin.partitions*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-file-music mr-3 text-white"></i>
                            Partitions
                        </a>
                        <a href="{{ route('admin.categories') }}" 
                           class="sidebar-item group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('admin.categories*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-tags mr-3 text-white"></i>
                            Catégories
                        </a>
                        <a href="{{ route('admin.messes.index') }}" 
                           class="sidebar-item group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('admin.messes*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-church mr-3 text-white"></i>
                            Messes
                        </a>
                        <a href="{{ route('admin.vocalises.index') }}" 
                           class="sidebar-item group flex items-center px-2 py-2 text-sm font-medium rounded-md text-white hover:bg-white hover:bg-opacity-20 {{ request()->routeIs('admin.vocalises*') ? 'bg-white bg-opacity-20' : '' }}">
                            <i class="fas fa-microphone mr-3 text-white"></i>
                            Vocalises
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top navigation -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <button class="md:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                                <i class="fas fa-bars text-lg"></i>
                            </button>
                            <h2 class="ml-4 text-xl font-semibold text-gray-900">@yield('page-title', 'Dashboard')</h2>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <span class="text-gray-700 font-medium">{{ Auth::user()->name }}</span>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 hover:shadow-lg">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <!-- Messages de succès/erreur -->
                    @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <button @click="show = false" class="text-green-400 hover:text-green-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                                </div>
                                <div class="ml-auto pl-3">
                                    <button @click="show = false" class="text-red-400 hover:text-red-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Erreurs de validation :</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="ml-auto pl-3">
                                    <button @click="show = false" class="text-red-400 hover:text-red-600">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('[x-data*="show: true"]');
            alerts.forEach(alert => {
                if (alert.__x) {
                    alert.__x.$data.show = false;
                }
            });
        }, 5000);

        // Form validation enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
                inputs.forEach(input => {
                    input.addEventListener('blur', function() {
                        if (this.value.trim() === '') {
                            this.classList.add('border-red-300');
                            this.classList.remove('border-gray-300');
                        } else {
                            this.classList.remove('border-red-300');
                            this.classList.add('border-gray-300');
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>