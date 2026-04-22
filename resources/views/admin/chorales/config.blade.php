@extends('layouts.maestro')

@section('title', 'Configuration - VoXY Maestro')
@section('page-title', 'Configuration de la Chorale')

@push('styles')
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .tab-active {
        color: var(--primary-color, rgb(158, 2, 80));
        border-bottom: 3px solid var(--primary-color, rgb(158, 2, 80));
    }
    .icon-option {
        transition: all 0.2s ease;
    }
    .icon-option:hover {
        transform: scale(1.1);
        background-color: rgba(158, 2, 80, 0.05);
    }
    .icon-selected {
        border-color: rgb(158, 2, 80) !important;
        background-color: rgba(158, 2, 80, 0.1) !important;
    }
</style>
@endpush

@section('content')
<div id="config-container" x-data="{ 
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
     " id="config-container">

    <!-- Header Stats / Welcome -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Paramètres <span class="text-primary italic">Chorale</span></h1>
            <p class="text-gray-500 mt-1">Gérez vos pupitres et vos rubriques de partitions.</p>
        </div>
        <div class="flex gap-2">
            <button @click="showTemplateModal = true" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-white text-gray-700 border border-gray-200 shadow-sm hover:bg-gray-50 transition-all flex items-center gap-2">
                <i class="fas fa-magic text-primary"></i> Templates
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 rounded-2xl bg-green-50 p-4 border border-green-100 flex items-center gap-3 animate-fade-in">
        <div class="w-8 h-8 rounded-lg bg-green-500 flex items-center justify-center text-white shadow-lg">
            <i class="fas fa-check text-sm"></i>
        </div>
        <p class="text-sm font-bold text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    <!-- Content Tabs -->
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-gray-100 overflow-hidden">
        <div class="border-b border-gray-100 px-8 bg-gray-50/50">
            <nav class="flex gap-8">
                <button @click="activeTab = 'pupitres'" 
                        :class="activeTab === 'pupitres' ? 'text-primary border-b-2 border-primary' : 'text-gray-400 border-b-2 border-transparent'"
                        class="py-5 text-sm font-black uppercase tracking-widest transition-all hover:text-primary">
                    <i class="fas fa-users mr-2"></i> Pupitres
                </button>
                <button @click="activeTab = 'rubriques'" 
                        :class="activeTab === 'rubriques' ? 'text-primary border-b-2 border-primary' : 'text-gray-400 border-b-2 border-transparent'"
                        class="py-5 text-sm font-black uppercase tracking-widest transition-all hover:text-primary">
                    <i class="fas fa-tags mr-2"></i> Rubriques
                </button>
            </nav>
        </div>

        <div class="p-8">
            <!-- Tabs: Pupitres -->
            <div x-show="activeTab === 'pupitres'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Membres & Pupitres</h3>
                        <p class="text-sm text-gray-400">Définissez la structure vocale de votre chorale.</p>
                    </div>
                    <button @click="showPupitreModal = true; editingPupitre = null" class="bg-primary-gradient text-white px-6 py-3 rounded-2xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-105 transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nouveau Pupitre
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($pupitres as $pupitre)
                    <div class="group relative bg-gray-50 rounded-[2rem] p-6 border-2 transition-all {{ $pupitre->is_default ? 'border-primary/20 bg-primary/[0.02]' : 'border-transparent hover:border-gray-200' }}">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-12 h-12 rounded-2xl bg-white shadow-sm flex items-center justify-center text-xl" style="color: {{ $pupitre->color ?? '#666' }}">
                                <i class="fas fa-{{ $pupitre->icon ?? 'users' }}"></i>
                            </div>
                            <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click="editPupitre({{ $pupitre->id }})" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button @click="deletePupitre({{ $pupitre->id }})" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $pupitre->nom }}</h4>
                        @if($pupitre->description)
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $pupitre->description }}</p>
                        @endif
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-widest text-gray-300">Ordre: {{ $pupitre->order }}</span>
                            @if($pupitre->is_default)
                            <span class="px-2 py-0.5 rounded-lg bg-primary text-white text-[10px] font-bold">PAR DÉFAUT</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-20 text-center bg-gray-50 rounded-[2.5rem] border-2 border-dashed border-gray-200">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <i class="fas fa-users text-gray-200 text-3xl"></i>
                        </div>
                        <h3 class="text-gray-400 font-bold text-lg">Aucun pupitre défini</h3>
                        <p class="text-gray-300 text-sm">Organisez votre chorale en ajoutant des pupitres vocaux.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Tabs: Rubriques -->
            <div x-show="activeTab === 'rubriques'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-cloak>
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Catégories & Rubriques</h3>
                        <p class="text-sm text-gray-400">Organisez vos partitions par moments liturgiques ou thèmes.</p>
                    </div>
                    <button @click="showCategoryModal = true; editingCategory = null;" class="bg-primary-gradient text-white px-6 py-3 rounded-2xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-105 transition-all flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nouvelle Rubrique
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($categories as $category)
                    <div class="group bg-white rounded-[2rem] p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all">
                        <div class="flex items-start justify-between mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center shadow-inner">
                                @if($category->icon)
                                    <span class="material-icons text-3xl" style="color: {{ $category->color ?? '#666' }}">{{ $category->icon }}</span>
                                @else
                                    <span class="material-icons text-3xl text-gray-300">folder</span>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.rubriques.show', $category->id) }}" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-primary/10 hover:text-primary transition-all">
                                    <i class="fas fa-eye text-sm"></i>
                                </a>
                                <button @click="editCategory({{ $category->id }})" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-blue-50 hover:text-blue-600 transition-all">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                <button @click="deleteCategory({{ $category->id }})" class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center hover:bg-red-50 hover:text-red-600 transition-all">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </div>
                        </div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $category->name }}</h4>
                        <p class="text-xs font-black uppercase tracking-tighter text-primary mt-1">
                            @if($category->structure_type === 'simple') Simple @elseif($category->structure_type === 'with_sections') Avec sections @else Avec dossiers @endif
                        </p>
                        @if($category->description)
                        <p class="text-sm text-gray-500 mt-3 line-clamp-2">{{ $category->description }}</p>
                        @endif
                        
                        <div class="mt-6 pt-6 border-t border-gray-50 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-layer-group text-gray-300 text-xs"></i>
                                <span class="text-xs font-bold text-gray-400">{{ $category->sections->count() }} sections</span>
                            </div>
                            <a href="{{ route('admin.rubriques.show', $category->id) }}" class="text-xs font-black text-primary hover:underline">OUVRIR <i class="fas fa-arrow-right ml-1"></i></a>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full py-20 text-center bg-gray-50 rounded-[2.5rem] border-2 border-dashed border-gray-200">
                        <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <i class="fas fa-tags text-gray-200 text-3xl"></i>
                        </div>
                        <h3 class="text-gray-400 font-bold text-lg">Aucune rubrique</h3>
                        <p class="text-gray-300 text-sm">Créez des rubriques pour classer vos partitions.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Pupitre Modal -->
    <div x-show="showPupitreModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="showPupitreModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showPupitreModal = false"></div>
        <div x-show="showPupitreModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="relative w-full max-w-lg bg-white rounded-[2.5rem] p-8 shadow-2xl">
            <h3 class="text-2xl font-black text-gray-900 mb-6" x-text="editingPupitre ? 'Modifier le pupitre' : 'Ajouter un pupitre'"></h3>
            <form id="pupitre-form" @submit.prevent="savePupitre()">
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Nom du pupitre *</label>
                        <input type="text" name="nom" id="pupitre-nom" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all" placeholder="Ex: Soprano, Alto, Ténor...">
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Description</label>
                        <textarea name="description" id="pupitre-description" rows="2" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all resize-none" placeholder="Brève description..."></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Couleur</label>
                            <input type="color" name="color" id="pupitre-color" class="w-full h-14 bg-gray-50 border border-gray-100 rounded-2xl p-1 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Icône (FA)</label>
                            <input type="text" name="icon" id="pupitre-icon" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all" placeholder="ex: music">
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-gray-50 p-4 rounded-2xl">
                        <input type="checkbox" name="is_default" id="pupitre-is-default" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary">
                        <label for="pupitre-is-default" class="text-sm font-bold text-gray-700">Définir comme pupitre par défaut (Tutti)</label>
                    </div>
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" @click="showPupitreModal = false" class="flex-1 py-4 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition-all">Annuler</button>
                    <button type="submit" class="flex-1 py-4 bg-primary-gradient text-white rounded-2xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-105 transition-all">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Modal -->
    <div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="showCategoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm" @click="showCategoryModal = false"></div>
        <div x-show="showCategoryModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="relative w-full max-w-2xl bg-white rounded-[2.5rem] p-10 shadow-2xl max-h-[90vh] overflow-y-auto custom-scrollbar">
            <h3 class="text-2xl font-black text-gray-900 mb-2" x-text="editingCategory ? 'Modifier la rubrique' : 'Ajouter une rubrique'"></h3>
            <p class="text-sm text-gray-400 mb-8">Configurez comment cette rubrique sera affichée dans l'application.</p>
            
            <form id="category-form" @submit.prevent="saveCategory()">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Nom de la rubrique *</label>
                                <input type="text" name="name" id="category-name" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all" placeholder="Ex: Messes de Noël, Chants Marials...">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Type d'organisation *</label>
                                <select name="structure_type" id="category-structure-type" required class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all appearance-none" onchange="updateStructureHelp()">
                                    <option value="simple">Partitions directes</option>
                                    <option value="with_sections">Avec sections (ex: Kyrie...)</option>
                                    <option value="with_dossiers">Avec dossiers thématiques</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Couleur thématique</label>
                                <input type="color" name="color" id="category-color" class="w-full h-14 bg-gray-50 border border-gray-100 rounded-2xl p-1 cursor-pointer">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Icône de l'application</label>
                            <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 flex flex-col items-center">
                                <div id="icon-preview" class="w-20 h-20 bg-white rounded-3xl shadow-sm border border-primary flex items-center justify-center text-5xl text-primary mb-4">
                                    <span class="material-icons text-4xl">music_note</span>
                                </div>
                                <input type="hidden" name="icon" id="category-icon" value="music_note">
                                <p class="text-[10px] font-bold text-gray-400 uppercase">Aperçu mobile</p>
                            </div>
                            <div class="mt-4 max-h-[200px] overflow-y-auto custom-scrollbar bg-gray-50 rounded-2xl border border-gray-100 p-4">
                                @php
                                    $iconHelper = new \App\Helpers\IconHelper();
                                    $iconsByCategory = $iconHelper::getIconsByCategory();
                                @endphp
                                @foreach($iconsByCategory as $category => $icons)
                                    <h4 class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2 mt-4 first:mt-0">{{ $category }}</h4>
                                    <div class="grid grid-cols-6 gap-2">
                                        @foreach($icons as $key => $icon)
                                            <button type="button" onclick="selectIcon('{{ $key }}', '{{ $icon['icon'] }}')" 
                                                    class="icon-option w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center text-gray-700 hover:text-primary"
                                                    data-icon-key="{{ $key }}" title="{{ $icon['name'] }}">
                                                <span class="material-icons text-lg">{{ $icon['icon'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-2">Description</label>
                        <textarea name="description" id="category-description" rows="3" class="w-full bg-gray-50 border border-gray-100 rounded-2xl px-5 py-4 text-gray-900 outline-none focus:ring-4 focus:ring-primary/10 transition-all resize-none"></textarea>
                    </div>
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" @click="showCategoryModal = false" class="flex-1 py-4 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-100 transition-all">Annuler</button>
                    <button type="submit" class="flex-1 py-4 bg-primary-gradient text-white rounded-2xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all">Enregistrer la rubrique</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // System logic moved from the original file
    function editPupitre(id) {
        fetch(`/admin/chorale/pupitres/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('pupitre-nom').value = data.nom;
                document.getElementById('pupitre-description').value = data.description || '';
                document.getElementById('pupitre-color').value = data.color || '#666666';
                document.getElementById('pupitre-icon').value = data.icon || '';
                document.getElementById('pupitre-is-default').checked = data.is_default || false;
                window.editingPupitreId = id;
                const alpine = Alpine.$data(document.getElementById('config-container'));
                alpine.showPupitreModal = true;
                alpine.editingPupitre = true;
            });
    }

    function savePupitre() {
        const form = document.getElementById('pupitre-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.is_default = document.getElementById('pupitre-is-default').checked;
        
        const url = window.editingPupitreId ? `/admin/chorale/pupitres/${window.editingPupitreId}` : '/admin/chorale/pupitres';
        const method = window.editingPupitreId ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Erreur');
        });
    }

    function deletePupitre(id) {
        if (!confirm('Supprimer ce pupitre ?')) return;
        fetch(`/admin/chorale/pupitres/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content }
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
        });
    }

    function editCategory(id) {
        fetch(`/admin/chorale/categories/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('category-name').value = data.name;
                document.getElementById('category-description').value = data.description || '';
                document.getElementById('category-color').value = data.color || '#666666';
                document.getElementById('category-structure-type').value = data.structure_type;
                if (data.icon) selectIcon(data.icon, data.icon);
                window.editingCategoryId = id;
                const alpine = Alpine.$data(document.getElementById('config-container'));
                alpine.showCategoryModal = true;
                alpine.editingCategory = true;
            });
    }

    function saveCategory() {
        const form = document.getElementById('category-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        data.structure_config = {};
        
        const url = window.editingCategoryId ? `/admin/chorale/categories/${window.editingCategoryId}` : '/admin/chorale/categories';
        const method = window.editingCategoryId ? 'PUT' : 'POST';
        
        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
            else alert(data.message || 'Erreur');
        });
    }

    function deleteCategory(id) {
        if (!confirm('Supprimer cette rubrique ?')) return;
        fetch(`/admin/chorale/categories/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content }
        }).then(res => res.json()).then(data => {
            if (data.success) location.reload();
        });
    }

    function selectIcon(iconKey, iconName) {
        document.getElementById('category-icon').value = iconKey;
        const preview = document.getElementById('icon-preview');
        preview.innerHTML = `<span class=\"material-icons text-4xl\">${iconName}</span>`;
        document.querySelectorAll('.icon-option').forEach(btn => btn.classList.remove('icon-selected'));
        const selectedBtn = document.querySelector(`[data-icon-key=\"${iconKey}\"]`);
        if (selectedBtn) selectedBtn.classList.add('icon-selected');
    }

    function updateStructureHelp() {
        const type = document.getElementById('category-structure-type').value;
        // Optional UI help feedback
    }
</script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
