<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Messe;
use App\Models\Reference;
use App\Models\Partition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class MesseController extends Controller
{
    /**
     * Afficher la liste des messes
     */
    public function index(): JsonResponse
    {
        $messes = Messe::ordered()
            ->with(['references' => function ($query) {
                $query->ordered();
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messes
        ]);
    }

    /**
     * Créer une nouvelle messe
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:7',
            'icone' => 'nullable|string|max:50',
        ]);

        $messe = Messe::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $messe
        ], 201);
    }

    /**
     * Afficher une messe spécifique avec ses sections et chants
     */
    public function show(Messe $messe): JsonResponse
    {
        $messe->load([
            'references' => function ($query) {
                $query->ordered()->with([
                    'partitions'
                ]);
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $messe
        ]);
    }

    /**
     * Mettre à jour une messe
     */
    public function update(Request $request, Messe $messe): JsonResponse
    {
        $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'couleur' => 'nullable|string|max:7',
            'icone' => 'nullable|string|max:50',
            'active' => 'nullable|boolean',
        ]);

        $messe->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $messe
        ]);
    }

    /**
     * Supprimer une messe
     */
    public function destroy(Messe $messe): JsonResponse
    {
        $messe->delete();

        return response()->json([
            'success' => true,
            'message' => 'Messe supprimée avec succès'
        ]);
    }

    /**
     * Obtenir les sections d'une messe
     */
    public function sections(Messe $messe): JsonResponse
    {
        $sections = $messe->references()
            ->ordered()
            ->with(['partitions'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sections
        ]);
    }

    /**
     * Obtenir les partitions d'une référence
     */
    public function partitions(Reference $reference): JsonResponse
    {
        $partitions = $reference->partitions()->get();

        return response()->json([
            'success' => true,
            'data' => $partitions
        ]);
    }


    /**
     * Supprimer toutes les messes (pour l'importation)
     */
    public function clearAll(): JsonResponse
    {
        // Supprimer toutes les partitions
        \App\Models\Partition::truncate();
        
        // Supprimer toutes les références
        \App\Models\Reference::truncate();
        
        // Supprimer toutes les messes
        Messe::truncate();

        return response()->json([
            'success' => true,
            'message' => 'Toutes les messes ont été supprimées'
        ]);
    }
}