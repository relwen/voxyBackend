<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $messe->nom }} - VoXY Maestro</title>
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
    showPartitionModal: false,
    selectedPart: null,
    selectedSubPart: null,
    partitionForm: {
        title: '',
        description: '',
        pupitre_id: '',
        part: '',
        subPart: '',
        files: []
    },
    selectedFiles: [],
    isDragging: false,
    addFiles(files) {
        Array.from(files).forEach(file => {
            if (!this.selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                this.selectedFiles.push({
                    name: file.name,
                    size: file.size,
                    type: file.type,
                    file: file
                });
            }
        });
    },
    removeFile(index) {
        this.selectedFiles.splice(index, 1);
    },
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    },
    getFileIcon(type) {
        if (!type) return 'fa-file';
        if (type.startsWith('audio/')) return 'fa-music';
        if (type.startsWith('image/')) return 'fa-image';
        if (type === 'application/pdf') return 'fa-file-pdf';
        if (type.includes('video')) return 'fa-video';
        if (type.includes('word') || type.includes('document')) return 'fa-file-word';
        if (type.includes('excel') || type.includes('spreadsheet')) return 'fa-file-excel';
        return 'fa-file';
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
                            <i class="fas fa-church text-primary text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">{{ $messe->nom }}</h2>
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

            <!-- Structure de la messe avec ses parties -->
            @if($messe->structure && count($messe->structure) > 0)
                <div class="space-y-6">
                    @foreach($messe->structure as $partIndex => $part)
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6 border-l-4 border-primary">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="icon-wrapper w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-music text-primary"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-900">{{ $part['nom'] }}</h3>
                                </div>
                                <button @click="showPartitionModal = true; selectedPart = '{{ $part['nom'] }}'; selectedSubPart = null; partitionForm.part = '{{ $part['nom'] }}'; partitionForm.subPart = ''; selectedFiles = [];" 
                                        class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm hover:shadow-md transition-shadow">
                                    <i class="fas fa-plus mr-2"></i>Ajouter partition
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
                                                        <i class="fas fa-file-music text-primary text-sm"></i>
                                                    </div>
                                                    <h4 class="text-lg font-medium text-gray-800">{{ $subPart['nom'] }}</h4>
                                                </div>
                                                <button @click="showPartitionModal = true; selectedPart = '{{ $part['nom'] }}'; selectedSubPart = '{{ $subPart['nom'] }}'; partitionForm.part = '{{ $part['nom'] }}'; partitionForm.subPart = '{{ $subPart['nom'] }}'; selectedFiles = [];" 
                                                        class="bg-primary hover:opacity-90 text-white px-3 py-1.5 rounded-lg text-sm shadow-sm hover:shadow transition-shadow">
                                                    <i class="fas fa-plus mr-1"></i>Ajouter
                                                </button>
                                            </div>
                                            
                                            <!-- Partitions de la sous-partie -->
                                            @php
                                                $partKey = $part['nom'] . ' > ' . $subPart['nom'];
                                                $subPartPartitions = $partitionsByPart[$partKey] ?? [];
                                            @endphp
                                            @if(count($subPartPartitions) > 0)
                                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-3">
                                                    @foreach($subPartPartitions as $partition)
                                                        @include('admin.rubriques.partition-card', ['partition' => $partition])
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <div class="icon-wrapper w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-2">
                                                        <i class="fas fa-file-music text-gray-400"></i>
                                                    </div>
                                                    <p class="text-sm text-gray-500 italic">Aucune partition</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Partitions de la partie principale (sans sous-partie) -->
                            @php
                                $partKey = $part['nom'];
                                $partPartitions = $partitionsByPart[$partKey] ?? [];
                            @endphp
                            @if(count($partPartitions) > 0)
                                <div class="mt-4">
                                    <h4 class="text-sm font-medium text-gray-700 mb-3">Partitions de la partie principale</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($partPartitions as $partition)
                                            @include('admin.rubriques.partition-card', ['partition' => $partition])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Messe sans parties - partitions directes -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-900">Partitions de la messe</h3>
                        <button @click="showPartitionModal = true; selectedPart = null; selectedSubPart = null; partitionForm.part = ''; partitionForm.subPart = '';" 
                                class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                            <i class="fas fa-plus mr-2"></i>Ajouter partition
                        </button>
                    </div>
                    
                    @if($messe->partitions->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($messe->partitions as $partition)
                                @include('admin.rubriques.partition-card', ['partition' => $partition])
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="icon-wrapper w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-file-music text-gray-400 text-3xl"></i>
                            </div>
                            <p class="text-gray-500">Aucune partition pour cette messe</p>
                        </div>
                    @endif
                </div>
            @endif
        </main>
    </div>

    <!-- Modal pour ajouter une partition -->
    <div x-show="showPartitionModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 overflow-y-auto"
         @click.self="showPartitionModal = false; selectedFiles = []">
        <div class="bg-white rounded-xl shadow-2xl max-w-3xl w-full p-8 my-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Ajouter une partition</h3>
                    <p x-show="selectedPart" class="text-sm text-gray-600 mt-1">
                        <i class="fas fa-music text-primary mr-1"></i>
                        <span x-text="selectedPart"></span>
                        <span x-show="selectedSubPart" x-text="' > ' + selectedSubPart"></span>
                    </p>
                </div>
                <button @click="showPartitionModal = false; selectedFiles = []" 
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <form id="partition-form" @submit.prevent="window.savePartitionForMesse()" enctype="multipart/form-data">
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-heading text-primary mr-2"></i>Titre *
                        </label>
                        <input type="text" name="title" id="partition-title" required
                               x-model="partitionForm.title"
                               placeholder="Ex: Partition Kyrié - Soprane"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-align-left text-primary mr-2"></i>Description
                        </label>
                        <textarea name="description" id="partition-description" rows="3"
                                  x-model="partitionForm.description"
                                  placeholder="Description optionnelle de la partition..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-users text-primary mr-2"></i>Pupitre
                        </label>
                        <select name="pupitre_id" id="partition-pupitre"
                                x-model="partitionForm.pupitre_id"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            <option value="">Sélectionner un pupitre (optionnel)</option>
                            @foreach($pupitres as $pupitre)
                                <option value="{{ $pupitre->id }}" {{ $pupitre->is_default ? 'selected' : '' }}>
                                    {{ $pupitre->nom }}@if($pupitre->is_default) (Par défaut)@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <input type="hidden" name="part" x-model="partitionForm.part">
                    <input type="hidden" name="subPart" x-model="partitionForm.subPart">
                    
                    <!-- Zone de téléchargement améliorée -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-file-upload text-primary mr-2"></i>Fichiers
                        </label>
                        
                        <!-- Zone de drag & drop -->
                        <div @dragover.prevent="isDragging = true" 
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="isDragging = false; addFiles($event.dataTransfer.files)"
                             :class="isDragging ? 'drag-over' : ''"
                             class="mt-2 flex justify-center px-6 pt-8 pb-8 border-2 border-dashed border-gray-300 rounded-xl hover:border-primary transition-all cursor-pointer"
                             @click="$refs.fileInput.click()">
                            <div class="text-center">
                                <div class="icon-wrapper mx-auto w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-cloud-upload-alt text-primary text-3xl"></i>
                                </div>
                                <div class="flex text-sm text-gray-600">
                                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary">
                                        <span>Cliquez pour sélectionner</span>
                                        <input type="file" 
                                               name="files[]" 
                                               id="partition-files" 
                                               x-ref="fileInput"
                                               multiple
                                               class="sr-only"
                                               @change="addFiles($event.target.files)">
                                    </label>
                                    <p class="pl-1">ou glissez-déposez vos fichiers ici</p>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Formats acceptés: Audio (MP3, WAV, OGG, M4A), PDF, Images (JPEG, PNG, GIF), Vidéos (MP4)
                                </p>
                                <p class="text-xs text-gray-500">Maximum 20MB par fichier</p>
                            </div>
                        </div>
                        
                        <!-- Liste des fichiers sélectionnés -->
                        <div x-show="selectedFiles.length > 0" class="mt-4 space-y-2">
                            <p class="text-sm font-medium text-gray-700">
                                <i class="fas fa-list text-primary mr-2"></i>Fichiers sélectionnés (<span x-text="selectedFiles.length"></span>)
                            </p>
                            <div class="bg-gray-50 rounded-lg p-4 space-y-2 max-h-48 overflow-y-auto">
                                <template x-for="(file, index) in selectedFiles" :key="index">
                                    <div class="flex items-center justify-between bg-white p-3 rounded-lg border border-gray-200 hover:border-primary transition-all shadow-sm">
                                        <div class="flex items-center flex-1 min-w-0">
                                            <div class="icon-wrapper w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                                <i :class="'fas ' + getFileIcon(file.type) + ' text-primary text-lg'"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate" x-text="file.name"></p>
                                                <p class="text-xs text-gray-500" x-text="formatFileSize(file.size)"></p>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                @click="removeFile(index)"
                                                class="ml-3 text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-colors flex-shrink-0">
                                            <i class="fas fa-times-circle text-lg"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                    <button type="button" 
                            @click="showPartitionModal = false; selectedFiles = []" 
                            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>Annuler
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 bg-primary text-white rounded-lg hover:opacity-90 font-medium shadow-md hover:shadow-lg transition-all">
                        <i class="fas fa-save mr-2"></i>Enregistrer la partition
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const rubriqueId = {{ $rubrique->id }};
        const messeId = {{ $messe->id }};

        // Fonction pour sauvegarder une partition pour une partie de messe
        window.savePartitionForMesse = function() {
            const alpineComponent = Alpine.$data(document.querySelector('[x-data]'));
            const form = document.getElementById('partition-form');
            const formData = new FormData(form);
            
            // Ajouter les fichiers depuis selectedFiles
            alpineComponent.selectedFiles.forEach((fileObj, index) => {
                formData.append('files[]', fileObj.file);
            });
            
            fetch(`/admin/rubriques/${rubriqueId}/messes/${messeId}/partitions`, {
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
        };

        // Fonction pour voir une partition
        function viewPartition(id) {
            // Utiliser la même fenêtre pour préserver la session
            window.location.href = `/admin/partitions/${id}`;
        }

        // Fonction pour modifier une partition
        function editPartition(id) {
            window.location.href = `/admin/partitions/${id}/edit`;
        }
    </script>
</body>
</html>

