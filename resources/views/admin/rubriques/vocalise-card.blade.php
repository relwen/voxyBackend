<div class="bg-gradient-to-br from-white to-gray-50 rounded-lg shadow-md hover:shadow-lg transition-all border border-gray-200 p-4">
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1 min-w-0">
            <h4 class="text-lg font-semibold text-gray-900 mb-1 truncate" title="{{ $vocalise->title }}">
                {{ $vocalise->title }}
            </h4>
            @if($vocalise->description)
                <p class="text-sm text-gray-600 line-clamp-2 mb-2">{{ $vocalise->description }}</p>
            @endif
            <div class="flex items-center space-x-2">
                @if($vocalise->pupitre)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $vocalise->pupitre->color ?? '#E3F2FD' }}20; color: {{ $vocalise->pupitre->color ?? '#1976D2' }};">
                        <i class="fas fa-users mr-1"></i>{{ $vocalise->pupitre->nom }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                        <i class="fas fa-users mr-1"></i>Aucun pupitre
                    </span>
                @endif
            </div>
        </div>
        <div class="flex space-x-1 ml-2">
            <button onclick="deleteVocalise({{ $vocalise->id }})" 
                    class="text-red-600 hover:text-red-800 hover:bg-red-50 p-2 rounded-lg transition-colors"
                    title="Supprimer">
                <i class="fas fa-trash text-sm"></i>
            </button>
        </div>
    </div>
    
    @if($vocalise->audio_path)
        <div class="mt-3 pt-3 border-t border-gray-200">
            <audio id="audio-{{ $vocalise->id }}" 
                   src="{{ asset('storage/' . $vocalise->audio_path) }}" 
                   controls 
                   class="w-full h-10"
                   preload="metadata">
                Votre navigateur ne supporte pas l'élément audio.
            </audio>
        </div>
    @else
        <div class="mt-3 pt-3 border-t border-gray-200">
            <p class="text-xs text-gray-500 italic">
                <i class="fas fa-exclamation-triangle mr-1"></i>Aucun fichier audio
            </p>
        </div>
    @endif
</div>

