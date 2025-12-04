<div id="section-{{ $section->id }}" class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-900">{{ $section->nom }}</h3>
            @if($section->description)
                <p class="text-sm text-gray-600 mt-1">{{ $section->description }}</p>
            @endif
        </div>
        <div class="flex space-x-2">
            <button @click="showPartitionModal = true; selectedSection = {{ $section->id }}" 
                    class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-plus mr-2"></i>Ajouter partition
            </button>
            <button @click="editSection({{ $section->id }})" 
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-edit"></i>
            </button>
            <button @click="deleteSection({{ $section->id }})" 
                    class="bg-red-200 hover:bg-red-300 text-red-700 px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>

    <!-- Partitions de la section -->
    @if($section->partitions->isEmpty())
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <p class="text-gray-500 mb-4">Aucune partition dans cette section</p>
            <button @click="showPartitionModal = true; selectedSection = {{ $section->id }}" 
                    class="bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                <i class="fas fa-plus mr-2"></i>Ajouter une partition
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($section->partitions as $partition)
                @include('admin.rubriques.partition-card', ['partition' => $partition])
            @endforeach
        </div>
    @endif
</div>

