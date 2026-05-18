<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Partition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChantController extends Controller
{
    /**
     * Afficher la liste des sections (dossiers) de chants
     * Les chants sont organisés en sections dans la catégorie "Chants"
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
            
            // Récupérer ou créer la catégorie "Chants"
            $chantsRubrique = Category::firstOrCreate(
                ['name' => 'Chants', 'chorale_id' => $choraleId],
                [
                    'description' => 'Rubrique des chants',
                    'structure_type' => 'with_sections',
                    'icon' => 'music_note',
                    'color' => '#4CAF50',
                ]
            );
            
            // Récupérer les sections (dossiers) de la catégorie "Chants"
            $sections = \App\Models\RubriqueSection::where('category_id', $chantsRubrique->id)
                ->whereNull('dossier_id') // Sections de premier niveau
                ->with(['partitions.pupitre', 'partitions.user'])
                ->orderBy('nom')
                ->get()
                ->map(function($section) {
                    // Filtrer les partitions pour exclure celles liées aux messes
                    $messesRubrique = Category::where('name', 'Messes')
                        ->where('chorale_id', $section->category->chorale_id)
                        ->first();
                    
                    $messeSectionIds = [];
                    if ($messesRubrique) {
                        $messeSectionIds = \App\Models\RubriqueSection::where('category_id', $messesRubrique->id)
                            ->pluck('id')
                            ->toArray();
                    }
                    
                    // Filtrer les partitions de cette section
                    $validPartitions = $section->partitions->filter(function($partition) use ($messeSectionIds) {
                        // Exclure les partitions qui ont un messe_part défini
                        $messePart = $partition->messe_part ?? [];
                        if (!empty($messePart) && isset($messePart['part'])) {
                            return false;
                        }
                        
                        // Exclure les partitions dont rubrique_section_id pointe vers une section de messe
                        if ($partition->rubrique_section_id && in_array($partition->rubrique_section_id, $messeSectionIds)) {
                            return false;
                        }
                        
                        return true;
                    });
                    
                    return [
                        'id' => $section->id,
                        'nom' => $section->nom,
                        'description' => $section->description,
                        'couleur' => $section->category->color ?? '#4CAF50',
                        'icone' => $section->category->icon ?? 'music_note',
                        'active' => true,
                        'structure' => $section->structure ?? [],
                        'chants_count' => $validPartitions->count(),
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $sections
            ]);
        } catch (\Exception $e) {
            Log::error('ChantController::index - Erreur: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des sections de chants: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Afficher les détails d'une section de chants et ses partitions
     */
    public function show($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $choraleId = $user?->chorale_id;
            
            if (!$choraleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être associé à une chorale'
                ], 403);
            }
            
            // On vérifie que la section appartient à la catégorie "Chants" de la chorale
            $section = \App\Models\RubriqueSection::with(['category', 'partitions.user'])
                ->where('id', $id)
                ->whereHas('category', function($query) use ($choraleId) {
                    $query->where('name', 'Chants')
                          ->where('chorale_id', $choraleId);
                })
                ->firstOrFail();
            
            // Filtrer les partitions pour exclure celles liées aux messes
            $messesRubrique = Category::where('name', 'Messes')
                ->where('chorale_id', $choraleId)
                ->first();
            
            $messeSectionIds = [];
            if ($messesRubrique) {
                $messeSectionIds = \App\Models\RubriqueSection::where('category_id', $messesRubrique->id)
                    ->pluck('id')
                    ->toArray();
            }
            
            $validPartitions = $section->partitions->filter(function($partition) use ($messeSectionIds) {
                // Exclure les partitions qui ont un messe_part défini
                $messePart = $partition->messe_part ?? [];
                if (!empty($messePart) && isset($messePart['part'])) {
                    return false;
                }
                
                // Exclure les partitions dont rubrique_section_id pointe vers une section de messe
                if ($partition->rubrique_section_id && in_array($partition->rubrique_section_id, $messeSectionIds)) {
                    return false;
                }
                
                return true;
            });
            
            $chants = $validPartitions->map(function($partition) {
                return $this->partitionToChantDeMesse($partition);
            })->values();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $section->id,
                    'nom' => $section->nom,
                    'description' => $section->description,
                    'couleur' => $section->category->color ?? '#4CAF50',
                    'icone' => $section->category->icon ?? 'music_note',
                    'active' => true,
                    'structure' => $section->structure ?? [],
                    'chants_count' => $chants->count(),
                    'created_at' => $section->created_at,
                    'updated_at' => $section->updated_at,
                    'chants' => $chants
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('ChantController::show - Erreur: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section de chants introuvable',
                    'data' => null
                ], 404);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de la section: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }
    
    /**
     * Convertir une partition en format ChantDeMesse
     */
    private function partitionToChantDeMesse($partition)
    {
        try {
            $files = $partition->files ?? [];
            $filesWithMetadata = [];
            
            // Utiliser l'accessor files_with_metadata si disponible
            try {
                $filesWithMetadata = $partition->files_with_metadata ?? [];
            } catch (\Exception $e) {
                Log::warning('Erreur lors de la récupération de files_with_metadata pour partition ' . $partition->id . ': ' . $e->getMessage());
            }
            
            // Utiliser files_with_metadata si disponible, sinon files
            $allFiles = !empty($filesWithMetadata) ? $filesWithMetadata : $files;
            
            // Extraire les fichiers par type et par pupitre
            $audioFiles = $this->getFilesByTypeFromMetadata($allFiles, 'audio');
            $pdfFiles = $this->getFilesByTypeFromMetadata($allFiles, 'pdf');
            $imageFiles = $this->getFilesByTypeFromMetadata($allFiles, 'image');
            
            // Si pas de fichiers dans les métadonnées, utiliser les anciens champs
            if (empty($audioFiles) && $partition->audio_path) {
                $audioFiles = [$partition->audio_path];
            }
            if (empty($pdfFiles) && $partition->pdf_path) {
                $pdfFiles = [$partition->pdf_path];
            }
            if (empty($imageFiles) && $partition->image_path) {
                $imageFiles = [$partition->image_path];
            }
            
            // Extraire les fichiers par pupitre
            $sopranoFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'soprano');
            $altoFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'alto');
            $tenorFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'tenor');
            $basseFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'basse');
            $tuttiFiles = $this->getFilesByPupitreFromMetadata($allFiles, 'tutti');
            
            return [
                'id' => $partition->id,
                'section_id' => 0, // 0 pour indiquer que ce n'est pas lié à une messe
                'titre' => $partition->title,
                'description' => $partition->description,
                'audio_path' => $partition->audio_path,
                'pdf_path' => $partition->pdf_path,
                'image_path' => $partition->image_path,
                'audio_files' => !empty($audioFiles) ? $audioFiles : null,
                'pdf_files' => !empty($pdfFiles) ? $pdfFiles : null,
                'image_files' => !empty($imageFiles) ? $imageFiles : null,
                'soprano_files' => !empty($sopranoFiles) ? $sopranoFiles : null,
                'alto_files' => !empty($altoFiles) ? $altoFiles : null,
                'tenor_files' => !empty($tenorFiles) ? $tenorFiles : null,
                'basse_files' => !empty($basseFiles) ? $basseFiles : null,
                'tutti_files' => !empty($tuttiFiles) ? $tuttiFiles : null,
                'userName' => $partition->user->name ?? null,
                'user_name' => $partition->user->name ?? null,
                'ordre' => $partition->order ?? 0,
                'active' => true,
                'created_at' => $partition->created_at ? $partition->created_at->toISOString() : now()->toISOString(),
                'updated_at' => $partition->updated_at ? $partition->updated_at->toISOString() : now()->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('Erreur lors de la conversion de la partition en ChantDeMesse: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            // Retourner un chant minimal en cas d'erreur
            return [
                'id' => $partition->id ?? 0,
                'section_id' => 0,
                'titre' => $partition->title ?? 'Chant sans titre',
                'description' => $partition->description ?? null,
                'audio_path' => $partition->audio_path ?? null,
                'pdf_path' => $partition->pdf_path ?? null,
                'image_path' => $partition->image_path ?? null,
                'audio_files' => null,
                'pdf_files' => null,
                'image_files' => null,
                'soprano_files' => null,
                'alto_files' => null,
                'tenor_files' => null,
                'basse_files' => null,
                'tutti_files' => null,
                'user_name' => $partition->user->name ?? null,
                'ordre' => 0,
                'active' => true,
                'created_at' => now()->toISOString(),
                'updated_at' => now()->toISOString(),
            ];
        }
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
                
                // Vérifier le type via le champ 'type' ou via l'extension du fichier
                if ($fileType === $type || $this->isFileType($filePath, $type)) {
                    // Utiliser le path (chemin relatif) plutôt que l'URL complète
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
        
        // Mapping des variations de noms de pupitres
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
                
                // Vérifier si le pupitre correspond (directement ou via variations)
                $matches = false;
                foreach ($variations as $variation) {
                    if ($filePupitre === $variation || strpos($filePath, $variation) !== false) {
                        $matches = true;
                        break;
                    }
                }
                
                if ($matches) {
                    // Utiliser le path (chemin relatif) plutôt que l'URL complète
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
     * Créer une nouvelle section de chants
     */
    public function store(Request $request): JsonResponse
    {
        try {
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
            ]);
            
            // Récupérer ou créer la rubrique "Chants"
            $chantsRubrique = Category::firstOrCreate(
                ['name' => 'Chants', 'chorale_id' => $choraleId],
                [
                    'description' => 'Rubrique des chants',
                    'structure_type' => 'with_sections',
                    'icon' => 'music_note',
                    'color' => '#4CAF50',
                ]
            );
            
            // Créer la section
            $section = \App\Models\RubriqueSection::create([
                'category_id' => $chantsRubrique->id,
                'nom' => $request->nom,
                'description' => $request->description,
                'type' => 'section',
                'order' => \App\Models\RubriqueSection::where('category_id', $chantsRubrique->id)->max('order') + 1 ?? 0,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Section de chants créée avec succès',
                'data' => [
                    'id' => $section->id,
                    'nom' => $section->nom,
                    'description' => $section->description,
                    'couleur' => $chantsRubrique->color ?? '#4CAF50',
                    'icone' => $chantsRubrique->icon ?? 'music_note',
                    'chants_count' => 0,
                ]
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur dans ChantController::store: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une section de chants
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();
            $choraleId = $user?->chorale_id;
            
            $section = \App\Models\RubriqueSection::where('id', $id)
                ->whereHas('category', function($query) use ($choraleId) {
                    $query->where('name', 'Chants')
                          ->where('chorale_id', $choraleId);
                })
                ->firstOrFail();
            
            $request->validate([
                'nom' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
            ]);
            
            $section->update($request->only(['nom', 'description']));
            
            return response()->json([
                'success' => true,
                'message' => 'Section de chants mise à jour avec succès',
                'data' => [
                    'id' => $section->id,
                    'nom' => $section->nom,
                    'description' => $section->description,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans ChantController::update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une section de chants
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = Auth::user();
            $choraleId = $user?->chorale_id;
            
            $section = \App\Models\RubriqueSection::where('id', $id)
                ->whereHas('category', function($query) use ($choraleId) {
                    $query->where('name', 'Chants')
                          ->where('chorale_id', $choraleId);
                })
                ->firstOrFail();
            
            // Supprimer les partitions liées
            \App\Models\Partition::where('rubrique_section_id', $id)->delete();
            
            $section->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Section de chants supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans ChantController::destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload d'un fichier pour un chant (section)
     */
    public function uploadFile(Request $request, $chantId): JsonResponse
    {
        try {
            $user = Auth::user();
            $choraleId = $user?->chorale_id;

            if (!$choraleId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous devez être associé à une chorale'
                ], 403);
            }

            // Validation
            $request->validate([
                'pupitre' => 'required|string',
                'file_type' => 'required|string|in:audio,image,pdf',
                'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac|max:20480',
                'image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
                'pdf_file' => 'nullable|file|mimes:pdf|max:20480',
            ]);

            $pupitre = $request->input('pupitre');
            $fileType = $request->input('file_type');

            // Déterminer quel fichier uploader
            $fileField = $fileType . '_file';
            if (!$request->hasFile($fileField)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun fichier fourni'
                ], 400);
            }

            $file = $request->file($fileField);

            // Créer le chemin
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $directory = "chants/{$chantId}/{$pupitre}/{$fileType}";

            // Stocker le fichier
            $path = $file->storeAs($directory, $fileName, 'public');

            if (!$path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'upload du fichier'
                ], 500);
            }

            // Récupérer ou créer la partition pour ce chant et ce pupitre
            // Pour les chants simples, on utilise le rubrique_section_id
            $partition = \App\Models\Partition::firstOrCreate(
                [
                    'rubrique_section_id' => $chantId,
                    'pupitre_id' => $this->getPupitreIdByName($pupitre, $choraleId),
                    'chorale_id' => $choraleId,
                ],
                [
                    'title' => "Partition $pupitre",
                    'description' => "Fichiers pour le pupitre $pupitre",
                    'user_id' => $user->id,
                    'files' => [],
                ]
            );

            // Ajouter le fichier au tableau 'files'
            $files = $partition->files ?? [];
            $files[] = [
                'path' => $path,
                'type' => $fileType,
                'pupitre' => $pupitre,
                'name' => $file->getClientOriginalName(),
                'uploaded_at' => now()->toISOString(),
            ];

            $partition->files = $files;
            $partition->save();

            return response()->json([
                'success' => true,
                'message' => 'Fichier uploadé avec succès',
                'data' => [
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'pupitre' => $pupitre,
                    'file_type' => $fileType,
                    'partition_id' => $partition->id,
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Erreur dans ChantController::uploadFile: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'upload: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Utilitaire pour récupérer l'ID d'un pupitre par son nom
     */
    private function getPupitreIdByName($name, $choraleId)
    {
        $pupitre = \App\Models\ChoralePupitre::where('chorale_id', $choraleId)
            ->where('nom', 'LIKE', '%' . $name . '%')
            ->first();
        
        return $pupitre?->id;
    }
}
