<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - VoXY Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-primary-gradient { 
            background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); 
        }
        .text-primary { 
            color: rgb(158, 2, 80); 
        }
        .border-primary { 
            border-color: rgb(158, 2, 80); 
        }
    </style>
</head>
<body class="bg-gray-50">
    @php
        $user = Auth::user();
        $chorale = $user->chorale;
    @endphp

    @include('components.maestro-sidebar', ['user' => $user, 'chorale' => $chorale])

    <div class="lg:ml-64">
        <!-- Navbar -->
        <header class="bg-white shadow-sm border-b">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-users text-primary mr-2"></i>Gestion des Utilisateurs
                    </h2>
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

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-lg rounded-xl border border-gray-100">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Utilisateurs de la chorale : {{ $chorale->name ?? 'Non définie' }}</h3>
                            <p class="text-sm text-gray-600">Approuver, rejeter et gérer les utilisateurs de votre chorale</p>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Utilisateur</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Partie vocale</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Statut</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">État</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rôle</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $userItem)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-primary-gradient rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-sm font-medium">{{ substr($userItem->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-semibold text-gray-900">{{ $userItem->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $userItem->email }}</div>
                                            @if($userItem->phone)
                                                <div class="text-xs text-gray-400">{{ $userItem->phone }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $userItem->voice_part ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($userItem->status === 'approved') bg-green-100 text-green-800
                                        @elseif($userItem->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        @if($userItem->status === 'approved') Approuvé
                                        @elseif($userItem->status === 'pending') En attente
                                        @else Rejeté @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($userItem->is_active) bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        @if($userItem->is_active) Actif @else Inactif @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                        @if($userItem->role === 'admin') bg-purple-100 text-purple-800
                                        @elseif($userItem->role === 'maestro') bg-blue-100 text-blue-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        @if($userItem->role === 'admin') Admin
                                        @elseif($userItem->role === 'maestro') Maestro
                                        @else Utilisateur @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex flex-wrap gap-2">
                                        @if($userItem->status === 'pending')
                                            <form method="POST" action="{{ route('admin.maestro.users.approve', $userItem->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors shadow-sm">
                                                    <i class="fas fa-check mr-1"></i>Approuver
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.maestro.users.reject', $userItem->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors shadow-sm" onclick="return confirm('Êtes-vous sûr de vouloir rejeter cet utilisateur ?')">
                                                    <i class="fas fa-times mr-1"></i>Rejeter
                                                </button>
                                            </form>
                                        @endif
                                        
                                        @if($userItem->id !== Auth::id())
                                            <form method="POST" action="{{ route('admin.maestro.users.delete', $userItem->id) }}" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.')">
                                                @csrf
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors shadow-sm">
                                                    <i class="fas fa-trash mr-1"></i>Supprimer
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <i class="fas fa-users text-6xl mb-4 text-gray-300"></i>
                                        <p class="text-lg font-medium text-gray-500">Aucun utilisateur dans votre chorale</p>
                                        <p class="text-sm text-gray-400 mt-2">Les utilisateurs qui s'inscrivent à votre chorale apparaîtront ici</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($users->hasPages())
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>
        </main>
    </div>
</body>
</html>
