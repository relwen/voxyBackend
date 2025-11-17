<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer une Vocalise - VoXY Admin</title>
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
                        <a href="{{ route('admin.vocalises.index') }}" class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Éditer la Vocalise</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Modifier les informations de la vocalise</p>
            </div>
            
            <form method="POST" action="{{ route('admin.vocalises.update', $vocalise->id) }}" enctype="multipart/form-data" class="px-4 py-5 sm:p-6">
                @csrf
                
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <!-- Titre -->
                    <div class="sm:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700">Titre *</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $vocalise->title) }}" required
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                    </div>

                    <!-- Description -->
                    <div class="sm:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description', $vocalise->description) }}</textarea>
                    </div>

                    <!-- Chorale -->
                    <div>
                        <label for="chorale_id" class="block text-sm font-medium text-gray-700">Chorale *</label>
                        <select name="chorale_id" id="chorale_id" required
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Sélectionner une chorale</option>
                            @foreach($chorales as $chorale)
                                <option value="{{ $chorale->id }}" {{ old('chorale_id', $vocalise->chorale_id) == $chorale->id ? 'selected' : '' }}>
                                    {{ $chorale->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Partie vocale -->
                    <div>
                        <label for="voice_part" class="block text-sm font-medium text-gray-700">Partie vocale *</label>
                        <select name="voice_part" id="voice_part" required
                                class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Sélectionner une partie vocale</option>
                            @foreach($voiceParts as $part)
                                <option value="{{ $part }}" {{ old('voice_part', $vocalise->voice_part) == $part ? 'selected' : '' }}>
                                    {{ $part }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fichier audio actuel -->
                    @if($vocalise->audio_path)
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Audio actuel</label>
                        <div class="mt-1 p-3 bg-gray-50 rounded-md">
                            <audio controls class="w-full">
                                <source src="{{ $vocalise->audio_url }}" type="audio/mpeg">
                                Votre navigateur ne supporte pas l'élément audio.
                            </audio>
                            <p class="mt-2 text-sm text-gray-500">Fichier actuel : {{ basename($vocalise->audio_path) }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Nouveau fichier audio -->
                    <div class="sm:col-span-2">
                        <label for="audio_file" class="block text-sm font-medium text-gray-700">
                            {{ $vocalise->audio_path ? 'Remplacer le fichier audio' : 'Fichier audio' }}
                        </label>
                        <input type="file" name="audio_file" id="audio_file" accept="audio/*"
                               class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        <p class="mt-1 text-sm text-gray-500">Formats acceptés : MP3, WAV, OGG, M4A (max 10MB)</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <a href="{{ route('admin.vocalises.index') }}" 
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                        Annuler
                    </a>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 