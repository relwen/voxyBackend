<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $rubrique->name }} - VoXY Maestro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
    showSectionModal: false,
    showDossierModal: false,
    showPartitionModal: false,
    showMesseModal: false,
    editingSection: null,
    selectedSection: null,
    selectedDossier: null,
    rubriqueId: {{ $rubrique->id }},
    messeForm: {
        nom: '',
        hasParts: false,
        parts: []
    },
    addPart(parentIndex = null) {
        const newPart = { nom: '', hasSubParts: false, subParts: [] };
        if (parentIndex !== null) {
            this.messeForm.parts[parentIndex].subParts.push(newPart);
        } else {
            this.messeForm.parts.push(newPart);
        }
    },
    removePart(index, parentIndex = null) {
        if (parentIndex !== null) {
            this.messeForm.parts[parentIndex].subParts.splice(index, 1);
        } else {
            this.messeForm.parts.splice(index, 1);
        }
    },
    resetMesseForm() {
        this.messeForm = { nom: '', hasParts: false, parts: [] };
        window.editingMesseId = null;
    }
}">
    @include('components.maestro-sidebar', ['user' => Auth::user(), 'chorale' => Auth::user()->chorale])
    
    <!-- Contenu principal -->
    <div class="lg:ml-64">
        <header class="bg-white shadow-sm border-b">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    @if($rubrique->icon)
                        <span class="material-icons text-5xl mr-4" style="color: {{ $rubrique->color ?? '#666' }}">{{ $rubrique->icon }}</span>
                    @else
                        <span class="material-icons text-5xl mr-4" style="color: {{ $rubrique->color ?? '#666' }}">folder</span>
                    @endif
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">{{ $rubrique->name }}</h2>
                        @if($rubrique->description)
                            <p class="text-sm text-gray-600 mt-1">{{ $rubrique->description }}</p>
                        @endif
                        <span class="inline-block mt-2 px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">
                            @if($rubrique->structure_type === 'simple')
                                Structure simple
                            @elseif($rubrique->structure_type === 'with_sections')
                                Avec sections
                            @else
                                Avec dossiers et sections
                            @endif
                        </span>
                    </div>
                </div>
                <div class="flex space-x-2">
                    @if(strtolower($rubrique->name) === 'messes')
                        <button @click="showMesseModal = true; resetMesseForm(); window.editingMesseId = null;" 
                                class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-plus mr-2"></i>Nouvelle messe
                        </button>
                    @else
                        @if($rubrique->hasDossiers())
                            <button @click="showDossierModal = true; selectedDossier = null" 
                                    class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-folder-plus mr-2"></i>Nouveau dossier
                            </button>
                        @endif
                        @if($rubrique->hasSections())
                            <button @click="showSectionModal = true; editingSection = null; selectedDossier = null" 
                                    class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>Nouvelle section
                            </button>
                        @endif
                        @if(!$rubrique->hasSections())
                            <button @click="showPartitionModal = true; selectedSection = null" 
                                    class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>Nouvelle partition
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(strtolower($rubrique->name) === 'messes')
                <!-- Interface simplifiée pour les Messes -->
                @if($rubrique->directSections->isEmpty())
                    <div class="text-center py-12 bg-white rounded-lg shadow">
                        <div class="text-gray-400 text-6xl mb-4"><i class="fas fa-church"></i></div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune messe</h3>
                        <p class="text-gray-500 mb-4">Commencez par créer une messe.</p>
                        <button @click="showMesseModal = true; resetMesseForm(); window.editingMesseId = null;" 
                                class="bg-primary hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                            <i class="fas fa-plus mr-2"></i>Créer une messe
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($rubrique->directSections as $messe)
                            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                            <i class="fas fa-church text-primary mr-2"></i>{{ $messe->nom }}
                                        </h3>
                                        @if($messe->structure && count($messe->structure) > 0)
                                            <div class="mt-3 space-y-2">
                                                <p class="text-sm font-medium text-gray-700">Parties :</p>
                                                <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                                                    @foreach($messe->structure as $part)
                                                        <li>{{ $part['nom'] }}
                                                            @if(isset($part['subParts']) && count($part['subParts']) > 0)
                                                                <ul class="list-disc list-inside ml-4 mt-1">
                                                                    @foreach($part['subParts'] as $subPart)
                                                                        <li>{{ $subPart['nom'] }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex space-x-2 ml-4">
                                        <a href="{{ route('admin.rubriques.messes.show', ['rubriqueId' => $rubrique->id, 'messeId' => $messe->id]) }}" 
                                           class="text-primary hover:text-primary-dark bg-primary/10 hover:bg-primary/20 px-3 py-2 rounded-lg text-sm font-medium">
                                            <i class="fas fa-folder-open mr-1"></i>Ouvrir
                                        </a>
                                        <button @click="editMesse({{ $messe->id }})" 
                                                class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteMesse({{ $messe->id }})" 
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                @if($messe->partitions->count() > 0)
                                    <div class="mt-4 pt-4 border-t">
                                        <p class="text-sm text-gray-600">
                                            <i class="fas fa-file-music mr-1"></i>
                                            {{ $messe->partitions->count() }} partition(s)
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @elseif($rubrique->structure_type === 'simple')
                <!-- Structure simple : partitions directes -->
                @if($rubrique->partitions->isEmpty())
                    <div class="text-center py-12 bg-white rounded-lg shadow">
                        <div class="text-gray-400 text-6xl mb-4"><i class="fas fa-file-music"></i></div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune partition</h3>
                        <p class="text-gray-500 mb-4">Commencez par créer une partition.</p>
                        <button @click="showPartitionModal = true; selectedSection = null" 
                                class="bg-primary hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                            <i class="fas fa-plus mr-2"></i>Créer une partition
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($rubrique->partitions as $partition)
                            @include('admin.rubriques.partition-card', ['partition' => $partition])
                        @endforeach
                    </div>
                @endif
            @elseif($rubrique->structure_type === 'with_dossiers')
                <!-- Structure avec dossiers : Dossiers -> Sections -> Partitions -->
                @if($rubrique->dossiers->isEmpty())
                    <div class="text-center py-12 bg-white rounded-lg shadow">
                        <div class="text-gray-400 text-6xl mb-4"><i class="fas fa-folder-open"></i></div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun dossier</h3>
                        <p class="text-gray-500 mb-4">Commencez par créer un dossier. Vous pourrez créer des dossiers dans des dossiers.</p>
                        <button @click="showDossierModal = true" 
                                class="bg-primary hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                            <i class="fas fa-folder-plus mr-2"></i>Créer un dossier
                        </button>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($rubrique->dossiers as $dossier)
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                                            <i class="fas fa-folder mr-2 text-yellow-500"></i>{{ $dossier->nom }}
                                        </h3>
                                        @if($dossier->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $dossier->description }}</p>
                                        @endif
                                    </div>
                                    <div class="flex space-x-2">
                                        <button @click="showDossierModal = true; selectedDossier = {{ $dossier->id }}" 
                                                class="bg-yellow-500 hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                            <i class="fas fa-folder-plus mr-2"></i>Nouveau sous-dossier
                                        </button>
                                        <button @click="showSectionModal = true; selectedDossier = {{ $dossier->id }}" 
                                                class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                            <i class="fas fa-plus mr-2"></i>Nouvelle section
                                        </button>
                                        <button @click="editSection({{ $dossier->id }})" 
                                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteSection({{ $dossier->id }})" 
                                                class="bg-red-200 hover:bg-red-300 text-red-700 px-4 py-2 rounded-lg text-sm font-medium">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Sous-dossiers et sections du dossier -->
                                @php
                                    $sousDossiers = $dossier->sections()->where('type', 'dossier')->get();
                                    $sections = $dossier->sections()->where('type', 'section')->get();
                                @endphp
                                
                                @if($sousDossiers->isEmpty() && $sections->isEmpty())
                                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                                        <p class="text-gray-500 mb-4">Aucun élément dans ce dossier</p>
                                        <div class="flex space-x-2 justify-center">
                                            <button @click="showDossierModal = true; selectedDossier = {{ $dossier->id }}" 
                                                    class="bg-yellow-500 hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                                <i class="fas fa-folder-plus mr-2"></i>Nouveau sous-dossier
                                            </button>
                                            <button @click="showSectionModal = true; selectedDossier = {{ $dossier->id }}" 
                                                    class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                                                <i class="fas fa-plus mr-2"></i>Nouvelle section
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <div class="space-y-4">
                                        <!-- Afficher les sous-dossiers récursivement -->
                                        @foreach($sousDossiers as $sousDossier)
                                            <div class="ml-4 border-l-2 border-yellow-300 pl-4">
                                                <div class="bg-yellow-50 rounded-lg shadow p-4">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div>
                                                            <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                                                <i class="fas fa-folder mr-2 text-yellow-600"></i>{{ $sousDossier->nom }}
                                                            </h4>
                                                            @if($sousDossier->description)
                                                                <p class="text-sm text-gray-600 mt-1">{{ $sousDossier->description }}</p>
                                                            @endif
                                                        </div>
                                                        <div class="flex space-x-2">
                                                            <button @click="showDossierModal = true; selectedDossier = {{ $sousDossier->id }}" 
                                                                    class="bg-yellow-500 hover:opacity-90 text-white px-3 py-1 rounded text-xs font-medium">
                                                                <i class="fas fa-folder-plus mr-1"></i>Sous-dossier
                                                            </button>
                                                            <button @click="showSectionModal = true; selectedDossier = {{ $sousDossier->id }}" 
                                                                    class="bg-primary hover:opacity-90 text-white px-3 py-1 rounded text-xs font-medium">
                                                                <i class="fas fa-plus mr-1"></i>Section
                                                            </button>
                                                            <button @click="editSection({{ $sousDossier->id }})" 
                                                                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1 rounded text-xs">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <button @click="deleteSection({{ $sousDossier->id }})" 
                                                                    class="bg-red-200 hover:bg-red-300 text-red-700 px-3 py-1 rounded text-xs">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @php
                                                        $sousSections = $sousDossier->sections()->where('type', 'section')->get();
                                                    @endphp
                                                    @if($sousSections->isNotEmpty())
                                                        <div class="mt-3 space-y-2">
                                                            @foreach($sousSections as $sousSection)
                                                                @include('admin.rubriques.section-block', ['section' => $sousSection, 'rubrique' => $rubrique])
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        <!-- Afficher les sections -->
                                        @foreach($sections as $section)
                                            @include('admin.rubriques.section-block', ['section' => $section, 'rubrique' => $rubrique])
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <!-- Structure avec sections : Sections -> Partitions -->
                @if($rubrique->directSections->isEmpty())
                    <div class="text-center py-12 bg-white rounded-lg shadow">
                        <div class="text-gray-400 text-6xl mb-4"><i class="fas fa-folder-open"></i></div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune section</h3>
                        <p class="text-gray-500 mb-4">Commencez par créer une section pour organiser vos partitions.</p>
                        <button @click="showSectionModal = true" 
                                class="bg-primary hover:opacity-90 text-white px-6 py-3 rounded-lg font-medium">
                            <i class="fas fa-plus mr-2"></i>Créer une section
                        </button>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach($rubrique->directSections as $section)
                            @include('admin.rubriques.section-block', ['section' => $section, 'rubrique' => $rubrique])
                        @endforeach
                    </div>
                @endif
            @endif
        </main>
    </div>

    <!-- Modals (à inclure depuis des fichiers séparés ou définis ici) -->
    @include('admin.rubriques.modals', ['rubrique' => $rubrique, 'pupitres' => $pupitres])

    <!-- Modal pour créer une messe simplifiée -->
    <div x-show="showMesseModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click.self="showMesseModal = false">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b">
                <h3 class="text-xl font-bold text-gray-900" x-text="window.editingMesseId ? 'Modifier la messe' : 'Créer une nouvelle messe'"></h3>
            </div>
            
            <form @submit.prevent="window.createMesse()" class="p-6 space-y-6">
                <!-- Nom de la messe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de la messe <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           x-model="messeForm.nom"
                           required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Ex: Messe de Noël">
                </div>

                <!-- Case à cocher : Cette messe a des parties -->
                <div class="flex items-center">
                    <input type="checkbox" 
                           id="hasParts"
                           x-model="messeForm.hasParts"
                           class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="hasParts" class="ml-2 block text-sm text-gray-700">
                        Cette messe a des parties
                    </label>
                </div>

                <!-- Formulaire pour les parties (affiché si hasParts est coché) -->
                <div x-show="messeForm.hasParts" x-cloak class="space-y-4 border-t pt-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700">Parties de la messe</label>
                        <button type="button" 
                                @click="addPart()"
                                class="text-sm text-primary hover:text-primary-dark">
                            <i class="fas fa-plus mr-1"></i>Ajouter une partie
                        </button>
                    </div>

                    <template x-for="(part, index) in messeForm.parts" :key="index">
                        <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                            <div class="flex items-center space-x-2">
                                <input type="text" 
                                       x-model="part.nom"
                                       placeholder="Nom de la partie (ex: Kyrié)"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                <button type="button" 
                                        @click="removePart(index)"
                                        class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            
                            <!-- Sous-parties -->
                            <div class="ml-4 space-y-2">
                                <div class="flex items-center">
                                    <input type="checkbox" 
                                           x-model="part.hasSubParts"
                                           class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                    <label class="ml-2 block text-xs text-gray-600">
                                        Cette partie a des sous-éléments
                                    </label>
                                </div>
                                
                                <div x-show="part.hasSubParts" x-cloak class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600">Sous-éléments</span>
                                        <button type="button" 
                                                @click="addPart(index)"
                                                class="text-xs text-primary hover:text-primary-dark">
                                            <i class="fas fa-plus mr-1"></i>Ajouter
                                        </button>
                                    </div>
                                    
                                    <template x-for="(subPart, subIndex) in part.subParts" :key="subIndex">
                                        <div class="flex items-center space-x-2">
                                            <input type="text" 
                                                   x-model="subPart.nom"
                                                   placeholder="Nom du sous-élément"
                                                   class="flex-1 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <button type="button" 
                                                    @click="removePart(subIndex, index)"
                                                    class="text-red-600 hover:text-red-800 text-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Boutons -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <button type="button" 
                            @click="showMesseModal = false; resetMesseForm(); window.editingMesseId = null;"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-primary text-white rounded-lg hover:opacity-90"
                            x-text="window.editingMesseId ? 'Modifier' : 'Créer la messe'">
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const rubriqueId = {{ $rubrique->id }};
        const structureType = '{{ $rubrique->structure_type }}';
        
        // Fonction pour créer une messe
        window.createMesse = function() {
            const alpineComponent = Alpine.$data(document.querySelector('[x-data]'));
            const formData = {
                nom: alpineComponent.messeForm.nom,
                has_parts: alpineComponent.messeForm.hasParts,
                structure: null
            };
            
            if (formData.has_parts && alpineComponent.messeForm.parts.length > 0) {
                formData.structure = alpineComponent.messeForm.parts.map(part => {
                    const partData = {
                        nom: part.nom || '',
                        subParts: []
                    };
                    if (part.hasSubParts && part.subParts && part.subParts.length > 0) {
                        partData.subParts = part.subParts.map(subPart => ({
                            nom: subPart.nom || ''
                        }));
                    }
                    return partData;
                });
            }
            
            const isEdit = window.editingMesseId;
            const url = isEdit 
                ? `/admin/rubriques/${rubriqueId}/sections/${window.editingMesseId}`
                : `/admin/rubriques/${rubriqueId}/messes`;
            const method = isEdit ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de ' + (isEdit ? 'la modification' : 'la création') + ' de la messe');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de ' + (isEdit ? 'la modification' : 'la création') + ' de la messe');
            });
        };
        
        function editMesse(id) {
            fetch(`/admin/rubriques/${rubriqueId}/sections/${id}`)
                .then(res => res.json())
                .then(data => {
                    // Remplir le formulaire avec les données de la messe
                    const alpineComponent = Alpine.$data(document.querySelector('[x-data]'));
                    alpineComponent.messeForm.nom = data.nom;
                    alpineComponent.messeForm.hasParts = data.structure && data.structure.length > 0;
                    if (data.structure) {
                        alpineComponent.messeForm.parts = data.structure.map(part => ({
                            nom: part.nom,
                            hasSubParts: part.subParts && part.subParts.length > 0,
                            subParts: part.subParts ? part.subParts.map(subPart => ({
                                nom: subPart.nom,
                                hasSubParts: false,
                                subParts: []
                            })) : []
                        }));
                    }
                    alpineComponent.showMesseModal = true;
                    window.editingMesseId = id;
                });
        }
        
        function deleteMesse(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette messe ?')) return;
            
            fetch(`/admin/rubriques/${rubriqueId}/sections/${id}`, {
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
        
        // Fonction pour sauvegarder une partition
        function savePartition() {
            const form = document.getElementById('partition-form');
            const formData = new FormData(form);
            const sectionId = window.selectedSection;
            
            let url;
            if (sectionId) {
                url = `/admin/rubriques/${rubriqueId}/sections/${sectionId}/partitions`;
            } else {
                url = `/admin/rubriques/${rubriqueId}/partitions`;
            }
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la création de la partition');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la création de la partition');
            });
        }
        
        // Exposer savePartition globalement pour Alpine.js
        window.savePartition = savePartition;
        
        function editSection(id) {
            fetch(`/admin/rubriques/${rubriqueId}/sections/${id}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('section-nom').value = data.nom;
                    document.getElementById('section-description').value = data.description || '';
                    document.getElementById('section-order').value = data.order || 0;
                    if (data.type) {
                        document.getElementById('section-type').value = data.type;
                    }
                    if (data.dossier_id) {
                        document.getElementById('section-dossier-id').value = data.dossier_id;
                    }
                    window.editingSectionId = id;
                    if (data.type === 'dossier') {
                        window.showDossierModal = true;
                    } else {
                        window.showSectionModal = true;
                    }
                });
        }

        function deleteSection(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ?')) return;
            
            fetch(`/admin/rubriques/${rubriqueId}/sections/${id}`, {
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

        function saveSection() {
            const form = document.getElementById('section-form');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);
            // Déterminer le type selon le modal ouvert
            if (window.showDossierModal) {
                data.type = 'dossier';
            } else {
                data.type = 'section';
            }
            if (window.selectedDossier) {
                data.dossier_id = window.selectedDossier;
            } else {
                data.dossier_id = null;
            }
            
            const url = window.editingSectionId 
                ? `/admin/rubriques/${rubriqueId}/sections/${window.editingSectionId}`
                : `/admin/rubriques/${rubriqueId}/sections`;
            const method = window.editingSectionId ? 'PUT' : 'POST';
            
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

        function savePartition() {
            const form = document.getElementById('partition-form');
            const formData = new FormData(form);
            const sectionId = window.selectedSection;
            
            let url;
            if (sectionId) {
                url = `/admin/rubriques/${rubriqueId}/sections/${sectionId}/partitions`;
            } else {
                url = `/admin/rubriques/${rubriqueId}/partitions`;
            }
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de l\'enregistrement');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de l\'enregistrement');
            });
        }

        function viewPartition(id) {
            window.open(`/admin/partitions/${id}`, '_blank');
        }

        function editPartition(id) {
            window.location.href = `/admin/partitions/${id}/edit`;
        }
    </script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
