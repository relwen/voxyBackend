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
            'directSections.vocalises',
            'sections.partitions',
            'dossiers.sections.partitions',
            'dossiers.sections.sections'
        ])->orderBy('name')->get() : collect();
    }
@endphp

<!-- Sidebar for mobile -->
<div x-show="sidebarOpen" class="relative z-50 lg:hidden" role="dialog" aria-modal="true">
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm"></div>

    <div x-show="sidebarOpen"
         x-transition:enter="transition ease-in-out duration-300 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in-out duration-300 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-0 flex">
        
        <div class="relative mr-16 flex w-full max-w-xs flex-1">
            <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <i class="fas fa-times text-white text-xl"></i>
                </button>
            </div>

            <!-- Mobile Sidebar component -->
            <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-primary-gradient px-6 pb-4 shadow-2xl">
                <div class="flex h-16 shrink-0 items-center mt-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-lg transform rotate-3">
                        <i class="fas fa-microphone-lines text-primary text-lg"></i>
                    </div>
                    <span class="ml-3 text-2xl font-black text-white tracking-tight">VoXY <span class="font-light">Maestro</span></span>
                </div>
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="flex flex-1 flex-col gap-y-7 mt-4">
                        <li>
                            <ul role="list" class="-mx-2 space-y-2">
                                <a href="{{ route('admin.chorale.config') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.chorale.config') ? 'bg-white/20 shadow-lg' : '' }}">
                                    <i class="fas fa-sliders w-5"></i> Configuration
                                </a>
                                <a href="{{ route('admin.maestro.users') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.maestro.users*') ? 'bg-white/20 shadow-lg' : '' }}">
                                    <i class="fas fa-users-gear w-5"></i> Utilisateurs
                                </a>
                            </ul>
                        </li>
                        @if($rubriques->isNotEmpty())
                        <li>
                            <div class="text-xs font-black uppercase tracking-widest text-white/50 mb-4 px-2">Rubriques</div>
                            <ul role="list" class="-mx-2 space-y-2">
                                @foreach($rubriques as $rubrique)
                                    @php $isActive = request()->routeIs('admin.rubriques.show') && request()->route('id') == $rubrique->id; @endphp
                                    <a href="{{ route('admin.rubriques.show', $rubrique->id) }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ $isActive ? 'bg-white/20 shadow-lg' : '' }}">
                                        @php
                                            $iconMap = ['church' => 'church', 'mic' => 'microphone', 'music_note' => 'music', 'folder' => 'folder'];
                                            $iconName = $rubrique->icon ?? 'folder';
                                            $faIconName = $iconMap[$iconName] ?? str_replace('_', '-', $iconName);
                                            if (strpos($faIconName, 'fa-') === 0) $faIconName = substr($faIconName, 3);
                                        @endphp
                                        <i class="fas fa-{{ $faIconName }} w-5 opacity-80" style="color: {{ $rubrique->color ?? 'white' }}"></i>
                                        <span class="truncate">{{ $rubrique->name }}</span>
                                    </a>
                                @endforeach
                            </ul>
                        </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Static sidebar for desktop -->
<div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-primary-gradient px-8 pb-4 shadow-2xl">
        <div class="flex h-20 shrink-0 items-center mt-6">
            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-2xl transform rotate-3">
                <i class="fas fa-microphone-lines text-primary text-xl"></i>
            </div>
            <span class="ml-4 text-2xl font-black text-white tracking-tighter">VoXY <span class="font-light opacity-80">Maestro</span></span>
        </div>
        
        <nav class="flex flex-1 flex-col mt-8">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
                <li>
                    <ul role="list" class="-mx-2 space-y-2">
                        <a href="{{ route('admin.chorale.config') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.chorale.config') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                            <i class="fas fa-sliders w-6 text-lg"></i> Configuration
                        </a>
                        <a href="{{ route('admin.maestro.users') }}" class="flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ request()->routeIs('admin.maestro.users*') ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                            <i class="fas fa-users-gear w-6 text-lg"></i> Utilisateurs
                        </a>
                    </ul>
                </li>

                @if($rubriques->isNotEmpty())
                <li>
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40 mb-4 px-4">Bibliothèque Chorale</div>
                    <ul role="list" class="-mx-2 space-y-1">
                        @foreach($rubriques as $rubrique)
                            @php $isActive = (request()->routeIs('admin.rubriques.*') || request()->is('admin/rubriques/'.$rubrique->id.'*')) && request()->route('id') == $rubrique->id; @endphp
                            <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                                <div class="flex items-center group">
                                    <a href="{{ route('admin.rubriques.show', $rubrique->id) }}" class="flex-1 flex items-center gap-x-3 rounded-xl px-4 py-3 text-sm font-semibold leading-6 text-white hover:bg-white/10 transition-all {{ $isActive ? 'bg-white/20 shadow-lg translate-x-2' : '' }}">
                                        @php
                                            $iconName = $rubrique->icon ?? 'folder';
                                            $faIconName = $iconMap[$iconName] ?? str_replace('_', '-', $iconName);
                                            if (strpos($faIconName, 'fa-') === 0) $faIconName = substr($faIconName, 3);
                                        @endphp
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-white/10 group-hover:bg-white/20 transition-colors">
                                            <i class="fas fa-{{ $faIconName }} text-sm" style="color: {{ $rubrique->color ?? 'white' }}"></i>
                                        </div>
                                        <span class="truncate">{{ $rubrique->name }}</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </ul>
                </li>
                @endif

                <li class="mt-auto">
                    <div class="rounded-2xl bg-white/10 p-4 border border-white/10 backdrop-blur-sm shadow-inner group">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 shrink-0 rounded-xl bg-white/20 flex items-center justify-center font-black text-white text-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[10px] text-white/50 truncate">{{ $chorale->name ?? 'Maestro' }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 text-white/50 hover:text-white transition-colors">
                                    <i class="fas fa-right-from-bracket"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            </ul>
        </nav>
    </div>
</div>

