<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une Partition - VoXY Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Créer une nouvelle partition</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Ajoutez une nouvelle partition avec ses fichiers associés</p>
            </div>

            <form method="POST" action="{{ route('admin.partitions.store') }}" enctype="multipart/form-data" class="px-4 py-5 sm:p-6">
                @csrf

                <!-- Titre -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700">Titre *</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('title') border-red-300 @enderror">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catégorie -->
                <div class="mb-6">
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Catégorie *</label>
                    <select name="category_id" id="category_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('category_id') border-red-300 @enderror"
                            onchange="toggleMesseSelector(this)">
                        <option value="">Sélectionner une catégorie</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    data-name="{{ strtolower($category->name) }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Messe (affiché dynamiquement si catégorie = Messe) -->
                <div class="mb-6" id="messe_selector" style="display: none;">
                    <label for="messe_id" class="block text-sm font-medium text-gray-700">Messe</label>
                    <select name="messe_id" id="messe_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('messe_id') border-red-300 @enderror">
                        <option value="">Sélectionner une messe</option>
                        @foreach($messes as $messe)
                            <option value="{{ $messe->id }}" {{ old('messe_id') == $messe->id ? 'selected' : '' }}>
                                {{ $messe->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('messe_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Chorale -->
                <div class="mb-6">
                    <label for="chorale_id" class="block text-sm font-medium text-gray-700">Chorale *</label>
                    <select name="chorale_id" id="chorale_id" required
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('chorale_id') border-red-300 @enderror"
                            onchange="loadPupitres(this.value)">
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

                <!-- Pupitre (chargé dynamiquement selon la chorale) -->
                <div class="mb-6">
                    <label for="pupitre_id" class="block text-sm font-medium text-gray-700">Pupitre</label>
                    <select name="pupitre_id" id="pupitre_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('pupitre_id') border-red-300 @enderror">
                        <option value="">Sélectionner d'abord une chorale</option>
                        @if(old('chorale_id'))
                            @php
                                $selectedChorale = \App\Models\Chorale::find(old('chorale_id'));
                                $choralePupitres = $selectedChorale ? $selectedChorale->pupitres : collect();
                            @endphp
                            @foreach($choralePupitres as $pupitre)
                                <option value="{{ $pupitre->id }}" {{ old('pupitre_id') == $pupitre->id ? 'selected' : '' }}>
                                    {{ $pupitre->nom }}@if($pupitre->is_default) (Par défaut)@endif
                                </option>
                            @endforeach
                        @endif
                    </select>
                    <p class="mt-2 text-sm text-gray-500">Sélectionnez le pupitre concerné. Le pupitre par défaut sera utilisé si aucun n'est sélectionné.</p>
                    @error('pupitre_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Fichiers (tous types) -->
                <div class="mb-6">
                    <label for="files" class="block text-sm font-medium text-gray-700">
                        Fichiers <span class="text-blue-600">(Vous pouvez sélectionner plusieurs fichiers)</span>
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-blue-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="files" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Sélectionner des fichiers</span>
                                    <input type="file" name="files[]" id="files" multiple
                                           class="sr-only"
                                           onchange="updateFileList(this)">
                                </label>
                                <p class="pl-1">ou glissez-déposez</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                Formats: Audio (MP3, WAV, OGG, M4A), PDF, Images (JPEG, PNG, GIF, WEBP), Vidéos (MP4, AVI), Documents (DOC, DOCX, XLS, XLSX)
                            </p>
                            <p class="text-xs text-gray-500">Max 20MB par fichier</p>
                        </div>
                    </div>
                    <div id="fileList" class="mt-3 space-y-2"></div>
                    @error('files.*')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <script>
                function updateFileList(input) {
                    const fileList = document.getElementById('fileList');
                    fileList.innerHTML = '';
                    
                    if (input.files.length > 0) {
                        const list = document.createElement('ul');
                        list.className = 'bg-gray-50 rounded-md p-3 space-y-2';
                        
                        for (let i = 0; i < input.files.length; i++) {
                            const file = input.files[i];
                            const li = document.createElement('li');
                            li.className = 'flex items-center justify-between text-sm';
                            
                            const fileInfo = document.createElement('span');
                            fileInfo.className = 'text-gray-700';
                            fileInfo.innerHTML = `<i class="fas fa-file mr-2"></i>${file.name} <span class="text-gray-500">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>`;
                            
                            li.appendChild(fileInfo);
                            list.appendChild(li);
                        }
                        
                        fileList.appendChild(list);
                    }
                }

                function toggleMesseSelector(select) {
                    const messeSelector = document.getElementById('messe_selector');
                    const selectedOption = select.options[select.selectedIndex];
                    const categoryName = selectedOption ? selectedOption.getAttribute('data-name') : '';
                    
                    // Afficher le sélecteur de messe si la catégorie est "Messe" ou "Messes"
                    if (categoryName && (categoryName.includes('messe') || categoryName.includes('messes'))) {
                        messeSelector.style.display = 'block';
                        document.getElementById('messe_id').setAttribute('required', 'required');
                    } else {
                        messeSelector.style.display = 'none';
                        document.getElementById('messe_id').removeAttribute('required');
                        document.getElementById('messe_id').value = '';
                    }
                }

                // Vérifier au chargement de la page si une catégorie est déjà sélectionnée
                document.addEventListener('DOMContentLoaded', function() {
                    const categorySelect = document.getElementById('category_id');
                    if (categorySelect.value) {
                        toggleMesseSelector(categorySelect);
                    }
                    
                    // Charger les pupitres si une chorale est déjà sélectionnée
                    const choraleSelect = document.getElementById('chorale_id');
                    if (choraleSelect.value) {
                        loadPupitres(choraleSelect.value);
                    }
                });

                // Charger les pupitres d'une chorale
                function loadPupitres(choraleId) {
                    const pupitreSelect = document.getElementById('pupitre_id');
                    pupitreSelect.innerHTML = '<option value="">Chargement...</option>';
                    
                    if (!choraleId) {
                        pupitreSelect.innerHTML = '<option value="">Sélectionner d\'abord une chorale</option>';
                        return;
                    }
                    
                    fetch(`/api/chorales/${choraleId}/pupitres`)
                        .then(res => res.json())
                        .then(data => {
                            pupitreSelect.innerHTML = '<option value="">Sélectionner un pupitre (optionnel)</option>';
                            if (data.success && data.data) {
                                data.data.forEach(pupitre => {
                                    const option = document.createElement('option');
                                    option.value = pupitre.id;
                                    option.textContent = pupitre.nom + (pupitre.is_default ? ' (Par défaut)' : '');
                                    if (pupitre.is_default) {
                                        option.selected = true;
                                    }
                                    pupitreSelect.appendChild(option);
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Erreur lors du chargement des pupitres:', err);
                            pupitreSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                        });
                }
                </script>

                <!-- Boutons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.partitions') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-md text-sm font-medium">
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
