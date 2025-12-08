<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vocalise;
use App\Models\Chorale;
use Illuminate\Support\Facades\Storage;

class VocaliseController extends Controller
{
    /**
     * Récupérer toutes les vocalises
     */
    public function index(Request $request)
    {
        $query = Vocalise::with(['chorale', 'pupitre']);

        // Filtrer par chorale si spécifié
        if ($request->has('chorale_id')) {
            $query->where('chorale_id', $request->chorale_id);
        }

        // Filtrer par pupitre si spécifié
        if ($request->has('pupitre_id')) {
            $query->where('pupitre_id', $request->pupitre_id);
        }

        $vocalises = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $vocalises
        ]);
    }

    /**
     * Récupérer une vocalise spécifique
     */
    public function show($id)
    {
        $vocalise = Vocalise::with(['chorale', 'pupitre'])->find($id);

        if (!$vocalise) {
            return response()->json([
                'success' => false,
                'message' => 'Vocalise non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vocalise
        ]);
    }

    /**
     * Créer une nouvelle vocalise
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'required|exists:chorale_pupitres,id',
            'chorale_id' => 'required|exists:chorales,id',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
        ]);

        // Vérifier que le pupitre appartient à la chorale
        $pupitre = \App\Models\ChoralePupitre::where('id', $request->pupitre_id)
            ->where('chorale_id', $request->chorale_id)
            ->firstOrFail();

        $data = $request->except('audio_file');
        
        if ($request->hasFile('audio_file')) {
            $path = $request->file('audio_file')->store('vocalises', 'public');
            $data['audio_path'] = $path;
        }

        $vocalise = Vocalise::create($data);

        return response()->json([
            'success' => true,
            'data' => $vocalise->load(['chorale', 'pupitre']),
            'message' => 'Vocalise créée avec succès'
        ], 201);
    }

    /**
     * Mettre à jour une vocalise
     */
    public function update(Request $request, $id)
    {
        $vocalise = Vocalise::find($id);

        if (!$vocalise) {
            return response()->json([
                'success' => false,
                'message' => 'Vocalise non trouvée'
            ], 404);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'required|exists:chorale_pupitres,id',
            'chorale_id' => 'required|exists:chorales,id',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
        ]);

        // Vérifier que le pupitre appartient à la chorale
        $pupitre = \App\Models\ChoralePupitre::where('id', $request->pupitre_id)
            ->where('chorale_id', $request->chorale_id)
            ->firstOrFail();

        $data = $request->except('audio_file');
        
        if ($request->hasFile('audio_file')) {
            // Supprimer l'ancien fichier s'il existe
            if ($vocalise->audio_path) {
                Storage::disk('public')->delete($vocalise->audio_path);
            }
            
            $path = $request->file('audio_file')->store('vocalises', 'public');
            $data['audio_path'] = $path;
        }

        $vocalise->update($data);

        return response()->json([
            'success' => true,
            'data' => $vocalise->load(['chorale', 'pupitre']),
            'message' => 'Vocalise mise à jour avec succès'
        ]);
    }

    /**
     * Supprimer une vocalise
     */
    public function destroy($id)
    {
        $vocalise = Vocalise::find($id);

        if (!$vocalise) {
            return response()->json([
                'success' => false,
                'message' => 'Vocalise non trouvée'
            ], 404);
        }

        // Supprimer le fichier audio s'il existe
        if ($vocalise->audio_path) {
            Storage::disk('public')->delete($vocalise->audio_path);
        }
        
        $vocalise->delete();

        return response()->json([
            'success' => true,
            'message' => 'Vocalise supprimée avec succès'
        ]);
    }

    /**
     * Récupérer les vocalises pour synchronisation (avec timestamps)
     */
    public function getForSync(Request $request)
    {
        $lastSync = $request->get('last_sync', '1970-01-01 00:00:00');
        
        $vocalises = Vocalise::with(['chorale', 'pupitre'])
            ->where('updated_at', '>', $lastSync)
            ->orderBy('updated_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vocalises,
            'last_sync' => now()->toDateTimeString()
        ]);
    }

    /**
     * Télécharger le fichier audio d'une vocalise
     */
    public function downloadAudio($id)
    {
        $vocalise = Vocalise::find($id);

        if (!$vocalise || !$vocalise->audio_path) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier audio non trouvé'
            ], 404);
        }

        $filePath = storage_path('app/public/' . $vocalise->audio_path);
        
        if (!file_exists($filePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier audio non trouvé sur le serveur'
            ], 404);
        }

        return response()->download($filePath, $vocalise->title . '.mp3');
    }
}
