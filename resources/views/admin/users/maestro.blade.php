@extends('layouts.maestro')

@section('title', 'Gestion des Utilisateurs - VoXY Maestro')
@section('page-title', 'Gestion des Utilisateurs')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Utilisateurs de la chorale</h1>
        <p class="text-gray-500 mt-1">Gérez les membres de {{ Auth::user()->chorale->name ?? 'votre chorale' }}.</p>
    </div>
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
                    <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-widest hidden md:table-cell">Partie</th>
                    <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-widest">Statut / Rôle</th>
                    <th class="px-6 py-5 text-xs font-bold text-gray-500 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $userItem)
                <tr class="group hover:bg-gray-50/50 transition-colors">
                    <td class="px-8 py-5 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-12 w-12 shrink-0 rounded-2xl bg-primary-gradient p-[2px] shadow-sm transform group-hover:rotate-3 transition-transform">
                                <div class="h-full w-full rounded-[14px] bg-white flex items-center justify-center">
                                    <span class="text-primary font-black text-sm">{{ substr($userItem->name, 0, 1) }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900 group-hover:text-primary transition-colors">{{ $userItem->name }}</div>
                                <div class="text-xs text-gray-500">{{ $userItem->email }}</div>
                                @if($userItem->phone)
                                    <div class="text-[10px] text-gray-400 mt-0.5">{{ $userItem->phone }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap hidden md:table-cell">
                        <span class="text-sm font-medium text-gray-600">{{ $userItem->voice_part ?? '-' }}</span>
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap">
                        <div class="flex flex-col gap-1.5">
                            <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                @if($userItem->status === 'approved') bg-emerald-100 text-emerald-700
                                @elseif($userItem->status === 'pending') bg-amber-100 text-amber-700
                                @else bg-red-100 text-red-700 @endif">
                                @if($userItem->status === 'approved') Approuvé
                                @elseif($userItem->status === 'pending') En attente
                                @else Rejeté @endif
                            </span>
                            @if($userItem->role === 'maestro')
                                <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700">
                                    Maestro
                                </span>
                            @elseif($userItem->role === 'admin')
                                <span class="inline-flex items-center w-fit px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-100 text-purple-700">
                                    Administrateur
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-2 px-2" x-data="{ open: false }">
                            <div class="relative">
                                <button @click="open = !open" class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-gray-100 shadow-sm hover:bg-gray-50 hover:border-gray-200 transition-all text-gray-500 hover:text-primary z-30">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" 
                                     class="absolute right-0 z-20 mt-2 w-48 rounded-2xl bg-white p-2 shadow-2xl ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden" x-cloak>
                                    
                                    @if($userItem->status === 'pending')
                                        <form method="POST" action="{{ route('admin.maestro.users.approve', $userItem->id) }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm text-emerald-700 hover:bg-emerald-50 rounded-xl transition-colors font-bold">
                                                <i class="fas fa-check mr-3 text-emerald-500 opacity-70"></i> Approuver
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.maestro.users.reject', $userItem->id) }}" class="block">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm text-red-700 hover:bg-red-50 rounded-xl transition-colors font-bold" onclick="return confirm('Rejeter cet utilisateur ?')">
                                                <i class="fas fa-times mr-3 text-red-500 opacity-70"></i> Rejeter
                                            </button>
                                        </form>
                                    @endif
                                    
                                    @if($userItem->id !== Auth::id())
                                        @if($userItem->role !== 'maestro')
                                            <form method="POST" action="{{ route('admin.maestro.users.make-maestro', $userItem->id) }}" class="block">
                                                @csrf
                                                <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm text-blue-700 hover:bg-blue-50 rounded-xl transition-colors font-bold">
                                                    <i class="fas fa-star mr-3 text-blue-500 opacity-70"></i> Rendre Maestro
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.maestro.users.make-user', $userItem->id) }}" class="block">
                                                @csrf
                                                <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm text-amber-700 hover:bg-amber-50 rounded-xl transition-colors font-bold">
                                                    <i class="fas fa-user-minus mr-3 text-amber-500 opacity-70"></i> Retirer Maestro
                                                </button>
                                            </form>
                                        @endif

                                        <div class="h-px bg-gray-100 my-1"></div>
                                        <form method="POST" action="{{ route('admin.maestro.users.delete', $userItem->id) }}" class="block" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                            @csrf
                                            <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-xl transition-colors font-bold">
                                                <i class="fas fa-trash-alt mr-3 opacity-70"></i> Supprimer
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-8 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-3xl flex items-center justify-center mb-4">
                                <i class="fas fa-users text-3xl text-gray-300"></i>
                            </div>
                            <p class="text-gray-900 font-bold">Aucun membre</p>
                            <p class="text-gray-500 text-sm mt-1">Les membres de votre chorale apparaîtront ici.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
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

