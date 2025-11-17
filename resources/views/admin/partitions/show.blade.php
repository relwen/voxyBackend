<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $partition->title }} - VoXY Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
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
                        <a href="{{ route('admin.messes.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Messes
                        </a>
                        <a href="{{ route('admin.vocalises.index') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Vocalises
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

    <!-- Contenu principal -->
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
                        <a href="{{ route('admin.partitions') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fichiers audio -->
        @if(count($partition->audio_files ?? []) > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                    <span class="text-2xl mr-2">🎵</span>
                    Fichiers Audio ({{ count($partition->audio_files) }})
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    @foreach($partition->audio_files as $index => $audioFile)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900">Audio {{ $index + 1 }}</h4>
                            <a href="{{ route('files.serve', [$partition->id, 'audio', $index]) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm" target="_blank">
                                Télécharger
                            </a>
                        </div>
                        <audio controls class="w-full">
                            <source src="{{ route('files.serve', [$partition->id, 'audio', $index]) }}" type="audio/mpeg">
                            <source src="{{ route('files.serve', [$partition->id, 'audio', $index]) }}" type="audio/wav">
                            <source src="{{ route('files.serve', [$partition->id, 'audio', $index]) }}" type="audio/ogg">
                            Votre navigateur ne supporte pas l'élément audio.
                        </audio>
                        <p class="text-xs text-gray-500 mt-2">{{ basename($audioFile) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Fichiers PDF -->
        @if(count($partition->pdf_files ?? []) > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                    <span class="text-2xl mr-2">📄</span>
                    Fichiers PDF ({{ count($partition->pdf_files) }})
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    @foreach($partition->pdf_files as $index => $pdfFile)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900">PDF {{ $index + 1 }}</h4>
                            <div class="flex space-x-2">
                                <a href="{{ route('files.serve', [$partition->id, 'pdf', $index]) }}" 
                                   class="text-blue-600 hover:text-blue-800 text-sm" target="_blank">
                                    Voir
                                </a>
                                <a href="{{ route('files.serve', [$partition->id, 'pdf', $index]) }}" 
                                   class="text-green-600 hover:text-green-800 text-sm" download>
                                    Télécharger
                                </a>
                            </div>
                        </div>
                        <div class="aspect-w-16 aspect-h-20 bg-gray-200 rounded">
                            <iframe src="{{ route('files.serve', [$partition->id, 'pdf', $index]) }}" 
                                    class="w-full h-48 rounded" frameborder="0"></iframe>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">{{ basename($pdfFile) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Fichiers images -->
        @if(count($partition->image_files ?? []) > 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                    <span class="text-2xl mr-2">🖼️</span>
                    Images ({{ count($partition->image_files) }})
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 p-6">
                    @foreach($partition->image_files as $index => $imageFile)
                    <div class="bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-900">Image {{ $index + 1 }}</h4>
                            <div class="flex space-x-2">
                                <button onclick="openImageModal('{{ route('files.serve', [$partition->id, 'image', $index]) }}')" 
                                        class="text-blue-600 hover:text-blue-800 text-sm">
                                    Agrandir
                                </button>
                                <a href="{{ route('files.serve', [$partition->id, 'image', $index]) }}" 
                                   class="text-green-600 hover:text-green-800 text-sm" download>
                                    Télécharger
                                </a>
                            </div>
                        </div>
                        <div class="aspect-w-16 aspect-h-12 bg-gray-200 rounded overflow-hidden">
                            <img src="{{ route('files.serve', [$partition->id, 'image', $index]) }}" 
                                 alt="Image {{ $index + 1 }}" 
                                 class="w-full h-32 object-cover rounded cursor-pointer"
                                 onclick="openImageModal('{{ route('files.serve', [$partition->id, 'image', $index]) }}')">
                        </div>
                        <p class="text-xs text-gray-500 mt-2">{{ basename($imageFile) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Message si aucun fichier -->
        @if(count($partition->audio_files ?? []) == 0 && count($partition->pdf_files ?? []) == 0 && count($partition->image_files ?? []) == 0)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="text-center py-12">
                <div class="text-gray-400 text-6xl mb-4">📁</div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun fichier</h3>
                <p class="text-gray-500 mb-4">Cette partition n'a pas encore de fichiers associés.</p>
                <a href="{{ route('admin.partitions.edit', $partition->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Ajouter des fichiers
                </a>
            </div>
        </div>
        @endif
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
</body>
</html>
