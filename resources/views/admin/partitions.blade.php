@extends('layouts.admin')

@section('content')
<div class="py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-10">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Espace <span class="text-primary italic">Partitions</span></h1>
            <p class="mt-2 text-sm text-slate-500 font-medium">Gérez le répertoire musical global et les fichiers associés.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.partitions.create') }}" 
               class="inline-flex items-center gap-2 rounded-2xl bg-primary-gradient px-6 py-4 text-sm font-bold text-white shadow-xl hover:shadow-primary/30 transform hover:-translate-y-1 transition-all">
                <i class="fas fa-plus"></i> Nouvelle Partition
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-8 rounded-2xl bg-green-50 p-4 border border-green-100 shadow-sm animate-fade-in-down">
        <div class="flex items-center">
            <div class="flex-shrink-0 w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center shadow-lg shadow-green-200">
                <i class="fas fa-check text-white"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm font-bold text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/60 overflow-hidden border border-slate-100">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">Partition</th>
                        <th scope="col" class="hidden md:table-cell px-8 py-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">Chorale</th>
                        <th scope="col" class="hidden sm:table-cell px-8 py-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">Catégorie</th>
                        <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">Fichiers</th>
                        <th scope="col" class="relative px-8 py-5">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($partitions as $partition)
                    <tr class="group hover:bg-slate-50/80 transition-colors">
                        <td class="whitespace-nowrap px-8 py-6">
                            <div class="flex items-center">
                                <div class="h-12 w-12 flex-shrink-0 bg-slate-100 rounded-2xl flex items-center justify-center border border-slate-200 group-hover:bg-primary-gradient group-hover:border-transparent transition-all">
                                    <i class="fas fa-file-audio text-slate-400 group-hover:text-white transition-colors"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-slate-900">{{ $partition->title }}</div>
                                    @if($partition->description)
                                        <div class="text-[10px] text-slate-400 font-medium max-w-[200px] truncate">{{ $partition->description }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-8 py-6">
                            <span class="text-sm text-slate-600 font-semibold">{{ $partition->chorale->name ?? '-' }}</span>
                        </td>
                        <td class="hidden sm:table-cell px-8 py-6">
                            @if($partition->category)
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-purple-50 text-[10px] font-bold text-purple-600 border border-purple-100">
                                    <i class="fas fa-layer-group"></i>
                                    {{ $partition->category->name }}
                                </div>
                            @else
                                <span class="text-slate-300">-</span>
                            @endif
                        </td>
                        <td class="px-8 py-6">
                            <div class="flex gap-2">
                                @php $filesByType = $partition->files_by_type ?? []; @endphp
                                @foreach($filesByType as $type => $files)
                                    @if(count($files) > 0)
                                        <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center group/file relative" title="{{ count($files) }} {{ $type }}">
                                            <i class="fas {{ $files[0]['icon'] ?? 'fa-file' }} text-[10px] {{ str_replace('text-', 'text-', $files[0]['text_color'] ?? 'text-slate-400') }}"></i>
                                            <span class="absolute -top-1 -right-1 w-4 h-4 bg-primary text-white text-[8px] font-black rounded-full flex items-center justify-center border border-white">
                                                {{ count($files) }}
                                            </span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-8 py-6 text-right text-sm font-medium">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.partitions.show', $partition->id) }}" 
                                   class="p-3 bg-slate-50 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-2xl transition-all border border-slate-100">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.partitions.edit', $partition->id) }}" 
                                   class="p-3 bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-2xl transition-all border border-slate-100">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.partitions.delete', $partition->id) }}" method="POST" onsubmit="return confirm('Supprimer cette partition ?')" class="inline">
                                    @csrf
                                    <button type="submit" class="p-3 bg-slate-50 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-2xl transition-all border border-slate-100">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-8 py-20 text-center">
                            <i class="fas fa-music text-slate-100 text-6xl mb-4 block"></i>
                            <h3 class="text-slate-400 font-bold">Aucune partition disponible</h3>
                            <p class="text-slate-300 text-sm">Le répertoire est vide pour le moment.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($partitions->hasPages())
        <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
            {{ $partitions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection