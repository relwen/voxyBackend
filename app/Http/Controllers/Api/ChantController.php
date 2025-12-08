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
            
            Log::info('ChantController::index - Début', [
                'user_id' => $user->id ?? null,
                'chorale_id' => $choraleId
            ]);
            
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
            
            Log::info('ChantController::index - Catégorie Chants', [
                'category_id' => $chantsRubrique->id
            ]);
            
            // Récupérer les sections (dossiers) de la catégorie "Chants"
            $sections = \App\Models\RubriqueSection::where('category_id', $chantsRubrique->id)
                ->whereNull('dossier_id') // Sections de premier niveau
                ->with(['partitions.pupitre'])
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
            
            Log::info('ChantController::index - Sections trouvées', [
                'count' => $sections->count()
            ]);
            
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
     * Afficher les chants d'une section spécifique
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
            
            // Récupérer la section
            $section = \App\Models\RubriqueSection::where('id', $id)
                ->whereHas('category', function($query) use ($choraleId) {
                    $query->where('name', 'Chants')
                          ->where('chorale_id', $choraleId);
                })
                ->with(['partitions.pupitre'])
                ->first();
            
            if (!$section) {
                return response()->json([
                    'success' => false,
                    'message' => 'Section de chants non trouvée'
                ], 404);
            }
            
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
            
            // Convertir les partitions en format ChantDeMesse
            $chants = $validPartitions->map(function($partition) {
                return $this->partitionToChantDeMesse($partition);
            })->values();
            
            return response()->json([
                'success' => true,
                'data' => $chants
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur dans ChantController::show: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des chants: ' . $e->getMessage()
            ], 500);
        }
    }
}

