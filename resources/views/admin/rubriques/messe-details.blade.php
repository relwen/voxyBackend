@extends('layouts.maestro')

@section('title', $messe->nom . ' - VoXY Maestro')
@section('page-title', $messe->nom)

@push('styles')
<style>
    [x-cloak] { display: none !important; }
    .drag-over {
        border-color: rgb(158, 2, 80) !important;
        background-color: rgba(158, 2, 80, 0.05) !important;
    }
</style>
@endpush

@section('content')
<div id="messe-container" x-data="{ 
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
        return 'fa-file';
    }
}">
    <!-- Header -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-6">
            <a href="{{ route('admin.rubriques.show', $rubrique->id) }}" class="w-14 h-14 rounded-2xl bg-white shadow-lg flex items-center justify-center text-gray-400 hover:text-primary transition-all">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ $messe->nom }}</h1>
                <p class="text-gray-400 text-sm font-medium mt-1">
                    <i class="fas fa-folder mr-1"></i> Rubrique: <span class="text-primary font-bold">{{ $rubrique->name }}</span>
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-8 rounded-[2rem] bg-green-50 p-6 border border-green-100 flex items-center gap-4 animate-fade-in shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-green-500 flex items-center justify-center text-white shadow-lg">
            <i class="fas fa-check text-lg"></i>
        </div>
        <p class="text-sm font-bold text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Content Structure -->
    @if($messe->structure && count($messe->structure) > 0)
        <div class="space-y-10">
            @foreach($messe->structure as $partIndex => $part)
                <div class="bg-white rounded-[3rem] shadow-xl shadow-slate-200/50 border border-gray-100 overflow-hidden">
                    <div class="p-8 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 bg-primary-gradient rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg shadow-primary/20">
                                <i class="fas fa-music"></i>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 tracking-tighter">{{ $part['nom'] }}</h3>
                        </div>
                        <button @click="showPartitionModal = true; selectedPart = '{{ $part['nom'] }}'; selectedSubPart = null; partitionForm.part = '{{ $part['nom'] }}'; partitionForm.subPart = ''; selectedFiles = [];" 
                                class="bg-white border border-gray-200 text-primary px-6 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-all flex items-center gap-2">
                            <i class="fas fa-plus"></i> AJOUTER PARTITION
                        </button>
                    </div>

                    <div class="p-8">
                        <!-- Subparts -->
                        @if(isset($part['subParts']) && count($part['subParts']) > 0)
                            <div class="space-y-8">
                                @foreach($part['subParts'] as $subPartIndex => $subPart)
                                    <div class="bg-gray-50/50 rounded-[2.5rem] p-8 border border-gray-100">
                                        <div class="flex items-center justify-between mb-8">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-primary shadow-sm">
                                                    <i class="fas fa-file-music"></i>
                                                </div>
                                                <h4 class="text-xl font-bold text-gray-800">{{ $subPart['nom'] }}</h4>
                                            </div>
                                            <button @click="showPartitionModal = true; selectedPart = '{{ $part['nom'] }}'; selectedSubPart = '{{ $subPart['nom'] }}'; partitionForm.part = '{{ $part['nom'] }}'; partitionForm.subPart = '{{ $subPart['nom'] }}'; selectedFiles = [];" 
                                                    class="text-[10px] font-black text-primary uppercase tracking-widest border-b-2 border-primary/20 hover:border-primary transition-all">
                                                + AJOUTER
                                            </button>
                                        </div>
                                        
                                        @php
                                            $partKey = $part['nom'] . ' > ' . $subPart['nom'];
                                            $subPartPartitions = $partitionsByPart[$partKey] ?? [];
                                        @endphp
                                        @if(count($subPartPartitions) > 0)
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                                @foreach($subPartPartitions as $partition)
                                                    @include('admin.rubriques.partition-card', ['partition' => $partition])
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="py-10 text-center border-2 border-dashed border-gray-200 rounded-[2rem]">
                                                <p class="text-gray-300 text-xs font-black uppercase tracking-widest">Aucune partition ici</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Main Part Partitions -->
                        @php
                            $partKey = $part['nom'];
                            $partPartitions = $partitionsByPart[$partKey] ?? [];
                        @endphp
                        @if(count($partPartitions) > 0)
                            <div class="mt-8">
                                <p class="text-[10px] font-black text-gray-300 uppercase tracking-widest mb-4">Partitions directes</p>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    @foreach($partPartitions as $partition)
                                        @include('admin.rubriques.partition-card', ['partition' => $partition])
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Messe without parts -->
        <div class="bg-white rounded-[3rem] p-10 shadow-xl border border-gray-100 flex flex-col items-center py-20">
            <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-8 shadow-inner">
                <i class="fas fa-file-music text-gray-200 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-900 mb-2">Aucune partition</h3>
            <p class="text-gray-400 text-center max-w-sm mb-10 leading-relaxed font-medium">Cette messe n'a pas encore de répertoire. Commencez par ajouter votre première partition.</p>
            <button @click="showPartitionModal = true; selectedPart = null; selectedSubPart = null; partitionForm.part = ''; partitionForm.subPart = '';" 
                    class="bg-primary-gradient text-white px-10 py-5 rounded-3xl text-sm font-black shadow-xl shadow-primary/20 hover:scale-105 transition-all flex items-center gap-3">
                <i class="fas fa-plus"></i> AJOUTER UNE PARTITION
            </button>
        </div>
    @endif

    <!-- Partition Modal -->
    <div x-show="showPartitionModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="showPartitionModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showPartitionModal = false"></div>
        <div x-show="showPartitionModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="relative w-full max-w-3xl bg-white rounded-[3rem] p-10 shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-2xl font-black text-gray-900">Nouvelle Partition</h3>
                    <div x-show="selectedPart" class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-black uppercase tracking-tighter text-primary" x-text="selectedPart"></span>
                        <template x-if="selectedSubPart">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-chevron-right text-[8px] text-gray-300"></i>
                                <span class="text-xs font-black uppercase tracking-tighter text-gray-400" x-text="selectedSubPart"></span>
                            </span>
                        </template>
                    </div>
                </div>
                <button @click="showPartitionModal = false" class="text-gray-300 hover:text-gray-900 transition-colors">
                    <i class="fas fa-times text-24"></i>
                </button>
            </div>
            
            <form id="partition-form" @submit.prevent="window.savePartitionForMesse()" class="space-y-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Titre de la partition *</label>
                            <input type="text" name="title" x-model="partitionForm.title" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all font-bold">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Pupitre cible</label>
                            <select name="pupitre_id" x-model="partitionForm.pupitre_id" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all font-bold appearance-none">
                                <option value="">Tous les pupitres (Tutti)</option>
                                @foreach($pupitres as $pupitre)
                                    <option value="{{ $pupitre->id }}">{{ $pupitre->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Description</label>
                        <textarea name="description" x-model="partitionForm.description" rows="5" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all resize-none"></textarea>
                    </div>
                </div>

                <input type="hidden" name="part" x-model="partitionForm.part">
                <input type="hidden" name="subPart" x-model="partitionForm.subPart">
                
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Fichiers (Audio, PDF, Image)</label>
                    <div @dragover.prevent="isDragging = true" 
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; addFiles($event.dataTransfer.files)"
                         :class="isDragging ? 'drag-over' : ''"
                         class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-[2.5rem] p-10 flex flex-col items-center cursor-pointer hover:border-primary transition-all group"
                         @click="$refs.fileInput.click()">
                        <div class="w-20 h-20 bg-white rounded-3xl shadow-sm border border-gray-100 flex items-center justify-center text-primary text-3xl mb-4 group-hover:scale-110 transition-transform">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p class="text-sm font-black text-gray-900 uppercase">Glissez vos fichiers ici</p>
                        <p class="text-xs text-gray-400 mt-1">ou cliquez pour parcourir</p>
                        <input type="file" name="files[]" x-ref="fileInput" multiple class="sr-only" @change="addFiles($event.target.files)">
                    </div>
                    
                    <div x-show="selectedFiles.length > 0" class="mt-6 space-y-3">
                        <template x-for="(file, index) in selectedFiles" :key="index">
                            <div class="flex items-center justify-between bg-white border border-gray-100 p-4 rounded-2xl shadow-sm">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-12 h-12 bg-primary/5 rounded-xl flex items-center justify-center text-primary text-xl">
                                        <i :class="'fas ' + getFileIcon(file.type)"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-black text-gray-900 truncate" x-text="file.name"></p>
                                        <p class="text-[10px] font-bold text-gray-400 uppercase" x-text="formatFileSize(file.size)"></p>
                                    </div>
                                </div>
                                <button type="button" @click="removeFile(index)" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center hover:bg-red-100 transition-colors">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="flex gap-4 pt-8 border-t border-gray-50">
                    <button type="button" @click="showPartitionModal = false" class="flex-1 py-5 rounded-3xl text-sm font-black text-gray-400 hover:bg-gray-50 transition-all">ANNULER</button>
                    <button type="submit" class="flex-1 py-5 bg-primary-gradient text-white rounded-3xl text-sm font-black shadow-xl shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">ENREGISTRER LA PARTITION</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rubriqueId = {{ $rubrique->id }};
    const messeId = {{ $messe->id }};

    window.savePartitionForMesse = function() {
        const alpine = Alpine.$data(document.getElementById('messe-container'));
        const formData = new FormData();
        const form = document.getElementById('partition-form');
        
        for (let element of form.elements) {
            if (element.name && element.name !== 'files[]' && element.type !== 'file') {
                formData.append(element.name, element.value);
            }
        }
        alpine.selectedFiles.forEach(fileObj => formData.append('files[]', fileObj.file));
        
        fetch(`/admin/rubriques/${rubriqueId}/messes/${messeId}/partitions`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content, 'Accept': 'application/json' },
            body: formData
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Erreur');
        });
    };

    function viewPartition(id) { window.location.href = `/admin/partitions/${id}`; }
    function editPartition(id) { window.location.href = `/admin/partitions/${id}/edit`; }
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
