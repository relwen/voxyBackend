<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vocaliseSection->nom }} - VoXY Maestro</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .bg-primary-gradient { background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); }
    </style>
    <style>
        .bg-primary { background: rgb(158, 2, 80); }
        .text-primary { color: rgb(158, 2, 80); }
        [x-cloak] { display: none !important; }
        .icon-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .drag-over {
            border-color: rgb(158, 2, 80) !important;
            background-color: rgba(158, 2, 80, 0.05) !important;
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ 
    showVocaliseModal: false,
    selectedPart: null,
    selectedSubPart: null,
    vocaliseForm: {
        title: '',
        description: '',
        pupitre_id: '',
        part: '',
        subPart: '',
    },
    selectedAudioFile: null,
    isDragging: false,
    setAudioFile(file) {
        if (file && file.type.startsWith('audio/')) {
            this.selectedAudioFile = {
                name: file.name,
                size: file.size,
                type: file.type,
                file: file
            };
        } else {
            alert('Veuillez sélectionner un fichier audio');
        }
    },
    removeAudioFile() {
        this.selectedAudioFile = null;
    },
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
}">
    @include('components.maestro-sidebar', ['user' => Auth::user(), 'chorale' => Auth::user()->chorale])
    
    <!-- Contenu principal -->
    <div class="lg:ml-64">
        <header class="bg-white shadow-sm border-b">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center">
                    <a href="{{ route('admin.rubriques.show', $rubrique->id) }}" 
                       class="text-gray-600 hover:text-gray-900 mr-4 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="flex items-center">
                        <div class="icon-wrapper w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-music-note text-primary text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ $vocaliseSection->nom }}</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-folder text-gray-400 mr-1"></i>Rubrique: {{ $rubrique->name }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Structure de la vocalise avec ses parties -->
            @if($vocaliseSection->structure && count($vocaliseSection->structure) > 0)
                <div class="space-y-6">
                    @foreach($vocaliseSection->structure as $partIndex => $part)
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6 border-l-4 border-primary">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="icon-wrapper w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-music text-primary"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $part['nom'] }}</h3>
                                </div>
                                <button @click="showVocaliseModal = true; selectedPart = '{{ $part['nom'] }}'; selectedSubPart = null; vocaliseForm.part = '{{ $part['nom'] }}'; vocaliseForm.subPart = ''; selectedAudioFile = null;" 
                                        class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-shadow">
                                    <i class="fas fa-plus mr-2"></i>Ajouter vocalise
                                </button>
                            </div>

                            <!-- Sous-parties -->
                            @if(isset($part['subParts']) && count($part['subParts']) > 0)
                                <div class="ml-6 space-y-4 mt-4">
                                    @foreach($part['subParts'] as $subPartIndex => $subPart)
                                        <div class="bg-gradient-to-r from-gray-50 to-white rounded-lg p-4 border-l-4 border-primary shadow-sm">
                                            <div class="flex items-center justify-between mb-3">
                                                <div class="flex items-center">
                                                    <div class="icon-wrapper w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center mr-2">
                                                        <i class="fas fa-microphone text-primary text-sm"></i>
                                                    </div>
                                                    <h4 class="text-lg font-medium text-gray-800">{{ $subPart['nom'] }}</h4>
                                                </div>
                                                <button @click="showVocaliseModal = true; selectedPart = '{{ $part['nom'] }}'; selectedSubPart = '{{ $subPart['nom'] }}'; vocaliseForm.part = '{{ $part['nom'] }}'; vocaliseForm.subPart = '{{ $subPart['nom'] }}'; selectedAudioFile = null;" 
                                                        class="bg-primary hover:opacity-90 text-white px-3 py-1.5 rounded-lg text-sm shadow-sm hover:shadow transition-shadow">
                                                    <i class="fas fa-plus mr-1"></i>Ajouter
                                                </button>
                                            </div>
                                            
                                            <!-- Vocalises de la sous-partie -->
                                            @php
                                                $partKey = $part['nom'] . ' > ' . $subPart['nom'];
                                                $subPartVocalises = $vocalisesByPart[$partKey] ?? [];
                                            @endphp
                                            @if(count($subPartVocalises) > 0)
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-3">
                                                    @foreach($subPartVocalises as $vocalise)
                                                        @include('admin.rubriques.vocalise-card', ['vocalise' => $vocalise])
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <div class="icon-wrapper w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                                        <i class="fas fa-microphone text-gray-400"></i>
                                                    </div>
                                                    <p class="text-sm text-gray-500 italic">Aucune vocalise</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Vocalises de la partie principale (sans sous-partie) -->
                            @php
                                $partKey = $part['nom'];
                                $partVocalises = $vocalisesByPart[$partKey] ?? [];
                            @endphp
                            @if(count($partVocalises) > 0)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Vocalises de la partie principale</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($partVocalises as $vocalise)
                                            @include('admin.rubriques.vocalise-card', ['vocalise' => $vocalise])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Vocalise sans parties - vocalises directes -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900">Vocalises</h3>
                        <button @click="showVocaliseModal = true; selectedPart = null; selectedSubPart = null; vocaliseForm.part = ''; vocaliseForm.subPart = ''; selectedAudioFile = null;" 
                                class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-plus mr-2"></i>Ajouter vocalise
                        </button>
                    </div>
                    
                    @if($vocaliseSection->vocalises->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($vocaliseSection->vocalises as $vocalise)
                                @include('admin.rubriques.vocalise-card', ['vocalise' => $vocalise])
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="icon-wrapper w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-microphone text-gray-400 text-3xl"></i>
                            </div>
                            <p class="text-gray-500">Aucune vocalise pour cette section</p>
                        </div>
                    @endif
                </div>
            @endif
        </main>
    </div>

    <!-- Modal pour ajouter une vocalise -->
    <div x-show="showVocaliseModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto"
         @click.self="showVocaliseModal = false; selectedAudioFile = null">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full p-8 my-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Ajouter une vocalise</h3>
                    <p x-show="selectedPart" class="text-sm text-gray-600 mt-1">
                        <i class="fas fa-music text-primary mr-1"></i>
                        <span x-text="selectedPart"></span>
                        <span x-show="selectedSubPart" x-text="' > ' + selectedSubPart"></span>
                    </p>
                </div>
                <button @click="showVocaliseModal = false; selectedAudioFile = null" 
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form id="vocalise-form" @submit.prevent="window.saveVocaliseForSection()" enctype="multipart/form-data">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-heading text-primary mr-2"></i>Titre *
                        </label>
                        <input type="text" name="title" id="vocalise-title" required
                               x-model="vocaliseForm.title"
                               placeholder="Ex: Vocalise Do-Ré-Mi - Soprane"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-align-left text-primary mr-2"></i>Description
                        </label>
                        <textarea name="description" id="vocalise-description" rows="3"
                                  x-model="vocaliseForm.description"
                                  placeholder="Description optionnelle de la vocalise..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-users text-primary mr-2"></i>Pupitre *
                        </label>
                        <select name="pupitre_id" id="vocalise-pupitre" required
                                x-model="vocaliseForm.pupitre_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            <option value="">Sélectionner un pupitre</option>
                            @foreach($pupitres as $pupitre)
                                <option value="{{ $pupitre->id }}" {{ $pupitre->is_default ? 'selected' : '' }}>
                                    {{ $pupitre->nom }}@if($pupitre->is_default) (Par défaut)@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <input type="hidden" name="part" x-model="vocaliseForm.part">
                    <input type="hidden" name="subPart" x-model="vocaliseForm.subPart">
                    
                    <!-- Zone de téléchargement pour fichier audio -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-audio text-primary mr-2"></i>Fichier audio *
                        </label>
                        
                        <!-- Zone de drag & drop -->
                        <div @dragover.prevent="isDragging = true" 
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="isDragging = false; setAudioFile($event.dataTransfer.files[0])"
                             :class="isDragging ? 'drag-over' : ''"
                             class="mt-2 flex justify-center px-6 pt-8 pb-8 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary transition-all cursor-pointer"
                             @click="$refs.audioInput.click()">
                            <div class="text-center">
                                <div class="icon-wrapper mx-auto w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-cloud-upload-alt text-primary text-3xl"></i>
                                </div>
                                <div class="flex text-sm text-gray-600">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                        <span>Cliquez pour sélectionner</span>
                                        <input type="file" 
                                               name="audio_file" 
                                               id="vocalise-audio-file" 
                                               x-ref="audioInput"
                                               accept="audio/*"
                                               class="sr-only"
                                               @change="setAudioFile($event.target.files[0])">
                                    </label>
                                    <p class="pl-1">ou glissez-déposez votre fichier audio ici</p>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Formats acceptés: MP3, WAV, OGG, M4A
                                </p>
                                <p class="text-xs text-gray-500">Maximum 10MB</p>
                            </div>
                        </div>
                        
                        <!-- Fichier audio sélectionné -->
                        <div x-show="selectedAudioFile" class="mt-4">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center flex-1 min-w-0">
                                        <div class="icon-wrapper w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                            <i class="fas fa-file-audio text-primary text-lg"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" x-text="selectedAudioFile.name"></p>
                                            <p class="text-xs text-gray-500" x-text="formatFileSize(selectedAudioFile.size)"></p>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            @click="removeAudioFile()"
                                            class="ml-3 text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-colors flex-shrink-0">
                                        <i class="fas fa-times-circle text-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                    <button type="button" 
                            @click="showVocaliseModal = false; selectedAudioFile = null" 
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:opacity-90 font-medium shadow-md hover:shadow-lg transition-all">
                        <i class="fas fa-save mr-2"></i>Enregistrer la vocalise
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const rubriqueId = {{ $rubrique->id }};
        const sectionId = {{ $vocaliseSection->id }};

        // Fonction pour sauvegarder une vocalise pour une partie de section
        window.saveVocaliseForSection = function() {
            const alpineComponent = Alpine.$data(document.querySelector('[x-data]'));
            
            if (!alpineComponent.selectedAudioFile) {
                alert('Veuillez sélectionner un fichier audio');
                return;
            }
            
            const form = document.getElementById('vocalise-form');
            const formData = new FormData(form);
            
            // Ajouter le fichier audio
            formData.append('audio_file', alpineComponent.selectedAudioFile.file);
            
            fetch(`/admin/rubriques/${rubriqueId}/vocalises/${sectionId}/vocalises`, {
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
                    alert(data.message || 'Erreur lors de la création de la vocalise');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de la création de la vocalise');
            });
        };

        // Fonction pour jouer/arrêter une vocalise
        function toggleVocalise(audioId) {
            const audio = document.getElementById('audio-' + audioId);
            if (audio.paused) {
                audio.play();
            } else {
                audio.pause();
            }
        }

        // Fonction pour supprimer une vocalise
        window.deleteVocalise = function(id) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette vocalise ?')) return;
            
            fetch(`/admin/rubriques/${rubriqueId}/vocalises/${sectionId}/vocalises/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
    </script>
</body>
</html>
