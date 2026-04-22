@if($rubrique->dossiers->isEmpty())
    <div class="py-24 text-center bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
            <i class="fas fa-folder-open text-gray-200 text-4xl"></i>
        </div>
        <h3 class="text-gray-400 font-bold text-xl">Aucun dossier</h3>
        <p class="text-gray-300 text-sm mt-1">Créez des dossiers pour organiser vos documents par thématiques.</p>
        <button @click="showDossierModal = true" class="mt-8 text-primary font-black py-3 px-8 rounded-2xl border-2 border-primary hover:bg-primary hover:text-white transition-all uppercase tracking-widest text-xs">
            Créer un dossier
        </button>
    </div>
@else
    <div class="space-y-8">
        @foreach($rubrique->dossiers as $dossier)
            <div id="dossier-{{ $dossier->id }}" class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/40 border border-gray-100 overflow-hidden">
                <div class="p-8 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-yellow-400/10 rounded-2xl flex items-center justify-center text-yellow-600 text-2xl">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900">{{ $dossier->nom }}</h3>
                            @if($dossier->description)
                                <p class="text-xs text-gray-400 font-medium">{{ $dossier->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button @click="showDossierModal = true; selectedDossier = {{ $dossier->id }}" 
                                class="h-10 px-4 rounded-xl bg-white border border-gray-200 text-gray-600 text-[10px] font-black uppercase tracking-widest hover:bg-gray-50 transition-all">
                            + DOSSIER
                        </button>
                        <button @click="showSectionModal = true; selectedDossier = {{ $dossier->id }}" 
                                class="h-10 px-4 rounded-xl bg-primary text-white text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:scale-105 transition-all">
                            + SECTION
                        </button>
                    </div>
                </div>

                <div class="p-8">
                    @php
                        $sousDossiers = $dossier->sections()->where('type', 'dossier')->get();
                        $sections = $dossier->sections()->where('type', 'section')->get();
                    @endphp
                    
                    @if($sousDossiers->isEmpty() && $sections->isEmpty())
                        <div class="text-center py-10 bg-gray-50/50 rounded-3xl border-2 border-dashed border-gray-100">
                            <p class="text-gray-400 text-sm font-bold">Dossier vide</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($sousDossiers as $sousDossier)
                                <div id="dossier-{{ $sousDossier->id }}" class="ml-6 border-l-2 border-yellow-200 pl-6">
                                    <div class="flex items-center justify-between group">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-folder text-yellow-500"></i>
                                            <span class="font-bold text-gray-700">{{ $sousDossier->nom }}</span>
                                        </div>
                                        <div class="flex opacity-0 group-hover:opacity-100 transition-opacity gap-2">
                                            <button @click="editSection({{ $sousDossier->id }})" class="text-blue-400 hover:text-blue-600"><i class="fas fa-edit"></i></button>
                                            <button @click="deleteSection({{ $sousDossier->id }})" class="text-red-400 hover:text-red-600"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                    <div class="mt-4 space-y-4">
                                        @foreach($sousDossier->sections()->where('type', 'section')->get() as $sousSection)
                                            @include('admin.rubriques.section-block', ['section' => $sousSection, 'rubrique' => $rubrique])
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            
                            @foreach($sections as $section)
                                @include('admin.rubriques.section-block', ['section' => $section, 'rubrique' => $rubrique])
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
