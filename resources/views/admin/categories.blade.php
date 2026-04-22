@extends('layouts.admin')

@section('content')
<div class="py-12 px-4 sm:px-6 lg:px-8">
    <div class="sm:flex sm:items-center sm:justify-between mb-10">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Gestion des <span class="text-primary italic">Catégories</span></h1>
            <p class="mt-2 text-sm text-slate-500 font-medium">Configurez les rubriques et taxonomies du répertoire musical.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.categories.create') }}" 
               class="inline-flex items-center gap-2 rounded-2xl bg-primary-gradient px-6 py-4 text-sm font-bold text-white shadow-xl hover:shadow-primary/30 transform hover:-translate-y-1 transition-all">
                <i class="fas fa-plus"></i> Nouvelle Catégorie
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
                        <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">Catégorie</th>
                        <th scope="col" class="hidden md:table-cell px-8 py-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">Description</th>
                        <th scope="col" class="px-8 py-5 text-left text-xs font-bold text-slate-400 uppercase tracking-widest">Utilisation</th>
                        <th scope="col" class="relative px-8 py-5">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($categories as $category)
                    <tr class="group hover:bg-slate-50/80 transition-colors">
                        <td class="whitespace-nowrap px-8 py-6">
                            <div class="flex items-center">
                                <div class="h-12 w-12 flex-shrink-0 rounded-2xl flex items-center justify-center shadow-lg transition-transform group-hover:scale-110" 
                                     style="background-color: {{ $category->color ?? '#9333ea' }}; shadow-color: {{ $category->color ?? '#9333ea' }}44">
                                    <i class="fas {{ $category->icon ?? 'fa-tag' }} text-white text-lg"></i>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-slate-900">{{ $category->name }}</div>
                                    <div class="text-[10px] uppercase font-bold text-slate-300 tracking-wider">Code: {{ strtoupper($category->icon ?? 'TAG') }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="hidden md:table-cell px-8 py-6">
                            <div class="text-sm text-slate-600 max-w-sm italic">{{ $category->description ?? 'Pas de description.' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-8 py-6">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                                    <i class="fas fa-file-lines mr-1.5 opacity-50"></i>
                                    {{ $category->partitions_count ?? 0 }} partitions
                                </span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-8 py-6 text-right text-sm font-medium">
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('admin.categories.edit', $category->id) }}" 
                                   class="p-3 bg-slate-50 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-2xl transition-all border border-slate-100">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.categories.delete', $category->id) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?')" class="inline">
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
                        <td colspan="4" class="px-8 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-tags text-slate-200 text-3xl"></i>
                                </div>
                                <h3 class="text-slate-400 font-bold">Aucune catégorie</h3>
                                <p class="text-slate-300 text-sm">Définissez vos premières rubriques.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="px-8 py-6 bg-slate-50/50 border-t border-slate-100">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>
@endsection