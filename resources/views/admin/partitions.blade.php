<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Partitions - VoXY Admin</title>
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
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-chart-line w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                
                <a href="{{ route('admin.users') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-users w-5 h-5 mr-3"></i>
                    Utilisateurs
                </a>
                
                <a href="{{ route('admin.chorales') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                    <i class="fas fa-music w-5 h-5 mr-3"></i>
                    Chorales
                </a>
                
                <a href="{{ route('admin.partitions') }}" 
                   class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors bg-gray-100 text-primary border-r-4 border-primary">
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
                    <h2 class="ml-4 lg:ml-0 text-2xl font-bold text-gray-800">Gestion des Partitions</h2>
                </div>
                
                <div class="flex items-center space-x-4">
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
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-lg rounded-xl border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Liste des Partitions</h3>
                        <p class="text-sm text-gray-600">Créer et gérer les partitions</p>
                    </div>
                    <a href="{{ route('admin.partitions.create') }}" class="relative z-10 bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 shadow-md hover:shadow-lg inline-flex items-center cursor-pointer">
                        <i class="fas fa-plus mr-2"></i>Nouvelle Partition
                    </a>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Partition</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Chorale</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Catégorie</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fichiers</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($partitions as $partition)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center">
                                            <i class="fas fa-file-music text-white"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $partition->title }}</div>
                                            @if($partition->description)
                                                <div class="text-xs text-gray-500">{{ Str::limit($partition->description, 50) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $partition->chorale->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($partition->category)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            {{ $partition->category->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @php
                                            $filesByType = $partition->files_by_type ?? [];
                                        @endphp
                                        @foreach($filesByType as $type => $files)
                                            @if(count($files) > 0)
                                                @php
                                                    $firstFile = $files[0];
                                                    $icon = $firstFile['icon'] ?? 'fa-file';
                                                    $colorClass = $firstFile['color_class'] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $colorClass }}">
                                                    <i class="fas {{ $icon }} mr-1"></i>{{ count($files) }}
                                                </span>
                                            @endif
                                        @endforeach
                                        @if(empty($filesByType) || (isset($filesByType['audio']) && count($filesByType['audio']) == 0 && isset($filesByType['pdf']) && count($filesByType['pdf']) == 0 && isset($filesByType['image']) && count($filesByType['image']) == 0))
                                            <span class="text-gray-400 text-xs">Aucun fichier</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.partitions.show', $partition->id) }}" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                            <i class="fas fa-eye mr-1"></i>Voir
                                        </a>
                                        <a href="{{ route('admin.partitions.edit', $partition->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                            <i class="fas fa-edit mr-1"></i>Éditer
                                        </a>
                                        <form method="POST" action="{{ route('admin.partitions.delete', $partition->id) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette partition ?')">
                                            @csrf
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg text-xs font-medium transition-colors">
                                                <i class="fas fa-trash mr-1"></i>Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $partitions->links() }}
                </div>
            </div>
        </main>
    </div>
</body>
</html>