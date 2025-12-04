@props(['files', 'partitionId'])

@php
    use App\Helpers\FileHelper;
    
    if (empty($files)) {
        $files = [];
    }
    
    // Si c'est un tableau simple de chemins, convertir en format avec métadonnées
    $filesWithMetadata = [];
    foreach ($files as $index => $file) {
        if (is_array($file) && isset($file['path'])) {
            // S'assurer que tous les fichiers ont une URL
            $file['url'] = $file['url'] ?? asset('storage/' . $file['path']);
            $file['name'] = $file['name'] ?? basename($file['path']);
            $file['type'] = $file['type'] ?? FileHelper::getFileType($file['path']);
            $file['icon'] = $file['icon'] ?? FileHelper::getFileIcon($file['path']);
            $file['color_class'] = $file['color_class'] ?? FileHelper::getFileColorClass($file['path']);
            $file['type_label'] = $file['type_label'] ?? FileHelper::getFileTypeLabel($file['path']);
            $file['index'] = $file['index'] ?? $index;
            $filesWithMetadata[] = $file;
        } else {
            $path = is_array($file) ? ($file['path'] ?? $file) : $file;
            $filesWithMetadata[] = [
                'path' => $path,
                'url' => asset('storage/' . $path),
                'name' => basename($path),
                'type' => FileHelper::getFileType($path),
                'icon' => FileHelper::getFileIcon($path),
                'color_class' => FileHelper::getFileColorClass($path),
                'type_label' => FileHelper::getFileTypeLabel($path),
                'index' => $index,
            ];
        }
    }
    
    // Grouper par type
    $groupedFiles = [];
    foreach ($filesWithMetadata as $file) {
        $type = $file['type'] ?? FileHelper::TYPE_OTHER;
        if (!isset($groupedFiles[$type])) {
            $groupedFiles[$type] = [];
        }
        $groupedFiles[$type][] = $file;
    }
@endphp

@if(empty($filesWithMetadata))
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="text-center py-12">
            <div class="text-gray-400 text-6xl mb-4">📁</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun fichier</h3>
            <p class="text-gray-500">Aucun fichier n'est associé à cet élément.</p>
        </div>
    </div>
@else
    @foreach($groupedFiles as $type => $typeFiles)
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                    <i class="fas {{ $typeFiles[0]['icon'] ?? 'fa-file' }} mr-2 text-2xl"></i>
                    {{ $typeFiles[0]['type_label'] ?? ucfirst($type) }} ({{ count($typeFiles) }})
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    @foreach($typeFiles as $file)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-sm font-medium text-gray-900">{{ $file['name'] }}</h4>
                                <div class="flex space-x-2">
                                    <a href="{{ $file['url'] }}" 
                                       class="text-blue-600 hover:text-blue-800 text-sm" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ $file['url'] }}" 
                                       class="text-green-600 hover:text-green-800 text-sm" download>
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                            
                            @if($type === FileHelper::TYPE_AUDIO)
                                <audio controls class="w-full">
                                    <source src="{{ $file['url'] }}" type="audio/mpeg">
                                    <source src="{{ $file['url'] }}" type="audio/wav">
                                    <source src="{{ $file['url'] }}" type="audio/ogg">
                                    Votre navigateur ne supporte pas l'élément audio.
                                </audio>
                            @elseif($type === FileHelper::TYPE_PDF)
                                <div class="aspect-w-16 aspect-h-20 bg-gray-200 rounded">
                                    <iframe src="{{ $file['url'] }}" 
                                            class="w-full h-48 rounded" frameborder="0"></iframe>
                                </div>
                            @elseif($type === FileHelper::TYPE_IMAGE)
                                <div class="aspect-w-16 aspect-h-12 bg-gray-200 rounded overflow-hidden">
                                    <img src="{{ $file['url'] }}" 
                                         alt="{{ $file['name'] }}" 
                                         class="w-full h-32 object-cover rounded cursor-pointer"
                                         onclick="openImageModal('{{ $file['url'] }}')">
                                </div>
                            @else
                                <div class="flex items-center justify-center h-32 bg-gray-200 rounded">
                                    <i class="fas {{ $file['icon'] ?? 'fa-file' }} text-4xl text-gray-400"></i>
                                </div>
                            @endif
                            
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $file['color_class'] ?? 'bg-gray-100 text-gray-800' }}">
                                    <i class="fas {{ $file['icon'] ?? 'fa-file' }} mr-1"></i>
                                    {{ $file['type_label'] ?? 'Fichier' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@endif

