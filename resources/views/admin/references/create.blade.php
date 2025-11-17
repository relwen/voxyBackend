<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une Référence - VoXY Admin</title>
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
                        <a href="{{ route('admin.references.index') }}" class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Références
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
                <h3 class="text-lg leading-6 font-medium text-gray-900">Créer une nouvelle section</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Ajoutez une nouvelle section à une messe (ex: Kyrié, Gloria, Agnus Dei)</p>
            </div>

            <form method="POST" action="{{ route('admin.references.store') }}" class="px-4 py-5 sm:p-6">
                @csrf

                <!-- Messe -->
                <div class="mb-6">
                    <label for="messe_id" class="block text-sm font-medium text-gray-700">Messe *</label>
                    <select name="messe_id" id="messe_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('messe_id') border-red-300 @enderror">
                        <option value="">Sélectionner une messe</option>
                        @foreach($messes as $messe)
                            <option value="{{ $messe->id }}" {{ old('messe_id', request('messe_id')) == $messe->id ? 'selected' : '' }}>
                                {{ $messe->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('messe_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nom -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nom de la section *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-300 @enderror"
                           placeholder="Ex: Kyrié, Gloria, Agnus Dei, Sanctus">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('description') border-red-300 @enderror"
                              placeholder="Description de cette section...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Position -->
                <div class="mb-6">
                    <label for="order_position" class="block text-sm font-medium text-gray-700">Position d'ordre</label>
                    <input type="number" name="order_position" id="order_position" value="{{ old('order_position') }}" min="1"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('order_position') border-red-300 @enderror"
                           placeholder="1, 2, 3... (pour ordonner les sections)">
                    <p class="mt-2 text-sm text-gray-500">Numéro pour ordonner les sections dans la messe (1 = première, 2 = deuxième, etc.)</p>
                    @error('order_position')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Exemples -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Exemples de références pour une messe :</label>
                    <div class="bg-gray-50 p-4 rounded-md">
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <strong>Kyrié</strong> (position 1) - "Seigneur, prends pitié"</li>
                            <li>• <strong>Gloria</strong> (position 2) - "Gloire à Dieu"</li>
                            <li>• <strong>Sanctus</strong> (position 3) - "Saint, Saint, Saint"</li>
                            <li>• <strong>Agnus Dei</strong> (position 4) - "Agneau de Dieu"</li>
                            <li>• <strong>Credo</strong> (position 5) - "Je crois en Dieu"</li>
                        </ul>
                    </div>
                </div>

                <!-- Boutons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.references.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
                        Annuler
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Créer la section
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
