<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\RubriqueSection;
use App\Models\Partition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MesseController extends Controller
{
    /**
     * Afficher la liste des messes (basé sur les sections de la rubrique "Messes")
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $choraleId = $user?->chorale_id;
        
        // Récupérer la rubrique "Messes" pour la chorale de l'utilisateur
        $messesRubrique = Category::where('name', 'Messes')
            ->when($choraleId, function($query) use ($choraleId) {
                $query->where('chorale_id', $choraleId);
            })
            ->first();
        
        if (!$messesRubrique) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        // Récupérer les messes (sections de la rubrique "Messes")
        $messes = RubriqueSection::where('category_id', $messesRubrique->id)
            ->with(['partitions.pupitre'])
            ->orderBy('nom')
            ->get()
            ->map(function($section) {
                return [
                    'id' => $section->id,
                    'nom' => $section->nom,
                    'description' => $section->description,
                    'couleur' => $section->category->color ?? null,
                    'icone' => $section->category->icon ?? null,
                    'active' => true,
                    'structure' => $section->structure ?? [],
                    'references' => $this->convertStructureToReferences($section->structure ?? [], $section->partitions ?? []),
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $messes
        ]);
    }
    
    /**
     * Convertir la structure JSON en format "references" pour compatibilité
     */
    private function convertStructureToReferences($structure, $partitions)
    {
        if (empty($structure) || !is_array($structure)) {
            return [];
        }
        
        $references = [];
        foreach ($structure as $index => $part) {
            // Utiliser 'nom' en priorité, puis 'name', sinon utiliser le nom de la messe ou un nom par défaut
            $partName = $part['nom'] ?? $part['name'] ?? "Partie " . ($index + 1);
            $partKey = $part['key'] ?? strtolower(str_replace(' ', '_', $partName));
            
            // Filtrer les partitions pour cette partie
            $partPartitions = collect($partitions)->filter(function($partition) use ($partKey) {
                $messePart = $partition->messe_part ?? [];
                return ($messePart['part'] ?? null) === $partKey;
            })->map(function($partition) {
                // Formater chaque partition avec ses fichiers
                $data = $partition->toArray();
                
                // Ajouter les fichiers avec métadonnées si disponibles
                try {
                    $data['files_with_metadata'] = $partition->files_with_metadata;
                } catch (\Exception $e) {
                    // Si l'accessor n'existe pas, utiliser un tableau vide
                    $data['files_with_metadata'] = [];
                }
                
                // S'assurer que le champ 'files' est présent même s'il est vide
                if (!isset($data['files']) || $data['files'] === null) {
                    $data['files'] = [];
                }
                
                // Ajouter les informations du pupitre si disponible
                if ($partition->pupitre) {
                    $data['pupitre'] = [
                        'id' => $partition->pupitre->id,
                        'nom' => $partition->pupitre->nom,
                        'color' => $partition->pupitre->color,
                        'icon' => $partition->pupitre->icon,
                    ];
                }
                
                return $data;
            })->values()->toArray();
            
            $reference = [
                'id' => $index + 1,
                'name' => $partName,
                'description' => $part['description'] ?? null,
                'order_position' => $index,
                'messe_id' => null, // Sera rempli par l'app mobile si nécessaire
                'partitions' => $partPartitions,
                'sub_parts' => $part['subParts'] ?? [],
            ];
            
            $references[] = $reference;
        }
        
        return $references;
    }
    
    /**
     * Créer une nouvelle messe (créer une section dans la rubrique "Messes")
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $choraleId = $user?->chorale_id;
        
        if (!$choraleId) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être associé à une chorale'
            ], 403);
        }
        
        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'structure' => 'nullable|array',
        ]);
        
        // Récupérer ou créer la rubrique "Messes"
        $messesRubrique = Category::firstOrCreate(
            ['name' => 'Messes', 'chorale_id' => $choraleId],
            [
                'description' => 'Rubrique des messes',
                'structure_type' => 'simple',
                'icon' => 'music',
                'color' => '#3B82F6',
            ]
        );
        
        // Créer la section (messe)
        $section = RubriqueSection::create([
            'category_id' => $messesRubrique->id,
            'nom' => $request->nom,
            'description' => $request->description,
            'structure' => $request->structure ?? [],
            'type' => 'section',
            'order' => RubriqueSection::where('category_id', $messesRubrique->id)->max('order') + 1 ?? 0,
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $section->id,
                'nom' => $section->nom,
                'description' => $section->description,
                'structure' => $section->structure,
                'references' => $this->convertStructureToReferences($section->structure ?? [], []),
            ]
        ], 201);
    }
    
    /**
     * Afficher une messe spécifique avec ses sections et partitions
     */
    public function show($id): JsonResponse
    {
        $section = RubriqueSection::with(['partitions.pupitre', 'category'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $section->id,
                'nom' => $section->nom,
                'description' => $section->description,
                'couleur' => $section->category->color ?? null,
                'icone' => $section->category->icon ?? null,
                'active' => true,
                'structure' => $section->structure ?? [],
                'references' => $this->convertStructureToReferences($section->structure ?? [], $section->partitions ?? []),
                'created_at' => $section->created_at,
                'updated_at' => $section->updated_at,
            ]
        ]);
    }
    
    /**
     * Mettre à jour une messe
     */
    public function update(Request $request, $id): JsonResponse
    {
        $section = RubriqueSection::findOrFail($id);
        
        $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'structure' => 'nullable|array',
        ]);
        
        $section->update($request->only(['nom', 'description', 'structure']));
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $section->id,
                'nom' => $section->nom,
                'description' => $section->description,
                'structure' => $section->structure,
                'references' => $this->convertStructureToReferences($section->structure ?? [], $section->partitions ?? []),
            ]
        ]);
    }
    
    /**
     * Supprimer une messe
     */
    public function destroy($id): JsonResponse
    {
        $section = RubriqueSection::findOrFail($id);
        $section->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Messe supprimée avec succès'
        ]);
    }
    
    /**
     * Obtenir les sections d'une messe (compatibilité avec l'ancien système)
     */
    public function sections($id): JsonResponse
    {
        try {
            $section = RubriqueSection::with(['partitions.pupitre'])
                ->findOrFail($id);
            
            $references = $this->convertStructureToReferences($section->structure ?? [], $section->partitions ?? []);
            
            return response()->json([
                'success' => true,
                'data' => $references
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans sections(): ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des sections: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtenir les partitions d'une référence (compatibilité avec l'ancien système)
     * Note: Dans le nouveau système, on utilise la structure JSON pour identifier les parties
     */
    public function partitions($referenceId): JsonResponse
    {
        try {
            Log::info("Partitions request for referenceId: $referenceId");
            
            // Cette méthode est pour compatibilité avec l'ancien système
            // Dans le nouveau système, les partitions sont liées directement aux sections via messe_part
            $partitions = Partition::where('rubrique_section_id', $referenceId)
                ->with(['pupitre'])
                ->get();
            
            Log::info("Found " . $partitions->count() . " partitions for referenceId: $referenceId");
            
            // Formater les partitions avec les fichiers et métadonnées
            $formattedPartitions = $partitions->map(function ($partition) {
                try {
                    $data = $partition->toArray();
                    
                    // Ajouter les fichiers avec métadonnées si disponibles
                    try {
                        $data['files_with_metadata'] = $partition->files_with_metadata;
                    } catch (\Exception $e) {
                        Log::warning('Erreur lors de la récupération de files_with_metadata pour partition ' . $partition->id . ': ' . $e->getMessage());
                        // Si l'accessor n'existe pas, utiliser un tableau vide
                        $data['files_with_metadata'] = [];
                    }
                    
                    // S'assurer que le champ 'files' est présent même s'il est vide
                    if (!isset($data['files']) || $data['files'] === null) {
                        $data['files'] = [];
                    }
                    
                    // Ajouter les informations du pupitre si disponible
                    try {
                        if ($partition->pupitre) {
                            $data['pupitre'] = [
                                'id' => $partition->pupitre->id,
                                'nom' => $partition->pupitre->nom,
                                'color' => $partition->pupitre->color,
                                'icon' => $partition->pupitre->icon,
                            ];
                        } else {
                            $data['pupitre'] = null;
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erreur lors de la récupération du pupitre pour partition ' . $partition->id . ': ' . $e->getMessage());
                        $data['pupitre'] = null;
                    }
                    
                    return $data;
                } catch (\Exception $e) {
                    Log::error('Erreur lors du formatage de la partition ' . ($partition->id ?? 'unknown') . ': ' . $e->getMessage());
                    Log::error($e->getTraceAsString());
                    // Retourner les données de base même en cas d'erreur
                    try {
                        return $partition->toArray();
                    } catch (\Exception $e2) {
                        Log::error('Impossible de convertir la partition en tableau: ' . $e2->getMessage());
                        return ['id' => $partition->id ?? null, 'error' => 'Erreur de formatage'];
                    }
                }
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedPartitions
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans partitions(): ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des partitions: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Supprimer toutes les messes (pour l'importation)
     */
    public function clearAll(): JsonResponse
    {
        $user = Auth::user();
        $choraleId = $user?->chorale_id;
        
        if (!$choraleId) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être associé à une chorale'
            ], 403);
        }
        
        // Récupérer la rubrique "Messes"
        $messesRubrique = Category::where('name', 'Messes')
            ->where('chorale_id', $choraleId)
            ->first();
        
        if ($messesRubrique) {
            // Supprimer toutes les partitions liées
            Partition::whereIn('rubrique_section_id', function($query) use ($messesRubrique) {
                $query->select('id')
                    ->from('rubrique_sections')
                    ->where('category_id', $messesRubrique->id);
            })->delete();
            
            // Supprimer toutes les sections (messes)
            RubriqueSection::where('category_id', $messesRubrique->id)->delete();
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Toutes les messes ont été supprimées'
        ]);
    }
}
