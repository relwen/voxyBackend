@if($rubrique->directSections->isEmpty())
    <div class="py-24 text-center bg-white rounded-[3rem] border-2 border-dashed border-gray-100">
        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
            <i class="fas fa-music text-gray-200 text-4xl"></i>
        </div>
        <h3 class="text-gray-400 font-bold text-xl">Aucun chant enregistré</h3>
        <p class="text-gray-300 text-sm mt-1">Commencez par ajouter votre premier chant.</p>
        <button @click="showMesseModal = true; resetMesseForm(); window.editingMesseId = null;" class="mt-8 text-primary font-black py-3 px-8 rounded-2xl border-2 border-primary hover:bg-primary hover:text-white transition-all uppercase tracking-widest text-xs">
            Ajouter un chant
        </button>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($rubrique->directSections as $chant)
            <div class="group relative bg-white rounded-[2.5rem] p-8 shadow-xl shadow-slate-200/40 border border-gray-100 hover:shadow-2xl hover:shadow-primary/10 transition-all overflow-hidden">
                <div class="absolute -top-10 -left-10 w-32 h-32 bg-primary/5 rounded-full blur-3xl group-hover:bg-primary/10 transition-all"></div>
                
                <div class="relative items-start justify-between flex mb-6">
                    <div class="w-16 h-16 rounded-2xl bg-primary-gradient shadow-lg shadow-primary/20 flex items-center justify-center text-white text-2xl">
                        <i class="fas fa-music"></i>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                        <button @click="editMesse({{ $chant->id }})" class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-100">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button @click="deleteMesse({{ $chant->id }})" class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-100">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>

                <h3 class="text-2xl font-black text-gray-900 leading-tight mb-2">{{ $chant->nom }}</h3>
                <p class="text-gray-400 text-xs font-black uppercase tracking-widest">{{ $chant->partitions->count() }} PARTITIONS</p>

                <div class="mt-10 pt-6 border-t border-gray-50 flex items-center justify-between">
                    <a href="{{ route('admin.rubriques.chants.show', ['rubriqueId' => $rubrique->id, 'chantId' => $chant->id]) }}" 
                       class="text-xs font-black text-primary flex items-center gap-2 group/btn">
                        GERER LE CHANT 
                        <i class="fas fa-arrow-right transform group-hover/btn:translate-x-1 transition-transform"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endif
