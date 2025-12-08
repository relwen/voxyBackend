<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration de la Chorale - VoXY Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .bg-primary { background: rgb(158, 2, 80); }
        .bg-primary-gradient { background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); }
        .text-primary { color: rgb(158, 2, 80); }
        .border-primary { border-color: rgb(158, 2, 80); }
        .material-icons {
            font-family: 'Material Icons';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ 
    activeTab: 'pupitres',
    editingPupitre: null,
    editingCategory: null,
    showPupitreModal: false,
    showCategoryModal: false,
    showTemplateModal: false
}" 
     @open-category-modal.window="showCategoryModal = true"
     x-init="
        $watch('showCategoryModal', value => {
            if (value && !editingCategory) {
                setTimeout(() => selectIcon('music_note', 'music_note'), 100);
            }
        })
     ">
    @include('components.maestro-sidebar', ['user' => Auth::user(), 'chorale' => Auth::user()->chorale])

    <!-- Contenu principal -->
    <div class="lg:ml-64">
        <header class="bg-white shadow-sm border-b">
            <div class="flex items-center justify-between px-6 py-4">
                <h2 class="text-2xl font-bold text-gray-800">Configuration de la Chorale</h2>
                <div class="flex items-center space-x-4">
                    <span class="font-medium text-gray-700">{{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i>Déconnexion
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Onglets -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-100 mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button @click="activeTab = 'pupitres'" 
                                :class="activeTab === 'pupitres' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-4 px-6 border-b-2 font-medium text-sm">
                            <i class="fas fa-users mr-2"></i>Pupitres
                        </button>
                        <button @click="activeTab = 'rubriques'" 
                                :class="activeTab === 'rubriques' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="py-4 px-6 border-b-2 font-medium text-sm">
                            <i class="fas fa-tags mr-2"></i>Rubriques
                        </button>
                    </nav>
                </div>

                <!-- Contenu des onglets -->
                <div class="p-6">
                    <!-- Onglet Pupitres -->
                    <div x-show="activeTab === 'pupitres'">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Gestion des Pupitres</h3>
                            <button @click="showPupitreModal = true; editingPupitre = null" 
                                    class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>Ajouter un pupitre
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="pupitres-list">
                            @foreach($pupitres as $pupitre)
                            <div class="bg-gray-50 rounded-lg p-4 border-2 {{ $pupitre->is_default ? 'border-primary' : 'border-gray-200' }}">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        @if($pupitre->icon)
                                            <i class="fas fa-{{ $pupitre->icon }} mr-2 text-xl" style="color: {{ $pupitre->color ?? '#666' }}"></i>
                                        @endif
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $pupitre->nom }}</h4>
                                        @if($pupitre->is_default)
                                            <span class="ml-2 px-2 py-1 bg-primary text-white text-xs rounded">Par défaut</span>
                                        @endif
                                    </div>
                                    <div class="flex space-x-2">
                                        <button @click="editPupitre({{ $pupitre->id }})" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deletePupitre({{ $pupitre->id }})" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @if($pupitre->description)
                                    <p class="text-sm text-gray-600 mb-2">{{ $pupitre->description }}</p>
                                @endif
                                <div class="text-xs text-gray-500">
                                    Ordre: {{ $pupitre->order }}
                                </div>
                            </div>
                            @endforeach
                            
                            @if($pupitres->isEmpty())
                            <div class="col-span-full text-center py-12">
                                <div class="text-gray-400 text-6xl mb-4"><i class="fas fa-users"></i></div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun pupitre</h3>
                                <p class="text-gray-500 mb-4">Commencez par ajouter des pupitres ou appliquez un template de base.</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Onglet Rubriques -->
                    <div x-show="activeTab === 'rubriques'">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900">Gestion des Rubriques</h3>
                            <button @click="showCategoryModal = true; editingCategory = null; setTimeout(() => { window.editingCategoryId = null; selectIcon('music_note', 'music_note'); }, 100)" 
                                    class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>Ajouter une rubrique
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="categories-list">
                            @foreach($categories as $category)
                            <div class="bg-gray-50 rounded-lg p-4 border-2 border-gray-200">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center">
                                        @if($category->icon)
                                            <i class="fas fa-{{ $category->icon }} mr-2 text-xl" style="color: {{ $category->color ?? '#666' }}"></i>
                                        @endif
                                        <h4 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h4>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button @click="editCategory({{ $category->id }})" class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteCategory({{ $category->id }})" class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @if($category->description)
                                    <p class="text-sm text-gray-600">{{ $category->description }}</p>
                                @endif
                            </div>
                            @endforeach
                            
                            @if($categories->isEmpty())
                            <div class="col-span-full text-center py-12">
                                <div class="text-gray-400 text-6xl mb-4"><i class="fas fa-tags"></i></div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune rubrique</h3>
                                <p class="text-gray-500 mb-4">Commencez par ajouter des rubriques ou appliquez un template de base.</p>
                            </div>
                            @else
                                @foreach($categories as $category)
                                <div class="bg-gray-50 rounded-lg p-4 border-2 border-gray-200 hover:border-primary transition-colors">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center">
                                            @if($category->icon)
                                                <span class="material-icons mr-2 text-xl" style="color: {{ $category->color ?? '#666' }}">{{ $category->icon }}</span>
                                            @else
                                                <span class="material-icons mr-2 text-xl" style="color: {{ $category->color ?? '#666' }}">folder</span>
                                            @endif
                                            <div>
                                                <h4 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h4>
                                                @if($category->sections->count() > 0)
                                                    <p class="text-xs text-gray-500 mt-1">{{ $category->sections->count() }} section(s)</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.rubriques.show', $category->id) }}" 
                                               class="text-blue-600 hover:text-blue-800" title="Voir les sections">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button @click="editCategory({{ $category->id }})" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button @click="deleteCategory({{ $category->id }})" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    @if($category->description)
                                        <p class="text-sm text-gray-600 mb-2">{{ $category->description }}</p>
                                    @endif
                                    <a href="{{ route('admin.rubriques.show', $category->id) }}" 
                                       class="text-sm text-primary hover:underline inline-flex items-center">
                                        <i class="fas fa-arrow-right mr-1"></i>Gérer les sections
                                    </a>
                                </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- Modal pour ajouter/modifier un pupitre -->
    <div x-show="showPupitreModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
         @click.self="showPupitreModal = false">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <span x-text="editingPupitre ? 'Modifier le pupitre' : 'Ajouter un pupitre'"></span>
            </h3>
            <form id="pupitre-form" @submit.prevent="savePupitre()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" name="nom" id="pupitre-nom" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="pupitre-description" rows="2"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
                            <input type="color" name="color" id="pupitre-color"
                                   class="w-full h-10 border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icône (Font Awesome)</label>
                            <input type="text" name="icon" id="pupitre-icon" placeholder="ex: music, users"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ordre</label>
                        <input type="number" name="order" id="pupitre-order" value="0" min="0"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_default" id="pupitre-is-default"
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="pupitre-is-default" class="ml-2 block text-sm text-gray-700">
                            Définir comme pupitre par défaut (Tutti)
                        </label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="showPupitreModal = false" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:opacity-90">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal pour ajouter/modifier une rubrique -->
    <div x-show="showCategoryModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto"
         @click.self="showCategoryModal = false">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 my-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <span x-text="editingCategory ? 'Modifier la rubrique' : 'Ajouter une rubrique'"></span>
            </h3>
            <form id="category-form" @submit.prevent="saveCategory()">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                        <input type="text" name="name" id="category-name" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" id="category-description" rows="2"
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type de structure *</label>
                        <select name="structure_type" id="category-structure-type" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                onchange="updateStructureHelp()">
                            <option value="simple">Simple (partitions directes, pas de sections)</option>
                            <option value="with_sections">Avec sections (rubrique → sections → partitions)</option>
                            <option value="with_dossiers">Avec dossiers (rubrique → dossiers → sections → partitions)</option>
                        </select>
                        <p id="structure-help" class="mt-1 text-xs text-gray-500">
                            <span id="structure-help-text">Les partitions peuvent être créées directement dans cette rubrique.</span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
                        <input type="color" name="color" id="category-color"
                               class="w-full h-10 border-gray-300 rounded-md">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Icône Material Icons *</label>
                        <div class="border border-gray-300 rounded-md p-3 bg-gray-50">
                            <div id="icon-preview" class="mb-4 p-4 bg-white rounded border-2 border-primary flex items-center justify-center" style="min-height: 80px;">
                                <span id="selected-icon-preview" class="text-5xl text-primary">
                                    <span class="material-icons">music_note</span>
                                </span>
                                <input type="hidden" name="icon" id="category-icon" value="music_note" required>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                @php
                                    $iconHelper = new \App\Helpers\IconHelper();
                                    $iconsByCategory = $iconHelper::getIconsByCategory();
                                @endphp
                                @foreach($iconsByCategory as $category => $icons)
                                    <div class="mb-4">
                                        <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $category }}</h4>
                                        <div class="grid grid-cols-6 gap-2">
                                            @foreach($icons as $key => $icon)
                                                <button type="button" 
                                                        onclick="selectIcon('{{ $key }}', '{{ $icon['icon'] }}')"
                                                        class="icon-option p-3 border-2 border-gray-200 rounded-lg hover:border-primary hover:bg-blue-50 transition-all flex flex-col items-center justify-center group"
                                                        data-icon-key="{{ $key }}"
                                                        title="{{ $icon['name'] }}">
                                                    <span class="material-icons text-2xl text-gray-600 group-hover:text-primary">{{ $icon['icon'] }}</span>
                                                    <span class="text-xs text-gray-500 mt-1 hidden group-hover:block">{{ Str::limit($icon['name'], 10) }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Sélectionnez une icône qui sera utilisée dans l'app mobile</p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" @click="showCategoryModal = false" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:opacity-90">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script>
        // Fonctions pour gérer les pupitres
        function editPupitre(id) {
            fetch(`/admin/chorale/pupitres/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('pupitre-nom').value = data.nom;
                    document.getElementById('pupitre-description').value = data.description || '';
                    document.getElementById('pupitre-color').value = data.color || '#666666';
                    document.getElementById('pupitre-icon').value = data.icon || '';
                    document.getElementById('pupitre-order').value = data.order || 0;
                    document.getElementById('pupitre-is-default').checked = data.is_default || false;
                    window.editingPupitreId = id;
                    window.showPupitreModal = true;
                });
        }

        function deletePupitre(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer ce pupitre ?')) return;
            
            fetch(`/admin/chorale/pupitres/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la suppression');
                }
            });
        }

        function savePupitre() {
            const form = document.getElementById('pupitre-form');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            data.is_default = document.getElementById('pupitre-is-default').checked;
            
            const url = window.editingPupitreId 
                ? `/admin/chorale/pupitres/${window.editingPupitreId}`
                : '/admin/chorale/pupitres';
            const method = window.editingPupitreId ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de l\'enregistrement');
                }
            });
        }

        // Fonctions pour gérer les rubriques
        function editCategory(id) {
            fetch(`/admin/chorale/categories/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('category-name').value = data.name;
                    document.getElementById('category-description').value = data.description || '';
                    document.getElementById('category-color').value = data.color || '#666666';
                    if (data.icon) {
                        selectIcon(data.icon, data.icon);
                    } else {
                        selectIcon('music_note', 'music_note');
                    }
                    window.editingCategoryId = id;
                    // Utiliser Alpine.js pour ouvrir le modal
                    const event = new CustomEvent('open-category-modal');
                    document.dispatchEvent(event);
                    setTimeout(() => {
                        const modal = document.querySelector('[x-show="showCategoryModal"]');
                        if (modal) {
                            modal.setAttribute('x-show', 'true');
                            modal.style.display = 'flex';
                        }
                    }, 100);
                });
        }

        function deleteCategory(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette rubrique ?')) return;
            
            fetch(`/admin/chorale/categories/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la suppression');
                }
            });
        }

        function saveCategory() {
            const form = document.getElementById('category-form');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            // Ajouter structure_config vide pour l'instant
            data.structure_config = {};
            
            const url = window.editingCategoryId 
                ? `/admin/chorale/categories/${window.editingCategoryId}`
                : '/admin/chorale/categories';
            const method = window.editingCategoryId ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de l\'enregistrement');
                }
            });
        }


        function selectIcon(iconKey, iconName) {
            // Mettre à jour le champ caché
            document.getElementById('category-icon').value = iconKey;
            
            // Mettre à jour l'aperçu
            const preview = document.getElementById('selected-icon-preview');
            preview.innerHTML = `<span class="material-icons">${iconName}</span>`;
            
            // Mettre à jour le style des boutons
            document.querySelectorAll('.icon-option').forEach(btn => {
                btn.classList.remove('border-primary', 'bg-blue-100');
                btn.classList.add('border-gray-200');
            });
            
            // Mettre en évidence l'icône sélectionnée
            const selectedBtn = document.querySelector(`[data-icon-key="${iconKey}"]`);
            if (selectedBtn) {
                selectedBtn.classList.remove('border-gray-200');
                selectedBtn.classList.add('border-primary', 'bg-blue-100');
            }
        }
        
        // Réinitialiser le modal quand il s'ouvre
        document.addEventListener('DOMContentLoaded', function() {
            // Observer les changements du modal
            const modal = document.querySelector('[x-show="showCategoryModal"]');
            if (modal) {
                // Réinitialiser l'icône quand le modal s'ouvre pour une nouvelle rubrique
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            const isVisible = !modal.style.display || modal.style.display !== 'none';
                            if (isVisible && !window.editingCategoryId) {
                                selectIcon('music_note', 'music_note');
                            }
                        }
                    });
                });
                observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
            }
        });

        // Initialiser avec l'icône par défaut quand le modal s'ouvre pour une nouvelle rubrique
        const originalShowCategoryModal = window.showCategoryModal;
        window.addEventListener('load', function() {
            // Réinitialiser l'icône quand on ouvre le modal pour créer une nouvelle rubrique
            const categoryModal = document.querySelector('[x-show="showCategoryModal"]');
            if (categoryModal) {
                // Utiliser Alpine.js pour détecter l'ouverture du modal
                setTimeout(() => {
                    const modalButton = document.querySelector('[onclick*="showCategoryModal = true"]');
                    if (modalButton) {
                        modalButton.addEventListener('click', function() {
                            if (!window.editingCategoryId) {
                                setTimeout(() => selectIcon('music_note', 'music_note'), 100);
                            }
                        });
                    }
                }, 500);
            }
        });
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] { display: none !important; }
        .material-icons {
            font-family: 'Material Icons';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
        }
        .icon-option {
            min-height: 60px;
        }
        .icon-option:hover {
            transform: scale(1.05);
        }
    </style>
</body>
</html>

