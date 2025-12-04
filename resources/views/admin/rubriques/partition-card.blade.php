<div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all hover:border-primary">
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1 min-w-0">
            <h4 class="font-semibold text-gray-900 mb-1 truncate">{{ $partition->title }}</h4>
            @if($partition->pupitre)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                    <i class="fas fa-users mr-1 text-xs"></i>{{ $partition->pupitre->nom }}
                </span>
            @endif
        </div>
    </div>
    @if($partition->description)
        <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ Str::limit($partition->description, 100) }}</p>
    @endif
    @if($partition->files && count($partition->files) > 0)
        <div class="flex flex-wrap gap-2 mb-3">
            @foreach(array_slice($partition->files, 0, 3) as $file)
                @php
                    $fileType = \App\Helpers\FileHelper::getFileType($file['name'] ?? $file);
                    $icon = \App\Helpers\FileHelper::getFileIcon($file['name'] ?? $file);
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 bg-gray-100 text-gray-700 text-xs rounded-lg">
                    <i class="fas {{ $icon }} mr-1.5 text-primary"></i>
                    <span class="truncate max-w-[120px]">{{ $file['name'] ?? basename($file) }}</span>
                </span>
            @endforeach
            @if(count($partition->files) > 3)
                <span class="inline-flex items-center px-2.5 py-1 bg-primary/10 text-primary text-xs rounded-lg font-medium">
                    +{{ count($partition->files) - 3 }} autre(s)
                </span>
            @endif
        </div>
    @endif
    <div class="flex space-x-2 pt-3 border-t border-gray-100">
        <button onclick="viewPartition({{ $partition->id }})" 
                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow">
            <i class="fas fa-eye mr-1.5"></i>Voir
        </button>
        <button onclick="editPartition({{ $partition->id }})" 
                class="flex-1 bg-gray-500 hover:bg-gray-600 text-white px-3 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow">
            <i class="fas fa-edit mr-1.5"></i>Modifier
        </button>
    </div>
</div>

