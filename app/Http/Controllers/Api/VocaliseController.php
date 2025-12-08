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
use Illuminate\Support\Facades\Storage;

class VocaliseController extends Controller
{
    /**
     * Afficher la liste des vocalises organisées par sections
     * Les vocalises sont des partitions dans la catégorie "Vocalises"
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $choraleId = $user?->chorale_id;
            
            if (!$choraleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être associé à une chorale',
                    'data' => []
                ], 403);
            }
            
            // Récupérer ou créer la catégorie "Vocalises"
            $vocalisesRubrique = Category::firstOrCreate(
                ['name' => 'Vocalises', 'chorale_id' => $choraleId],
                [
                    'description' => 'Rubrique des vocalises',
                    'structure_type' => 'with_sections',
                    'icon' => 'mic',
                    'color' => '#9C27B0',
                ]
            );
            
            // Récupérer les sections/dossiers de la rubrique "Vocalises"
            $sections = RubriqueSection::where('category_id', $vocalisesRubrique->id)
                ->whereNull('dossier_id')
                ->with(['partitions.pupitre', 'sections.partitions.pupitre'])
                ->ordered()
                ->get()
                ->map(function($section) {
                    // Convertir les partitions en vocalises
                    $vocalises = $this->convertPartitionsToVocalises($section->partitions ?? []);
                    
                    return [
                        'id' => $section->id,
                        'nom' => $section->nom,
                        'description' => $section->description,
                        'type' => $section->type,
                        'structure' => $section->structure ?? [],
                        'couleur' => $section->category->color ?? null,
                        'icone' => $section->category->icon ?? null,
                        'vocalises' => $this->convertStructureToVocalises($section->structure ?? [], $vocalises),
                        'sections' => $section->sections->map(function($subSection) {
                            $subVocalises = $this->convertPartitionsToVocalises($subSection->partitions ?? []);
                            return [
                                'id' => $subSection->id,
                                'nom' => $subSection->nom,
                                'description' => $subSection->description,
                                'structure' => $subSection->structure ?? [],
                                'vocalises' => $this->convertStructureToVocalises($subSection->structure ?? [], $subVocalises),
                            ];
                        }),
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $sections
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans VocaliseController::index: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des vocalises: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    
    /**
     * Convertir les partitions en format vocalise
     */
    private function convertPartitionsToVocalises($partitions)
    {
        return collect($partitions)->map(function($partition) {
            return $this->partitionToVocalise($partition);
        })->values();
    }
    
