@php
    if (!isset($user)) {
        $user = Auth::user();
    }
    if (!isset($chorale)) {
        $chorale = $user->chorale;
    }
    if (!isset($rubriques)) {
        $rubriques = $chorale ? $chorale->categories()->with([
            'directSections.partitions', 
            'directSections.vocalises', // Pour les vocalises
            'sections.partitions',
            'dossiers.sections.partitions',
            'dossiers.sections.sections'
        ])->orderBy('name')->get() : collect();
    }
@endphp

<style>
    .bg-primary-gradient { 
        background: linear-gradient(135deg, rgb(78, 13, 4), rgb(179, 5, 5), rgb(158, 2, 80)); 
    }
    .text-primary { 
        color: rgb(158, 2, 80); 
    }
</style>

<div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0">
    <div class="flex items-center justify-center h-16 bg-primary-gradient">
        <h1 class="text-xl font-bold text-white">VoXY Maestro</h1>
    </div>
    <nav class="mt-8 overflow-y-auto" style="max-height: calc(100vh - 4rem);">
        <div class="px-4 space-y-2">
            <a href="{{ route('admin.chorale.config') }}" 
               class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors {{ request()->routeIs('admin.chorale.config') ? 'bg-gray-100 text-primary border-r-4 border-primary' : '' }}">
                <i class="fas fa-cog w-5 h-5 mr-3"></i>Configuration
            </a>
            
            <a href="{{ route('admin.maestro.users') }}" 
               class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors {{ request()->routeIs('admin.maestro.users*') ? 'bg-gray-100 text-primary border-r-4 border-primary' : '' }}">
                <i class="fas fa-users w-5 h-5 mr-3"></i>Utilisateurs
            </a>
            
            @if($rubriques->isNotEmpty())
                <div class="pt-4">
                    <h3 class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rubriques</h3>
                    @foreach($rubriques as $rubrique)
                        @php
                            $isActive = request()->routeIs('admin.rubriques.show') && request()->route('id') == $rubrique->id;
                        @endphp
                        <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                            <button @click="open = !open" 
                                    class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors {{ $isActive ? 'bg-gray-100 text-primary' : '' }}">
                                <div class="flex items-center">
                                    @php
                                        // Mapping des icônes Material Icons vers Font Awesome
                                        $iconMap = [
                                            'church' => 'church',
                                            'mic' => 'microphone',
                                            'music_note' => 'music',
                                            'folder' => 'folder',
                                            'folder_open' => 'folder-open',
                                        ];
                                        $iconName = $rubrique->icon ?? 'folder';
                                        $faIconName = $iconMap[$iconName] ?? str_replace('_', '-', $iconName);
                                        // Si l'icône commence déjà par 'fa-', enlever le préfixe
                                        if (strpos($faIconName, 'fa-') === 0) {
                                            $faIconName = substr($faIconName, 3);
                                        }
                                    @endphp
                                    <i class="fas fa-{{ $faIconName }} mr-3 text-lg" style="color: {{ $rubrique->color ?? '#666' }}"></i>
                                    <span class="font-medium">{{ $rubrique->name }}</span>
                                </div>
                                <i class="fas fa-chevron-down text-xs transition-transform" :class="open ? 'transform rotate-180' : ''"></i>
                            </button>
                            
                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 transform -translate-y-1"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 transform translate-y-0"
                                 x-transition:leave-end="opacity-0 transform -translate-y-1"
                                 class="ml-4 mt-1 space-y-1">
                                <a href="{{ route('admin.rubriques.show', $rubrique->id) }}" 
                                   class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors {{ $isActive ? 'bg-gray-50 text-primary' : '' }}">
                                    <i class="fas fa-list w-4 h-4 mr-2"></i>Toutes les sections
                                </a>
                                @php
                                    // Pour la rubrique "Messes", "Vocalises" et "Chants", utiliser directSections
                                    // Pour les rubriques avec dossiers, afficher les dossiers
                                    // Pour les autres, utiliser sections
                                    $isMesses = strtolower($rubrique->name) === 'messes';
                                    $isVocalises = strtolower($rubrique->name) === 'vocalises';
                                    $isChants = strtolower($rubrique->name) === 'chants';
                                    $hasDossiers = $rubrique->hasDossiers();
                                @endphp
                                
                                @if($hasDossiers)
                                    {{-- Afficher les dossiers --}}
                                    @foreach($rubrique->dossiers->take(10) as $dossier)
                                        <div x-data="{ dossierOpen: false }">
                                            <button @click="dossierOpen = !dossierOpen" 
                                                    class="w-full flex items-center justify-between px-4 py-2 text-sm text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                                <div class="flex items-center">
                                                    <i class="fas fa-folder w-4 h-4 mr-2 text-yellow-500"></i>
                                                    <span>{{ $dossier->nom }}</span>
                                                </div>
                                                <i class="fas fa-chevron-down text-xs transition-transform" :class="dossierOpen ? 'transform rotate-180' : ''"></i>
                                            </button>
                                            <div x-show="dossierOpen" 
                                                 x-transition:enter="transition ease-out duration-200"
                                                 x-transition:enter-start="opacity-0 transform -translate-y-1"
                                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                                 x-transition:leave="transition ease-in duration-150"
                                                 x-transition:leave-start="opacity-100 transform translate-y-0"
                                                 x-transition:leave-end="opacity-0 transform -translate-y-1"
                                                 class="ml-4 mt-1 space-y-1">
                                                <a href="{{ route('admin.rubriques.show', $rubrique->id) }}#dossier-{{ $dossier->id }}" 
                                                   class="flex items-center px-4 py-2 text-xs text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                                                    <i class="fas fa-eye w-3 h-3 mr-2"></i>Ouvrir le dossier
                                                </a>
                                                @foreach($dossier->sections->where('type', 'section')->take(5) as $section)
                                                    <a href="{{ route('admin.rubriques.show', $rubrique->id) }}#section-{{ $section->id }}" 
                                                       class="flex items-center px-4 py-2 text-xs text-gray-500 rounded-lg hover:bg-gray-50 transition-colors">
                                                        <i class="fas fa-file-alt w-3 h-3 mr-2"></i>{{ $section->nom }}
                                                        <span class="ml-auto text-xs text-gray-400">{{ $section->partitions->count() }}</span>
                                                    </a>
                                                @endforeach
                                                @foreach($dossier->sections->where('type', 'dossier')->take(3) as $sousDossier)
                                                    <a href="{{ route('admin.rubriques.show', $rubrique->id) }}#dossier-{{ $sousDossier->id }}" 
                                                       class="flex items-center px-4 py-2 text-xs text-yellow-600 rounded-lg hover:bg-yellow-50 transition-colors">
                                                        <i class="fas fa-folder w-3 h-3 mr-2"></i>{{ $sousDossier->nom }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($rubrique->dossiers->count() > 10)
                                        <a href="{{ route('admin.rubriques.show', $rubrique->id) }}" 
                                           class="flex items-center px-4 py-2 text-sm text-primary rounded-lg hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-ellipsis-h w-4 h-4 mr-2"></i>Voir plus...
                                        </a>
                                    @endif
                                @else
                                    @php
                                        // Pour Messes, Vocalises et Chants, utiliser directSections
                                        $sectionsToShow = ($isMesses || $isVocalises || $isChants) ? $rubrique->directSections->take(10) : $rubrique->sections->take(10);
                                    @endphp
                                    @foreach($sectionsToShow as $section)
                                        @if($isMesses)
                                            {{-- Lien vers la page de détails de la messe --}}
                                            <a href="{{ route('admin.rubriques.messes.show', ['rubriqueId' => $rubrique->id, 'messeId' => $section->id]) }}" 
                                               class="flex items-center px-4 py-2 text-sm text-gray-500 rounded-lg hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-church w-4 h-4 mr-2"></i>{{ $section->nom }}
                                                <span class="ml-auto text-xs text-gray-400">{{ $section->partitions->count() }}</span>
                                            </a>
                                        @elseif($isVocalises)
                                            {{-- Lien vers la page de détails de la vocalise --}}
                                            <a href="{{ route('admin.rubriques.vocalises.show', ['rubriqueId' => $rubrique->id, 'vocaliseId' => $section->id]) }}" 
                                               class="flex items-center px-4 py-2 text-sm text-gray-500 rounded-lg hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-music-note w-4 h-4 mr-2"></i>{{ $section->nom }}
                                                <span class="ml-auto text-xs text-gray-400">{{ $section->vocalises->count() }}</span>
                                            </a>
                                        @elseif($isChants)
                                            {{-- Lien vers la page de détails du chant --}}
                                            <a href="{{ route('admin.rubriques.chants.show', ['rubriqueId' => $rubrique->id, 'chantId' => $section->id]) }}" 
                                               class="flex items-center px-4 py-2 text-sm text-gray-500 rounded-lg hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-music w-4 h-4 mr-2"></i>{{ $section->nom }}
                                                <span class="ml-auto text-xs text-gray-400">{{ $section->partitions->count() }}</span>
                                            </a>
                                        @else
                                            {{-- Lien vers la section dans la rubrique --}}
                                            <a href="{{ route('admin.rubriques.show', $rubrique->id) }}#section-{{ $section->id }}" 
                                               class="flex items-center px-4 py-2 text-sm text-gray-500 rounded-lg hover:bg-gray-50 transition-colors">
                                                <i class="fas fa-file-alt w-4 h-4 mr-2"></i>{{ $section->nom }}
                                                <span class="ml-auto text-xs text-gray-400">{{ $section->partitions->count() }}</span>
                                            </a>
                                        @endif
                                    @endforeach
                                    @php
                                        // Pour Messes, Vocalises et Chants, utiliser directSections
                                        $totalSections = ($isMesses || $isVocalises || $isChants) ? $rubrique->directSections->count() : $rubrique->sections->count();
                                    @endphp
                                    @if($totalSections > 10)
                                        <a href="{{ route('admin.rubriques.show', $rubrique->id) }}" 
                                           class="flex items-center px-4 py-2 text-sm text-primary rounded-lg hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-ellipsis-h w-4 h-4 mr-2"></i>Voir plus...
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-4 py-3 text-sm text-gray-500">
                    <p>Aucune rubrique créée</p>
                    <a href="{{ route('admin.chorale.config') }}" class="text-primary hover:underline mt-2 block">
                        Créer une rubrique
                    </a>
                </div>
            @endif
        </div>
    </nav>
    
    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gray-50 border-t">
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-600">{{ $user->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </div>
</div>

