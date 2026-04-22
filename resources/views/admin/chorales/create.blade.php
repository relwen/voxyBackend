@extends('layouts.admin')

@section('title', 'Créer une Chorale - VoXY Admin')
@section('page-title', 'Nouvelle Chorale')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Créer une nouvelle chorale</h1>
            <p class="text-gray-500 mt-1">Ajoutez une nouvelle entité musicale au système.</p>
        </div>
        <a href="{{ route('admin.chorales') }}" class="inline-flex items-center text-sm font-bold text-gray-500 hover:text-primary transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Retour à la liste
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('admin.chorales.store') }}" method="POST" class="p-8 md:p-10 space-y-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Nom de la chorale <span class="text-primary">*</span></label>
                        <div class="relative group">
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-4 text-gray-900 placeholder-gray-400 outline-none transition-all focus:ring-4 focus:ring-primary/10 focus:border-primary"
                                   placeholder="Ex: Chœur des Anges">
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-primary transition-colors">
                                <i class="fas fa-music"></i>
                            </div>
                        </div>
                        @error('name')
                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-bold text-gray-700 mb-2">Localisation</label>
                        <div class="relative group">
                            <input type="text" name="location" id="location" value="{{ old('location') }}"
                                   class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-4 text-gray-900 placeholder-gray-400 outline-none transition-all focus:ring-4 focus:ring-primary/10 focus:border-primary"
                                   placeholder="Ex: Paris, France">
                            <div class="absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-primary transition-colors">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </div>
                        @error('location')
                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Description / Slogan</label>
                        <textarea name="description" id="description" rows="5"
                                  class="w-full bg-gray-50 border border-gray-200 rounded-2xl px-5 py-4 text-gray-900 placeholder-gray-400 outline-none transition-all focus:ring-4 focus:ring-primary/10 focus:border-primary resize-none"
                                  placeholder="Brève description de la chorale...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-50 flex items-center justify-end gap-4">
                <a href="{{ route('admin.chorales') }}" class="px-6 py-3 rounded-xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all">
                    Annuler
                </a>
                <button type="submit" class="bg-primary-gradient text-white px-8 py-3 rounded-xl text-sm font-black shadow-lg shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                    <i class="fas fa-check"></i> Créer la chorale
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
 