    /**
     * Convertir une partition en format vocalise
     */
    private function partitionToVocalise($partition)
    {
        $files = $partition->files ?? [];
        $filesWithMetadata = [];
        
        try {
            $filesWithMetadata = $partition->files_with_metadata ?? [];
        } catch (\Exception $e) {
            Log::warning('Erreur lors de la récupération de files_with_metadata: ' . $e->getMessage());
        }
        
        $allFiles = !empty($filesWithMetadata) ? $filesWithMetadata : $files;
        
        // Extraire les fichiers audio par pupitre
        $audioFiles = $this->getFilesByTypeFromMetadata($allFiles, 'audio');
        $sopranoFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'soprano');
        $altoFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'alto');
        $tenorFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'tenor');
        $basseFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'basse');
        $tuttiFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'tutti');
        
        // Si pas de fichiers dans les métadonnées, utiliser les anciens champs
        if (empty($audioFiles) && $partition->audio_path) {
            $audioFiles = [$partition->audio_path];
        }
        
        return [
            'id' => $partition->id,
            'section_id' => $partition->rubrique_section_id ?? 0,
            'titre' => $partition->title,
            'description' => $partition->description,
            'audio_path' => $partition->audio_path,
            'audio_url' => $partition->audio_path ? asset('storage/' . $partition->audio_path) : null,
            'files' => $allFiles, // Ajouter le champ files avec métadonnées
            'files_with_metadata' => $filesWithMetadata, // Ajouter aussi files_with_metadata
            'audio_files' => !empty($audioFiles) ? $audioFiles : null,
            'soprano_files' => !empty($sopranoFiles) ? $sopranoFiles : null,
            'alto_files' => !empty($altoFiles) ? $altoFiles : null,
            'tenor_files' => !empty($tenorFiles) ? $tenorFiles : null,
            'basse_files' => !empty($basseFiles) ? $basseFiles : null,
            'tutti_files' => !empty($tuttiFiles) ? $tuttiFiles : null,
            'pupitre' => $partition->pupitre ? [
                'id' => $partition->pupitre->id,
                'nom' => $partition->pupitre->nom,
                'color' => $partition->pupitre->color,
                'icon' => $partition->pupitre->icon,
            ] : null,
            'voice_part' => $partition->pupitre?->nom ?? 'Tous',
            'vocalise_part' => $partition->vocalise_part ?? null,
            'chorale_id' => $partition->chorale_id,
            'created_at' => $partition->created_at ? $partition->created_at->toISOString() : now()->toISOString(),
            'updated_at' => $partition->updated_at ? $partition->updated_at->toISOString() : now()->toISOString(),
        ];
    }
    
    /**
     * Obtenir les fichiers par type depuis les métadonnées
     */
    private function getFilesByTypeFromMetadata($files, $type)
    {
        if (empty($files) || !is_array($files)) {
            return [];
        }
        
        $result = [];
        foreach ($files as $file) {
            if (is_array($file)) {
                $fileType = $file['type'] ?? null;
                $filePath = $file['path'] ?? $file['url'] ?? '';
                
                if ($fileType === $type || $this->isFileType($filePath, $type)) {
                    $path = $file['path'] ?? $filePath;
                    if (!empty($path)) {
                        $result[] = $path;
                    }
                }
            } elseif (is_string($file) && $this->isFileType($file, $type)) {
                $result[] = $file;
            }
        }
        
        return array_unique($result);
    }
    
    /**
     * Obtenir les fichiers par pupitre depuis les métadonnées
     */
    private function getFilesByPupitreFromMetadata($files, $pupitre)
    {
        if (empty($files) || !is_array($files)) {
            return [];
        }
        
        $result = [];
        $pupitreLower = strtolower($pupitre);
        
        $pupitreVariations = [
            'soprano' => ['soprano', 'soprane', 'sop'],
            'alto' => ['alto', 'mezzo'],
            'tenor' => ['tenor', 'ténor', 'ten'],
            'basse' => ['basse', 'basses', 'bariton', 'bass'],
            'tutti' => ['tutti', 'tutties', 'all'],
        ];
        
        $variations = $pupitreVariations[$pupitreLower] ?? [$pupitreLower];
        
        foreach ($files as $file) {
            if (is_array($file)) {
                $filePupitre = strtolower($file['pupitre'] ?? '');
                $filePath = $file['path'] ?? $file['url'] ?? '';
                
                $matches = false;
                foreach ($variations as $variation) {
                    if ($filePupitre === $variation || strpos($filePath, $variation) !== false) {
                        $matches = true;
                        break;
                    }
                }
                
                if ($matches) {
                    $path = $file['path'] ?? $filePath;
                    if (!empty($path)) {
                        $result[] = $path;
                    }
                }
            }
        }
        
        return array_unique($result);
    }
    
    /**
     * Vérifier si un fichier est d'un type donné
     */
    private function isFileType($path, $type)
    {
        if (!is_string($path)) {
            return false;
        }
        
        $pathLower = strtolower($path);
        
        switch ($type) {
            case 'audio':
                return preg_match('/\.(mp3|wav|m4a|aac|ogg|opus|flac|mp4)$/i', $pathLower);
            case 'pdf':
                return strpos($pathLower, '.pdf') !== false;
            case 'image':
                return preg_match('/\.(jpg|jpeg|png|gif|bmp|webp)$/i', $pathLower);
            default:
                return false;
        }
    }
    
    /**
     * Convertir la structure JSON en vocalises organisées par partie
     * $vocalises est maintenant une collection de partitions converties
     */
    private function convertStructureToVocalises($structure, $vocalises)
    {
        if (empty($structure) || !is_array($structure)) {
            // Si pas de structure, retourner toutes les vocalises (déjà converties)
            return is_array($vocalises) ? $vocalises : collect($vocalises)->values()->toArray();
        }
        
        $result = [];
        foreach ($structure as $index => $part) {
            $partName = $part['nom'] ?? $part['name'] ?? "Partie " . ($index + 1);
            $partKey = $part['key'] ?? strtolower(str_replace(' ', '_', $partName));
            
            // Filtrer les vocalises pour cette partie
            $partVocalises = collect($vocalises)->filter(function($vocalise) use ($partKey) {
                $vocalisePart = is_array($vocalise) ? ($vocalise['vocalise_part'] ?? []) : ($vocalise->vocalise_part ?? []);
                if (is_string($vocalisePart)) {
                    $vocalisePart = json_decode($vocalisePart, true) ?? [];
                }
                return ($vocalisePart['part'] ?? null) === $partKey;
            })->values()->toArray();
            
            $result[] = [
                'id' => $index + 1,
                'name' => $partName,
                'description' => $part['description'] ?? null,
                'order_position' => $index,
                'vocalises' => $partVocalises,
                'sub_parts' => $part['subParts'] ?? [],
            ];
        }
        
        return $result;
    }
    
    
    /**
     * Créer une nouvelle section/dossier dans la rubrique "Vocalises"
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
            'type' => 'required|in:dossier,section',
            'dossier_id' => 'nullable|exists:rubrique_sections,id',
            'structure' => 'nullable|array',
        ]);
        
        // Récupérer ou créer la rubrique "Vocalises"
        $vocalisesRubrique = Category::firstOrCreate(
            ['name' => 'Vocalises', 'chorale_id' => $choraleId],
            [
                'description' => 'Rubrique des vocalises',
                'structure_type' => 'with_dossiers',
                'icon' => 'music_note',
                'color' => '#2196F3',
            ]
        );
        
        // Vérifier l'unicité du nom
        $uniqueQuery = RubriqueSection::where('category_id', $vocalisesRubrique->id)
            ->where('nom', $request->nom);
        
        if ($request->dossier_id) {
            $uniqueQuery->where('dossier_id', $request->dossier_id);
        } else {
            $uniqueQuery->whereNull('dossier_id');
        }
        
        if ($uniqueQuery->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Un ' . $request->type . ' avec ce nom existe déjà.'
            ], 422);
        }
        
        // Créer la section/dossier
        $section = RubriqueSection::create([
            'category_id' => $vocalisesRubrique->id,
            'dossier_id' => $request->dossier_id,
            'nom' => $request->nom,
            'description' => $request->description,
            'type' => $request->type,
            'structure' => $request->structure ?? [],
            'order' => RubriqueSection::where('category_id', $vocalisesRubrique->id)
                ->where('dossier_id', $request->dossier_id ?? null)
                ->max('order') ?? 0,
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $section->id,
                'nom' => $section->nom,
                'description' => $section->description,
                'type' => $section->type,
                'structure' => $section->structure,
                'vocalises' => $this->convertStructureToVocalises($section->structure ?? [], []),
            ]
        ], 201);
    }
    
    /**
     * Afficher une section spécifique avec ses vocalises
     */
    public function show($id): JsonResponse
    {
        $section = RubriqueSection::with(['partitions.pupitre', 'sections.partitions.pupitre', 'category'])
            ->findOrFail($id);
        
        $vocalises = $this->convertPartitionsToVocalises($section->partitions ?? []);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $section->id,
                'nom' => $section->nom,
                'description' => $section->description,
                'type' => $section->type,
                'couleur' => $section->category->color ?? null,
                'icone' => $section->category->icon ?? null,
                'structure' => $section->structure ?? [],
                'vocalises' => $this->convertStructureToVocalises($section->structure ?? [], $vocalises),
                'sections' => $section->sections->map(function($subSection) {
                    $subVocalises = $this->convertPartitionsToVocalises($subSection->partitions ?? []);
                    return [
                        'id' => $subSection->id,
                        'nom' => $subSection->nom,
                        'description' => $subSection->description,
                        'structure' => $subSection->structure ?? [],
                        'vocalises' => $this->convertStructureToVocalises($subSection->structure ?? [], $subVocalises),
                    ];
                }),
                'created_at' => $section->created_at,
                'updated_at' => $section->updated_at,
            ]
        ]);
    }
    
    /**
     * Mettre à jour une section
     */
    public function update(Request $request, $id): JsonResponse
    {
        $section = RubriqueSection::findOrFail($id);
        
        $request->validate([
            'nom' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:dossier,section',
            'structure' => 'nullable|array',
        ]);
        
        $section->update($request->only(['nom', 'description', 'type', 'structure']));
        
        $section->load('partitions.pupitre');
        $vocalises = $this->convertPartitionsToVocalises($section->partitions ?? []);
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $section->id,
                'nom' => $section->nom,
                'description' => $section->description,
                'type' => $section->type,
                'structure' => $section->structure,
                'vocalises' => $this->convertStructureToVocalises($section->structure ?? [], $vocalises),
            ]
        ]);
    }
    
    /**
     * Supprimer une section
     */
    public function destroy($id): JsonResponse
    {
        $section = RubriqueSection::findOrFail($id);
        
        // Supprimer les partitions associées (vocalises)
        $section->partitions()->delete();
        
        $section->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Section supprimée avec succès'
        ]);
    }
    
    /**
     * Obtenir les vocalises d'une section (organisées par parties)
     */
    public function vocalises($id): JsonResponse
    {
        try {
            $section = RubriqueSection::with('partitions.pupitre')
                ->findOrFail($id);
            
            $vocalises = $this->convertPartitionsToVocalises($section->partitions ?? []);
            $vocalises = $this->convertStructureToVocalises($section->structure ?? [], $vocalises);
            
            return response()->json([
                'success' => true,
                'data' => $vocalises
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans vocalises(): ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des vocalises: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Créer une vocalise dans une section
     */
    public function storeVocalise(Request $request, $sectionId): JsonResponse
    {
        $user = Auth::user();
        $choraleId = $user?->chorale_id;
        
        if (!$choraleId) {
            return response()->json([
                'success' => false,
                'message' => 'Vous devez être associé à une chorale'
            ], 403);
        }
        
        $section = RubriqueSection::findOrFail($sectionId);
        
        // Vérifier si la section a une structure (parties)
        $hasStructure = $section->structure && count($section->structure) > 0;
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'required|exists:chorale_pupitres,id',
            'part' => $hasStructure ? 'required|string' : 'nullable|string',
            'subPart' => 'nullable|string',
            'files.*' => 'nullable|file|max:20480', // 20MB max pour tous les types
        ]);
        
        // Vérifier que le pupitre appartient à la chorale
        $pupitre = \App\Models\ChoralePupitre::where('id', $request->pupitre_id)
            ->where('chorale_id', $choraleId)
            ->firstOrFail();
        
        // Créer une partition (vocalise) dans la catégorie "Vocalises"
        $vocalisesRubrique = Category::where('name', 'Vocalises')
            ->where('chorale_id', $choraleId)
            ->firstOrFail();
        
        // Préparer les données de la partition (sans les fichiers)
        $data = $request->except(['files']);
        $data['category_id'] = $vocalisesRubrique->id;
        $data['rubrique_section_id'] = $section->id;
        $data['chorale_id'] = $choraleId;
        
        // Récupérer le nom du pupitre pour le nommage des fichiers
        $pupitreNom = $pupitre->nom;
        $sectionNom = $section->nom;
        
        // Traiter tous les fichiers de manière unifiée avec nommage personnalisé
        if ($request->hasFile('files')) {
            $filePaths = [];
            foreach ($request->file('files') as $file) {
                // Générer le nom de fichier personnalisé
                $customFileName = \App\Helpers\FileHelper::generatePartitionFileName(
                    $file,
                    null,
                    $sectionNom,
                    $request->part ?? null,
                    $request->subPart ?? null,
                    $pupitreNom
                );
                
                // Déterminer le chemin de stockage selon le type de fichier
                $storagePath = \App\Helpers\FileHelper::getStoragePath($file->getClientOriginalName());
                
                // Stocker le fichier avec le nom personnalisé
                $path = $file->storeAs($storagePath, $customFileName, 'public');
                $filePaths[] = $path;
            }
            $data['files'] = $filePaths;
        }
        
        // Ajouter vocalise_part au format JSON si part est fourni
        if ($request->has('part') && !empty($request->part)) {
            $data['vocalise_part'] = [
                'part' => $request->part,
                'subPart' => $request->subPart ?? null,
            ];
        }
        
        $partition = Partition::create($data);
        $partition->load('pupitre');
        
        return response()->json([
            'success' => true,
            'message' => 'Vocalise créée avec succès',
            'data' => $this->partitionToVocalise($partition)
        ], 201);
    }
    
    /**
     * Mettre à jour une vocalise (partition)
     */
    public function updateVocalise(Request $request, $sectionId, $vocaliseId): JsonResponse
    {
        $partition = Partition::where('rubrique_section_id', $sectionId)
            ->where('id', $vocaliseId)
            ->firstOrFail();
        
        $section = RubriqueSection::findOrFail($sectionId);
        $hasStructure = $section->structure && count($section->structure) > 0;
        
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'sometimes|required|exists:chorale_pupitres,id',
            'part' => 'nullable|string',
            'subPart' => 'nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
        ]);
        
        // Vérifier que le pupitre appartient à la chorale si fourni
        if ($request->has('pupitre_id')) {
            $user = Auth::user();
            $choraleId = $user?->chorale_id;
            if ($choraleId) {
                \App\Models\ChoralePupitre::where('id', $request->pupitre_id)
                    ->where('chorale_id', $choraleId)
                    ->firstOrFail();
            }
        }
        
        $data = $request->only(['title', 'description', 'pupitre_id']);
        
        // Gérer le fichier audio
        if ($request->hasFile('audio_file')) {
            // Supprimer l'ancien fichier s'il existe
            if ($partition->audio_path) {
                Storage::disk('public')->delete($partition->audio_path);
            }
            
            $data['audio_path'] = $request->file('audio_file')->store('vocalises', 'public');
        }
        
        // Mettre à jour vocalise_part si part est fourni
        if ($request->has('part')) {
            if (!empty($request->part)) {
                $data['vocalise_part'] = [
                    'part' => $request->part,
                    'subPart' => $request->subPart ?? null,
                ];
            } else {
                $data['vocalise_part'] = null;
            }
        }
        
        $partition->update($data);
        $partition->load('pupitre');
        
        return response()->json([
            'success' => true,
            'message' => 'Vocalise mise à jour avec succès',
            'data' => $this->partitionToVocalise($partition->fresh())
        ]);
    }
    
    /**
     * Supprimer une vocalise (partition)
     */
    public function destroyVocalise($sectionId, $vocaliseId): JsonResponse
    {
        $partition = Partition::where('rubrique_section_id', $sectionId)
            ->where('id', $vocaliseId)
            ->firstOrFail();
        
        // Supprimer le fichier audio s'il existe
        if ($partition->audio_path) {
            Storage::disk('public')->delete($partition->audio_path);
        }
        
        $partition->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Vocalise supprimée avec succès'
        ]);
    }
}

