<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une partition à {{ $reference->name }} - VoXY Admin</title>
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
                        <a href="{{ route('admin.partitions') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Partitions
                        </a>
                        <a href="{{ route('admin.categories') }}" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Catégories
                        </a>
                        <a href="{{ route('admin.messes.index') }}" class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
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
    <div class="max-w-2xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <div class="flex items-center">
                    @if($reference->messe->icon)
                        <span class="text-2xl mr-3">{{ $reference->messe->icon }}</span>
                    @endif
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Ajouter une partition à {{ $reference->name }}</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Section de la messe "{{ $reference->messe->name }}" - Créez une nouvelle partition pour cette section
                        </p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.references.store-partition', $reference->id) }}" enctype="multipart/form-data" class="px-4 py-5 sm:p-6">
                @csrf

                <!-- Titre -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700">Titre de la partition *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('title') border-red-300 @enderror"
                           placeholder="Ex: Kyrié de la Messe St Gabriel">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('description') border-red-300 @enderror"
                              placeholder="Description de cette partition...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catégorie -->
                <div class="mb-6">
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie *</label>
                    <select name="category_id" id="category_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('category_id') border-red-300 @enderror">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Chorale -->
                <div class="mb-6">
                    <label for="chorale_id" class="block text-sm font-medium text-gray-700">Chorale *</label>
                    <select name="chorale_id" id="chorale_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('chorale_id') border-red-300 @enderror">
                        <option value="">Sélectionner une chorale</option>
                        @foreach($chorales as $chorale)
                            <option value="{{ $chorale->id }}" {{ old('chorale_id') == $chorale->id ? 'selected' : '' }}>
                                {{ $chorale->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('chorale_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fichiers Audio -->
                <div class="mb-6">
                    <label for="audio_files" class="block text-sm font-medium text-gray-700">Fichiers Audio</label>
                    <input type="file" name="audio_files[]" id="audio_files" multiple accept="audio/*"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('audio_files.*') border-red-300 @enderror">
                    <p class="mt-2 text-sm text-gray-500">Sélectionnez un ou plusieurs fichiers audio (MP3, WAV, OGG, M4A)</p>
                    @error('audio_files.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fichiers PDF -->
                <div class="mb-6">
                    <label for="pdf_files" class="block text-sm font-medium text-gray-700">Fichiers PDF</label>
                    <input type="file" name="pdf_files[]" id="pdf_files" multiple accept=".pdf"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('pdf_files.*') border-red-300 @enderror">
                    <p class="mt-2 text-sm text-gray-500">Sélectionnez un ou plusieurs fichiers PDF</p>
                    @error('pdf_files.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fichiers Images -->
                <div class="mb-6">
                    <label for="image_files" class="block text-sm font-medium text-gray-700">Fichiers Images</label>
                    <input type="file" name="image_files[]" id="image_files" multiple accept="image/*"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('image_files.*') border-red-300 @enderror">
                    <p class="mt-2 text-sm text-gray-500">Sélectionnez un ou plusieurs fichiers images (JPEG, PNG, JPG, GIF)</p>
                    @error('image_files.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Boutons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.references.partitions', $reference->id) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                        Annuler
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Créer la partition
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
