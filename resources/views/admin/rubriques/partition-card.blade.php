<div class="group bg-white rounded-[2rem] p-6 border border-gray-100 shadow-sm hover:shadow-xl transition-all h-full flex flex-col">
    <div class="flex items-start justify-between mb-4">
        <div class="flex-1 min-w-0">
            <h4 class="font-black text-gray-900 leading-tight mb-2 truncate group-hover:text-primary transition-colors" title="{{ $partition->title }}">
                {{ $partition->title }}
            </h4>
            @if($partition->pupitre)
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-primary/5 text-primary text-[10px] font-black uppercase tracking-widest border border-primary/10">
                    <i class="fas fa-users text-[8px]"></i>
                    {{ $partition->pupitre->nom }}
                </div>
            @endif
        </div>
    </div>

    @if($partition->description)
        <p class="text-xs text-gray-500 mb-6 line-clamp-2 leading-relaxed flex-grow">{{ $partition->description }}</p>
    @else
        <div class="flex-grow"></div>
    @endif

    @if($partition->files && count($partition->files) > 0)
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach(array_slice($partition->files, 0, 2) as $file)
                @php
                    $icon = \App\Helpers\FileHelper::getFileIcon($file['name'] ?? $file);
                @endphp
                <div class="flex items-center gap-2 p-2 rounded-xl bg-gray-50 border border-gray-100 max-w-full">
                    <i class="fas {{ $icon }} text-primary text-[10px]"></i>
                    <span class="text-[9px] font-bold text-gray-600 truncate max-w-[80px]">{{ $file['name'] ?? basename($file) }}</span>
                </div>
            @endforeach
            @if(count($partition->files) > 2)
                <div class="flex items-center justify-center w-8 h-8 rounded-xl bg-primary/10 text-primary text-[10px] font-black">
                    +{{ count($partition->files) - 2 }}
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-3 gap-2 pt-6 border-t border-gray-50">
        <button type="button" onclick="event.stopPropagation(); viewPartition({{ $partition->id }})" 
                class="flex items-center justify-center gap-2 bg-gray-50 hover:bg-primary-gradient hover:text-white text-gray-400 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all" title="VOIR">
            <i class="fas fa-eye"></i>
        </button>
        <button type="button" onclick="event.stopPropagation(); editPartition({{ $partition->id }})" 
                class="flex items-center justify-center gap-2 bg-gray-50 hover:bg-blue-500 hover:text-white text-gray-400 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all" title="EDITER">
            <i class="fas fa-edit"></i>
        </button>
        <button type="button" onclick="event.stopPropagation(); deletePartition({{ $partition->id }})" 
                class="flex items-center justify-center gap-2 bg-red-50 hover:bg-red-500 hover:text-white text-red-400 py-3 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all" title="SUPPRIMER">
            <i class="fas fa-trash"></i>
        </button>
    </div>
</div>
