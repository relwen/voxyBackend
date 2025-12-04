<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $partition->title }} - VoXY {{ Auth::user()->isAdmin() ? 'Admin' : 'Maestro' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-primary { background: rgb(158, 2, 80); }
        .text-primary { color: rgb(158, 2, 80); }
        .bg-primary-gradient { background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); }
    </style>
</head>
<body class="bg-gray-50">
    @if(Auth::user()->isMaestro())
        @include('components.maestro-sidebar', ['user' => Auth::user(), 'chorale' => Auth::user()->chorale])
    @else
        <!-- Navigation Admin -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-gray-900">VoXY Admin</h1>
                        </div>
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            <a href="{{ route('admin.dashboard') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('admin.users') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Utilisateurs
                            </a>
                            <a href="{{ route('admin.chorales') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Chorales
                            </a>
                            <a href="{{ route('admin.partitions') }}" class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Partitions
                            </a>
                            <a href="{{ route('admin.categories') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Catégories
                            </a>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <span class="text-gray-700 mr-4">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Déconnexion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
    @endif

    <!-- Contenu principal -->
    <div class="{{ Auth::user()->isMaestro() ? 'lg:ml-64' : '' }}">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- En-tête de la partition -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl leading-6 font-medium text-gray-900">{{ $partition->title }}</h3>
                        @if($partition->description)
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $partition->description }}</p>
                        @endif
                        <div class="mt-2 flex items-center space-x-4">
                            @if($partition->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $partition->category->name }}
                                </span>
                            @endif
                            @if($partition->chorale)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $partition->chorale->name }}
                                </span>
                            @endif
                            @if($partition->reference)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $partition->reference->name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('admin.partitions.edit', $partition->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Modifier
                        </a>
                        @if(Auth::user()->isMaestro() && $partition->category)
                            <a href="{{ route('admin.rubriques.show', $partition->category->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                                Retour à la rubrique
                            </a>
                        @else
                            <a href="{{ route('admin.partitions') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                                Retour
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Fichiers unifiés -->
        <x-file-display :files="$partition->files ?? []" :partitionId="$partition->id" />
    </div>

    <!-- Modal pour agrandir les images -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg max-w-4xl max-h-full overflow-auto">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Image</h3>
                <button onclick="closeImageModal()" class="text-gray-400 hover:text-gray-600">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>
            <div class="p-4">
                <img id="modalImage" src="" alt="Image agrandie" class="max-w-full max-h-full">
            </div>
        </div>
    </div>

    <script>
        function openImageModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageModal').classList.remove('hidden');
        }

        function closeImageModal() {
            document.getElementById('imageModal').classList.add('hidden');
        }

        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    </script>
        </div>
    </div>
</body>
</html>
