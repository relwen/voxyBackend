@extends('layouts.admin')

@section('title', 'Gestion des Utilisateurs - VoXY Admin')
@section('page-title', 'Gestion des Utilisateurs')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Utilisateurs</h1>
        <p class="text-gray-500 mt-1">Gérez les membres, approuvez les inscriptions et contrôlez les accès.</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-primary-gradient px-6 py-3 text-sm font-bold text-white shadow-lg hover:opacity-90 transition-all">
        <i class="fas fa-plus mr-2"></i> Nouvel Utilisateur
    </a>
</div>

@if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-700 px-6 py-4 rounded-2xl flex items-center shadow-sm animate-fade-in">
        <i class="fas fa-check-circle mr-3 text-xl"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-100 text-red-700 px-6 py-4 rounded-2xl flex items-center shadow-sm animate-fade-in">
        <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
@endif

<div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100">
                    <th class="px-8 py-5 text-xs font-bold text-gray-500 uppercase tracking-widest">Membre</th>
                    <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-widest hidden md:table-cell">Chorale</th>
                    <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-widest hidden lg:table-cell">Partie</th>
                    <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-widest">Statut</th>
                    <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                <tr class="group hover:bg-gray-50/50 transition-colors">
                    <td class="px-8 py-5 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-12 w-12 shrink-0 rounded-2xl bg-primary-gradient p-[2px] shadow-sm transform group-hover:rotate-3 transition-transform">
                                <div class="h-full w-full rounded-[14px] bg-white flex items-center justify-center">
                                    <span class="text-primary font-black text-sm">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900 group-hover:text-primary transition-colors">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ $user->phone }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap hidden md:table-cell">
                        @if($user->chorale)
                            <div class="flex items-center text-sm text-gray-700">
                                <div class="w-1.5 h-1.5 rounded-full bg-primary mr-2"></div>
                                {{ $user->chorale->name }}
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Non assigné</span>
                        @endif
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap hidden lg:table-cell">
                        <span class="text-sm font-medium text-gray-600">{{ $user->voice_part ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                @if($user->status === 'approved') bg-emerald-100 text-emerald-700
                                @elseif($user->status === 'pending') bg-amber-100 text-amber-700
                                @else bg-red-100 text-red-700 @endif">
                                @if($user->status === 'approved') Approuvé
                                @elseif($user->status === 'pending') En attente
                                @else Rejeté @endif
                            </span>
                            @if($user->role === 'admin')
                                <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-700">
                                    Administrateur
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2 px-2" x-data="{ open: false }">
                            <div class="relative">
                                <button @click="open = !open" class="h-8 w-8 flex items-center justify-center rounded-lg hover:bg-gray-200 transition-colors text-gray-500">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" 
                                     class="absolute right-0 z-20 mt-2 w-48 rounded-2xl bg-white p-2 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden" x-cloak>
                                    
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-xl transition-colors">
                                        <i class="fas fa-edit mr-3 text-blue-500 opacity-70"></i> Modifier
                                    </a>
                                    
                                    @if($user->status === 'pending')
                                        <form method="POST" action="{{ route('admin.users.approve', $user->id) }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-emerald-700 hover:bg-emerald-50 rounded-xl transition-colors">
                                                <i class="fas fa-check mr-3 text-emerald-500 opacity-70"></i> Approuver
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.reject', $user->id) }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50 rounded-xl transition-colors">
                                                <i class="fas fa-times mr-3 text-red-500 opacity-70"></i> Rejeter
                                            </button>
                                        </form>
                                    @endif

                                    @if($user->is_active)
                                        <form method="POST" action="{{ route('admin.users.deactivate', $user->id) }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-orange-700 hover:bg-orange-50 rounded-xl transition-colors">
                                                <i class="fas fa-pause mr-3 text-orange-500 opacity-70"></i> Désactiver
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.users.activate', $user->id) }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 rounded-xl transition-colors">
                                                <i class="fas fa-play mr-3 text-blue-500 opacity-70"></i> Activer
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <div class="h-px bg-gray-100 my-1"></div>
                                    
                                    @if($user->id !== Auth::id())
                                        <form method="POST" action="{{ route('admin.users.delete', $user->id) }}" class="block" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors font-bold">
                                                <i class="fas fa-trash-alt mr-3 opacity-70"></i> Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
    <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection