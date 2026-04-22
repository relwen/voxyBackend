@extends('layouts.maestro')

@section('title', $rubrique->name . ' - VoXY Maestro')
@section('page-title', $rubrique->name)

@push('styles')
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
    .rubrique-gradient {
        background: linear-gradient(135deg, {{ $rubrique->color ?? 'rgb(158, 2, 80)' }} 0%, {{ $rubrique->color ? 'rgba('.implode(',', sscanf($rubrique->color, "#%02x%02x%02x")).', 0.8)' : 'rgba(158, 2, 80, 0.8)' }} 100%);
    }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div id="rubrique-container" x-data="{ 
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
    <!-- Header with Actions -->
    <div class="mb-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-6">
            <div class="w-20 h-20 rounded-[2rem] bg-white shadow-xl shadow-slate-200/50 flex items-center justify-center border border-gray-100 overflow-hidden">
                @if($rubrique->icon)
                    <span class="material-icons text-5xl" style="color: {{ $rubrique->color ?? 'rgb(158, 2, 80)' }}">{{ $rubrique->icon }}</span>
                @else
                    <span class="material-icons text-5xl text-gray-200">folder</span>
                @endif
            </div>
            <div>
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">{{ $rubrique->name }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    <span class="px-3 py-1 rounded-full bg-primary/10 text-primary text-[10px] font-black uppercase tracking-widest border border-primary/10">
                        @if($rubrique->structure_type === 'simple') Simple @elseif($rubrique->structure_type === 'with_sections') Sections @else Dossiers @endif
                    </span>
                    @if($rubrique->description)
                        <span class="text-gray-400 text-sm font-medium">{{ Str::limit($rubrique->description, 50) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-3">
            @if(in_array(strtolower($rubrique->name), ['messes', 'vocalises', 'chants']))
                <button @click="showMesseModal = true; resetMesseForm(); window.editingMesseId = null;" 
                        class="bg-primary-gradient text-white px-6 py-4 rounded-2xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-105 transition-all flex items-center gap-2">
                    <i class="fas fa-plus"></i> Nouveau {{ strtolower($rubrique->name) === 'messes' ? 'Messe' : (strtolower($rubrique->name) === 'vocalises' ? 'Vocalise' : 'Chant') }}
                </button>
            @else
                @if($rubrique->hasDossiers())
                    <button @click="showDossierModal = true; selectedDossier = null" 
                            class="bg-white text-gray-700 border border-gray-200 px-6 py-4 rounded-2xl text-sm font-black shadow-sm hover:bg-gray-50 transition-all flex items-center gap-2">
                        <i class="fas fa-folder-plus text-primary"></i> Nouveau Dossier
                    </button>
                @endif
                @if($rubrique->hasSections())
                    <button @click="showSectionModal = true; editingSection = null; selectedDossier = null" 
                            class="bg-primary-gradient text-white px-6 py-4 rounded-2xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-105 transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nouvelle Section
                    </button>
                @endif
                @if(!$rubrique->hasSections() && !$rubrique->hasDossiers())
                    <button @click="showPartitionModal = true; selectedSection = null" 
                            class="bg-primary-gradient text-white px-6 py-4 rounded-2xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-105 transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nouvelle Partition
                    </button>
                @endif
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="mb-8 rounded-[2rem] bg-green-50 p-6 border border-green-100 flex items-center gap-4 animate-fade-in shadow-sm">
        <div class="w-12 h-12 rounded-2xl bg-green-500 flex items-center justify-center text-white shadow-lg shadow-green-200">
            <i class="fas fa-check text-lg"></i>
        </div>
        <p class="text-sm font-bold text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Content Grid -->
    <div class="space-y-8">
        @if(strtolower($rubrique->name) === 'messes')
            <!-- Messes View -->
            @include('admin.rubriques._messes_list', ['rubrique' => $rubrique])
        @elseif(strtolower($rubrique->name) === 'chants')
            <!-- Chants View -->
            @include('admin.rubriques._chants_list', ['rubrique' => $rubrique])
        @elseif(strtolower($rubrique->name) === 'vocalises')
            <!-- Vocalises View -->
            @include('admin.rubriques._vocalises_list', ['rubrique' => $rubrique])
        @elseif($rubrique->structure_type === 'simple')
            <!-- Simple Partitions View -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($rubrique->partitions as $partition)
                    @include('admin.rubriques.partition-card', ['partition' => $partition])
                @empty
                    <div class="col-span-full py-24 text-center bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-music text-gray-200 text-4xl"></i>
                        </div>
                        <h3 class="text-gray-400 font-bold text-xl">Aucune partition ici</h3>
                        <p class="text-gray-300 text-sm mt-1">Commencez par ajouter votre première partition.</p>
                        <button @click="showPartitionModal = true; selectedSection = null" class="mt-8 text-primary font-black py-3 px-8 rounded-2xl border-2 border-primary hover:bg-primary hover:text-white transition-all uppercase tracking-widest text-xs">
                            Ajouter une partition
                        </button>
                    </div>
                @endforelse
            </div>
        @elseif($rubrique->structure_type === 'with_dossiers')
            <!-- Dossiers View -->
            @include('admin.rubriques._dossiers_list', ['rubrique' => $rubrique])
        @else
            <!-- Sections View -->
            <div class="space-y-8">
                @forelse($rubrique->directSections as $section)
                    @include('admin.rubriques.section-block', ['section' => $section, 'rubrique' => $rubrique])
                @empty
                    <div class="py-24 text-center bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
                        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-folder-open text-gray-200 text-4xl"></i>
                        </div>
                        <h3 class="text-gray-400 font-bold text-xl">Aucune section</h3>
                        <p class="text-gray-300 text-sm mt-1">Créez des sections pour organiser vos partitions.</p>
                        <button @click="showSectionModal = true" class="mt-8 text-primary font-black py-3 px-8 rounded-2xl border-2 border-primary hover:bg-primary hover:text-white transition-all uppercase tracking-widest text-xs">
                            Créer une section
                        </button>
                    </div>
                @endforelse
            </div>
        @endif
    </div>

    <!-- Modals -->
    @include('admin.rubriques.modals', ['rubrique' => $rubrique, 'pupitres' => $pupitres])

    <!-- Simplified Messe Modal -->
    <div x-show="showMesseModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="showMesseModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showMesseModal = false"></div>
        <div x-show="showMesseModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="relative w-full max-w-2xl bg-white rounded-[2.5rem] p-10 shadow-2xl max-h-[90vh] overflow-y-auto">
            <h3 class="text-2xl font-black text-gray-900 mb-6" x-text="window.editingMesseId ? 'Modifier l\'élément' : 'Nouvel élément'"></h3>
            
            <form @submit.prevent="window.createMesse()" class="space-y-6">
                <div>
                    <label class="premium-label">Nom / Titre *</label>
                    <input type="text" x-model="messeForm.nom" required class="premium-input">
                </div>

                <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-2xl">
                    <input type="checkbox" id="hasParts" x-model="messeForm.hasParts" class="w-5 h-5 rounded border-gray-300 text-primary">
                    <label for="hasParts" class="text-sm font-bold text-gray-700">Cet élément contient plusieurs parties (ex: Kyrié, Gloria...)</label>
                </div>

                <div x-show="messeForm.hasParts" x-cloak class="space-y-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <label class="premium-label">Structure des parties</label>
                        <button type="button" @click="addPart()" class="text-xs font-black text-primary uppercase">
                            <i class="fas fa-plus mr-1"></i> Ajouter une partie
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(part, index) in messeForm.parts" :key="index">
                            <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4">
                                <div class="flex items-center gap-3">
                                    <input type="text" x-model="part.nom" placeholder="Nom de la partie" class="flex-1 premium-input !py-2 !px-4 !rounded-xl !text-sm">
                                    <button type="button" @click="removePart(index)" class="text-red-400 hover:text-red-600">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <div class="mt-4 flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" x-model="part.hasSubParts" class="rounded text-primary">
                                        <span class="text-[10px] font-bold text-gray-500 uppercase">Sous-éléments</span>
                                    </div>
                                    <button x-show="part.hasSubParts" type="button" @click="addPart(index)" class="text-[10px] font-black text-primary uppercase">
                                        <i class="fas fa-plus mr-1"></i> Sous-partie
                                    </button>
                                </div>
                                <div x-show="part.hasSubParts" class="mt-3 pl-6 space-y-2">
                                    <template x-for="(subPart, subIndex) in part.subParts" :key="subIndex">
                                        <div class="flex items-center gap-2">
                                            <input type="text" x-model="subPart.nom" placeholder="Sous-élément" class="flex-1 premium-input !py-1.5 !px-3 !rounded-lg !text-xs">
                                            <button type="button" @click="removePart(subIndex, index)" class="text-red-300 hover:text-red-500 text-xs">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" @click="showMesseModal = false; resetMesseForm();" class="flex-1 py-4 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-100">Annuler</button>
                    <button type="submit" class="flex-1 py-4 bg-primary-gradient text-white rounded-2xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-105 transition-all">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const rubriqueId = {{ $rubrique->id }};
    const structureType = '{{ $rubrique->structure_type }}';
    
    window.createMesse = function() {
        const alpine = Alpine.$data(document.getElementById('rubrique-container'));
        const formData = {
            nom: alpine.messeForm.nom,
            has_parts: alpine.messeForm.hasParts,
            structure: null
        };
        
        if (formData.has_parts && alpine.messeForm.parts.length > 0) {
            formData.structure = alpine.messeForm.parts.map(part => {
                const partData = { nom: part.nom || '', subParts: [] };
                if (part.hasSubParts && part.subParts) {
                    partData.subParts = part.subParts.map(sp => ({ nom: sp.nom || '' }));
                }
                return partData;
            });
        }
        
        const isEdit = window.editingMesseId;
        const isVocalises = {{ strtolower($rubrique->name) === 'vocalises' ? 'true' : 'false' }};
        const isChants = {{ strtolower($rubrique->name) === 'chants' ? 'true' : 'false' }};
        const url = isEdit ? `/admin/rubriques/${rubriqueId}/sections/${window.editingMesseId}` : (isVocalises || isChants ? `/admin/rubriques/${rubriqueId}/sections` : `/admin/rubriques/${rubriqueId}/messes`);
        const method = isEdit ? 'PUT' : 'POST';
        
        if (!isEdit && (isVocalises || isChants)) formData.type = 'section';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Erreur');
        });
    };

    function editMesse(id) {
        fetch(`/admin/rubriques/${rubriqueId}/sections/${id}`).then(res => res.json()).then(data => {
            const alpine = Alpine.$data(document.getElementById('rubrique-container'));
            alpine.messeForm.nom = data.nom;
            alpine.messeForm.hasParts = data.structure && data.structure.length > 0;
            if (data.structure) {
                alpine.messeForm.parts = data.structure.map(p => ({
                    nom: p.nom,
                    hasSubParts: p.subParts && p.subParts.length > 0,
                    subParts: p.subParts ? p.subParts.map(sp => ({ nom: sp.nom })) : []
                }));
            }
            alpine.showMesseModal = true;
            window.editingMesseId = id;
        });
    }

    function deleteMesse(id) {
        if (!confirm('Supprimer cet élément ?')) return;
        fetch(`/admin/rubriques/${rubriqueId}/sections/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content }
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
        });
    }

    function editSection(id) {
        fetch(`/admin/rubriques/${rubriqueId}/sections/${id}`).then(res => res.json()).then(data => {
            document.getElementById('section-nom').value = data.nom;
            document.getElementById('section-description').value = data.description || '';
            document.getElementById('section-order').value = data.order || 0;
            if (data.type) document.getElementById('section-type').value = data.type;
            if (data.dossier_id) document.getElementById('section-dossier-id').value = data.dossier_id;
            window.editingSectionId = id;
            if (data.type === 'dossier') window.showDossierModal = true;
            else window.showSectionModal = true;
        });
    }

    function deleteSection(id) {
        if (!confirm('Supprimer cet élément ?')) return;
        fetch(`/admin/rubriques/${rubriqueId}/sections/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content }
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
        });
    }
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
