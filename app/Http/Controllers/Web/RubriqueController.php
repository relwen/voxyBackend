<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\RubriqueSection;
use App\Models\Partition;
use App\Models\Vocalise;
use App\Models\ChoralePupitre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RubriqueController extends Controller
{
    /**
     * Afficher une rubrique avec ses sections
     */
    public function show($id)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return redirect()->route('admin.chorale.config')->with('error', 'Vous n\'êtes associé à aucune chorale.');
        }

        $rubrique = Category::where('id', $id)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();
        
        // Charger les relations de manière conditionnelle selon le type de structure
        // Pour éviter les erreurs avec la colonne 'type' qui peut ne pas exister dans certains contextes
        try {
            if ($rubrique->structure_type === 'with_dossiers') {
                $rubrique->load([
                    'dossiers.sections.partitions.pupitre',
                    'directSections.partitions.pupitre',
                    'partitions.pupitre'
                ]);
            } elseif ($rubrique->structure_type === 'with_sections' || strtolower($rubrique->name) === 'messes' || strtolower($rubrique->name) === 'vocalises' || strtolower($rubrique->name) === 'chants') {
                if (strtolower($rubrique->name) === 'vocalises') {
                    $rubrique->load([
                        'directSections.vocalises.pupitre',
                    ]);
                } else {
                    $rubrique->load([
                        'directSections.partitions.pupitre',
                        'partitions.pupitre'
                    ]);
                }
            } else {
                // Structure simple
                $rubrique->load([
                    'partitions.pupitre'
                ]);
            }
        } catch (\Exception $e) {
            // En cas d'erreur, charger seulement les sections de base
            $rubrique->load([
                'sections.partitions.pupitre',
                'partitions.pupitre'
            ]);
        }

        $pupitres = $chorale->pupitres;

        return view('admin.rubriques.show', compact('rubrique', 'pupitres'));
    }

    /**
     * Afficher les détails d'une messe avec ses parties
     */
    public function showMesse($rubriqueId, $messeId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return redirect()->route('admin.chorale.config')->with('error', 'Vous n\'êtes associé à aucune chorale.');
        }

        $rubrique = Category::where('id', $rubriqueId)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        $messe = RubriqueSection::where('id', $messeId)
            ->where('category_id', $rubriqueId)
            ->with(['partitions.pupitre', 'vocalises.pupitre'])
            ->firstOrFail();

        // Grouper les partitions par partie
        $partitionsByPart = [];
        foreach ($messe->partitions as $partition) {
            $messePart = $partition->messe_part;
            if ($messePart && isset($messePart['part'])) {
                $partName = $messePart['part'];
                $subPartName = $messePart['subPart'] ?? null;
                $key = $subPartName ? "{$partName} > {$subPartName}" : $partName;
                
                if (!isset($partitionsByPart[$key])) {
                    $partitionsByPart[$key] = [];
                }
                $partitionsByPart[$key][] = $partition;
            } else {
                // Partitions sans partie spécifiée
                if (!isset($partitionsByPart['Sans partie'])) {
                    $partitionsByPart['Sans partie'] = [];
                }
                $partitionsByPart['Sans partie'][] = $partition;
            }
        }

        $pupitres = $chorale->pupitres;

        return view('admin.rubriques.messe-details', compact('rubrique', 'messe', 'partitionsByPart', 'pupitres'));
    }

    /**
     * Créer une messe simplifiée (juste le nom ou avec parties)
     */
    public function storeMesse(Request $request, $rubriqueId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $rubrique = Category::where('id', $rubriqueId)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        $request->validate([
            'nom' => 'required|string|max:255',
            'has_parts' => 'nullable|boolean',
            'structure' => 'nullable|array',
        ]);

        // Vérifier l'unicité du nom
        if (RubriqueSection::where('category_id', $rubriqueId)
            ->where('nom', $request->nom)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Une messe avec ce nom existe déjà.'
            ], 422);
        }

        $structure = null;
        if ($request->has_parts && $request->structure) {
            $structure = $request->structure;
        }

        $messe = RubriqueSection::create([
            'category_id' => $rubrique->id,
            'dossier_id' => null,
            'nom' => $request->nom,
            'description' => null,
            'order' => 0,
            'type' => 'section',
            'structure' => $structure,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Messe créée avec succès',
            'data' => $messe
        ]);
    }

    /**
     * Créer une nouvelle section ou dossier dans une rubrique
     */
    public function storeSection(Request $request, $rubriqueId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $rubrique = Category::where('id', $rubriqueId)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'type' => 'required|in:dossier,section',
            'dossier_id' => 'nullable|exists:rubrique_sections,id',
            'structure' => 'nullable|array',
        ]);

        // Vérifier l'unicité du nom dans le contexte approprié
        $uniqueQuery = RubriqueSection::where('category_id', $rubriqueId)
            ->where('nom', $request->nom);
        
        if ($request->dossier_id) {
            $uniqueQuery->where('dossier_id', $request->dossier_id);
        } else {
            $uniqueQuery->whereNull('dossier_id');
        }
        
        if ($uniqueQuery->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Un ' . $request->type . ' avec ce nom existe déjà dans ce contexte.'
            ], 422);
        }

        $section = RubriqueSection::create([
            'category_id' => $rubrique->id,
            'dossier_id' => $request->dossier_id,
            'nom' => $request->nom,
            'description' => $request->description,
            'order' => $request->order ?? 0,
            'type' => $request->type,
            'structure' => $request->structure ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => ucfirst($request->type) . ' créé(e) avec succès',
            'data' => $section
        ]);
    }

    /**
     * Mettre à jour une section
     */
    public function updateSection(Request $request, $rubriqueId, $sectionId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $section = RubriqueSection::where('id', $sectionId)
            ->whereHas('category', function($query) use ($chorale) {
                $query->where('chorale_id', $chorale->id);
            })
            ->firstOrFail();

        $request->validate([
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'type' => 'nullable|in:dossier,section',
            'dossier_id' => 'nullable|exists:rubrique_sections,id',
            'structure' => 'nullable|array',
        ]);

        // Vérifier l'unicité du nom
        $uniqueQuery = RubriqueSection::where('category_id', $rubriqueId)
            ->where('nom', $request->nom)
            ->where('id', '!=', $sectionId);
        
        if ($request->dossier_id) {
            $uniqueQuery->where('dossier_id', $request->dossier_id);
        } else {
            $uniqueQuery->whereNull('dossier_id');
        }
        
        if ($uniqueQuery->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Un élément avec ce nom existe déjà dans ce contexte.'
            ], 422);
        }

        $updateData = $request->all();
        if ($request->has('structure')) {
            $updateData['structure'] = $request->structure;
        }
        $section->update($updateData);

        return response()->json([
            'success' => true,
            'message' => ucfirst($section->type ?? 'section') . ' mise à jour avec succès',
            'data' => $section
        ]);
    }

    /**
     * Supprimer une section
     */
    public function destroySection($rubriqueId, $sectionId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $section = RubriqueSection::where('id', $sectionId)
            ->whereHas('category', function($query) use ($chorale) {
                $query->where('chorale_id', $chorale->id);
            })
            ->firstOrFail();

        // Vérifier s'il y a des partitions associées
        if ($section->partitions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer cette section car elle contient des partitions'
            ], 422);
        }

        $section->delete();

        return response()->json([
            'success' => true,
            'message' => 'Section supprimée avec succès'
        ]);
    }

    /**
     * Créer une partition directement dans une rubrique (sans section)
     */
    public function storePartitionDirect(Request $request, $rubriqueId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $rubrique = Category::where('id', $rubriqueId)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'nullable|exists:chorale_pupitres,id',
            'files.*' => 'nullable|file|max:20480',
        ]);

        // Si aucun pupitre n'est sélectionné, utiliser le pupitre par défaut
        $pupitreId = $request->pupitre_id;
        if (!$pupitreId) {
            $defaultPupitre = $chorale->getDefaultPupitre();
            if ($defaultPupitre) {
                $pupitreId = $defaultPupitre->id;
            }
        }

        // Gérer les fichiers avec nommage personnalisé
        $filePaths = [];
        $processedFileNames = []; // Pour éviter les doublons
        
        if ($request->hasFile('files')) {
            // Récupérer le nom du pupitre
            $pupitreNom = null;
            if ($pupitreId) {
                $pupitre = \App\Models\ChoralePupitre::find($pupitreId);
                if ($pupitre) {
                    $pupitreNom = $pupitre->nom;
                }
            }
            
            foreach ($request->file('files') as $file) {
                // Générer le nom de fichier personnalisé (pas de messe pour cette méthode)
                $customFileName = \App\Helpers\FileHelper::generatePartitionFileName(
                    $file,
                    null,
                    null, // Pas de messe
                    null, // Pas de partie
                    null, // Pas de sous-partie
                    $pupitreNom
                );
                
                // Déterminer le chemin de stockage selon le type de fichier
                $storagePath = \App\Helpers\FileHelper::getStoragePath($file->getClientOriginalName());
                
                // Vérifier si ce fichier n'a pas déjà été traité (éviter les doublons)
                $fullPath = $storagePath . '/' . $customFileName;
                if (in_array($fullPath, $processedFileNames)) {
                    continue; // Ignorer les doublons
                }
                
                // Stocker le fichier avec le nom personnalisé
                $path = $file->storeAs($storagePath, $customFileName, 'public');
                $filePaths[] = $path;
                $processedFileNames[] = $fullPath;
            }
        }

        $partition = Partition::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $rubrique->id,
            'rubrique_section_id' => null, // Pas de section
            'chorale_id' => $chorale->id,
            'pupitre_id' => $pupitreId,
            'files' => $filePaths,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Partition créée avec succès',
            'data' => $partition
        ]);
    }

    /**
     * Créer une partition dans une partie de messe
     */
    public function storePartitionForMessePart(Request $request, $rubriqueId, $messeId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $messe = RubriqueSection::where('id', $messeId)
            ->whereHas('category', function($query) use ($chorale, $rubriqueId) {
                $query->where('chorale_id', $chorale->id)
                      ->where('id', $rubriqueId);
            })
            ->firstOrFail();

        // Vérifier si la messe a une structure (parties)
        $hasStructure = $messe->structure && count($messe->structure) > 0;
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'nullable|exists:chorale_pupitres,id',
            'part' => $hasStructure ? 'required|string' : 'nullable|string',
            'subPart' => 'nullable|string',
            'files.*' => 'nullable|file|max:20480',
        ]);

        // Si aucun pupitre n'est sélectionné, utiliser le pupitre par défaut
        $pupitreId = $request->pupitre_id;
        if (!$pupitreId) {
            $defaultPupitre = $chorale->getDefaultPupitre();
            if ($defaultPupitre) {
                $pupitreId = $defaultPupitre->id;
            }
        }

        // Gérer les fichiers avec nommage personnalisé
        $filePaths = [];
        $processedFileNames = []; // Pour éviter les doublons
        
        if ($request->hasFile('files')) {
            // Récupérer le nom du pupitre
            $pupitreNom = null;
            if ($pupitreId) {
                $pupitre = \App\Models\ChoralePupitre::find($pupitreId);
                if ($pupitre) {
                    $pupitreNom = $pupitre->nom;
                }
            }
            
            foreach ($request->file('files') as $file) {
                // Générer le nom de fichier personnalisé
                $customFileName = \App\Helpers\FileHelper::generatePartitionFileName(
                    $file,
                    null,
                    $messe->nom, // Nom de la messe
                    $request->part, // Partie
                    $request->subPart, // Sous-partie
                    $pupitreNom
                );
                
                // Déterminer le chemin de stockage selon le type de fichier
                $storagePath = \App\Helpers\FileHelper::getStoragePath($file->getClientOriginalName());
                
                // Vérifier si ce fichier n'a pas déjà été traité (éviter les doublons)
                $fullPath = $storagePath . '/' . $customFileName;
                if (in_array($fullPath, $processedFileNames)) {
                    continue; // Ignorer les doublons
                }
                
                // Stocker le fichier avec le nom personnalisé
                $path = $file->storeAs($storagePath, $customFileName, 'public');
                $filePaths[] = $path;
                $processedFileNames[] = $fullPath;
            }
        }

        // Préparer les données de la partition
        $partitionData = [
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $messe->category_id,
            'rubrique_section_id' => $messe->id,
            'chorale_id' => $chorale->id,
            'pupitre_id' => $pupitreId,
            'files' => $filePaths,
        ];
        
        // Ajouter messe_part seulement si part est fourni
        if ($request->has('part') && !empty($request->part)) {
            $partitionData['messe_part'] = [
                'part' => $request->part,
                'subPart' => $request->subPart ?? null,
            ];
        }
        
        $partition = Partition::create($partitionData);

        return response()->json([
            'success' => true,
            'message' => 'Partition créée avec succès',
            'data' => $partition
        ]);
    }

    /**
     * Créer une partition dans une section
     */
    public function storePartition(Request $request, $rubriqueId, $sectionId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $section = RubriqueSection::where('id', $sectionId)
            ->whereHas('category', function($query) use ($chorale, $rubriqueId) {
                $query->where('chorale_id', $chorale->id)
                      ->where('id', $rubriqueId);
            })
            ->firstOrFail();

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'nullable|exists:chorale_pupitres,id',
            'files.*' => 'nullable|file|max:20480',
        ]);

        // Si aucun pupitre n'est sélectionné, utiliser le pupitre par défaut
        $pupitreId = $request->pupitre_id;
        if (!$pupitreId) {
            $defaultPupitre = $chorale->getDefaultPupitre();
            if ($defaultPupitre) {
                $pupitreId = $defaultPupitre->id;
            }
        }

        // Gérer les fichiers avec nommage personnalisé
        $filePaths = [];
        $processedFileNames = []; // Pour éviter les doublons
        
        if ($request->hasFile('files')) {
            // Récupérer le nom du pupitre
            $pupitreNom = null;
            if ($pupitreId) {
                $pupitre = \App\Models\ChoralePupitre::find($pupitreId);
                if ($pupitre) {
                    $pupitreNom = $pupitre->nom;
                }
            }
            
            foreach ($request->file('files') as $file) {
                // Générer le nom de fichier personnalisé
                $customFileName = \App\Helpers\FileHelper::generatePartitionFileName(
                    $file,
                    null,
                    $section->nom, // Nom de la messe/section
                    null, // Pas de partie spécifique
                    null, // Pas de sous-partie
                    $pupitreNom
                );
                
                // Déterminer le chemin de stockage selon le type de fichier
                $storagePath = \App\Helpers\FileHelper::getStoragePath($file->getClientOriginalName());
                
                // Vérifier si ce fichier n'a pas déjà été traité (éviter les doublons)
                $fullPath = $storagePath . '/' . $customFileName;
                if (in_array($fullPath, $processedFileNames)) {
                    continue; // Ignorer les doublons
                }
                
                // Stocker le fichier avec le nom personnalisé
                $path = $file->storeAs($storagePath, $customFileName, 'public');
                $filePaths[] = $path;
                $processedFileNames[] = $fullPath;
            }
        }

        $partition = Partition::create([
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $section->category_id,
            'rubrique_section_id' => $section->id,
            'chorale_id' => $chorale->id,
            'pupitre_id' => $pupitreId,
            'files' => $filePaths,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Partition créée avec succès',
            'data' => $partition
        ]);
    }

    /**
     * Afficher une section pour édition
     */
    public function showSection($rubriqueId, $sectionId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $section = RubriqueSection::where('id', $sectionId)
            ->whereHas('category', function($query) use ($chorale, $rubriqueId) {
                $query->where('chorale_id', $chorale->id)
                      ->where('id', $rubriqueId);
            })
            ->firstOrFail();

        return response()->json($section);
    }

    /**
     * Afficher les détails d'une vocalise avec ses parties
     */
    public function showVocalise($rubriqueId, $vocaliseId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return redirect()->route('admin.chorale.config')->with('error', 'Vous n\'êtes associé à aucune chorale.');
        }

        $rubrique = Category::where('id', $rubriqueId)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        $vocaliseSection = RubriqueSection::where('id', $vocaliseId)
            ->where('category_id', $rubriqueId)
            ->with(['vocalises.pupitre'])
            ->firstOrFail();

        // Grouper les vocalises par partie
        $vocalisesByPart = [];
        foreach ($vocaliseSection->vocalises as $vocalise) {
            $vocalisePart = $vocalise->vocalise_part;
            if ($vocalisePart && isset($vocalisePart['part'])) {
                $partName = $vocalisePart['part'];
                $subPartName = $vocalisePart['subPart'] ?? null;
                $key = $subPartName ? "{$partName} > {$subPartName}" : $partName;
                
                if (!isset($vocalisesByPart[$key])) {
                    $vocalisesByPart[$key] = [];
                }
                $vocalisesByPart[$key][] = $vocalise;
            } else {
                // Vocalises sans partie spécifiée
                if (!isset($vocalisesByPart['Sans partie'])) {
                    $vocalisesByPart['Sans partie'] = [];
                }
                $vocalisesByPart['Sans partie'][] = $vocalise;
            }
        }

        $pupitres = $chorale->pupitres;

        return view('admin.rubriques.vocalise-details', compact('rubrique', 'vocaliseSection', 'vocalisesByPart', 'pupitres'));
    }

    /**
     * Afficher les détails d'un chant avec ses parties
     */
    public function showChant($rubriqueId, $chantId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        if (!$chorale) {
            return redirect()->route('admin.chorale.config')->with('error', 'Vous n\'êtes associé à aucune chorale.');
        }

        $rubrique = Category::where('id', $rubriqueId)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        $chant = RubriqueSection::where('id', $chantId)
            ->where('category_id', $rubriqueId)
            ->with(['partitions.pupitre'])
            ->firstOrFail();

        // Grouper les partitions par partie
        $partitionsByPart = [];
        foreach ($chant->partitions as $partition) {
            $messePart = $partition->messe_part;
            if ($messePart && isset($messePart['part'])) {
                $partName = $messePart['part'];
                $subPartName = $messePart['subPart'] ?? null;
                $key = $subPartName ? "{$partName} > {$subPartName}" : $partName;
                
                if (!isset($partitionsByPart[$key])) {
                    $partitionsByPart[$key] = [];
                }
                $partitionsByPart[$key][] = $partition;
            } else {
                // Partitions sans partie spécifiée
                if (!isset($partitionsByPart['Sans partie'])) {
                    $partitionsByPart['Sans partie'] = [];
                }
                $partitionsByPart['Sans partie'][] = $partition;
            }
        }

        $pupitres = $chorale->pupitres;

        return view('admin.rubriques.chant-details', compact('rubrique', 'chant', 'partitionsByPart', 'pupitres'));
    }

    /**
     * Créer une vocalise dans une partie de section vocalise
     */
    public function storeVocaliseForSectionPart(Request $request, $rubriqueId, $sectionId)
    {
        $user = Auth::user();
        $chorale = $user->chorale;
        
        $section = RubriqueSection::where('id', $sectionId)
            ->whereHas('category', function($query) use ($chorale, $rubriqueId) {
                $query->where('chorale_id', $chorale->id)
                      ->where('id', $rubriqueId);
            })
            ->firstOrFail();

        // Vérifier si la section a une structure (parties)
        $hasStructure = $section->structure && count($section->structure) > 0;
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'required|exists:chorale_pupitres,id',
            'part' => $hasStructure ? 'required|string' : 'nullable|string',
            'subPart' => 'nullable|string',
            'audio_file' => 'required|file|mimes:mp3,wav,ogg,m4a|max:10240',
        ]);

        // Vérifier que le pupitre appartient à la chorale
        $pupitre = ChoralePupitre::where('id', $request->pupitre_id)
            ->where('chorale_id', $chorale->id)
            ->firstOrFail();

        // Gérer le fichier audio
        $audioPath = null;
        if ($request->hasFile('audio_file')) {
            $audioPath = $request->file('audio_file')->store('vocalises', 'public');
        }

        // Préparer les données de la vocalise
        $vocaliseData = [
            'title' => $request->title,
            'description' => $request->description,
            'pupitre_id' => $request->pupitre_id,
            'audio_path' => $audioPath,
            'chorale_id' => $chorale->id,
            'rubrique_section_id' => $section->id,
        ];
        
        // Ajouter vocalise_part seulement si part est fourni
        if ($request->has('part') && !empty($request->part)) {
            $vocaliseData['vocalise_part'] = [
                'part' => $request->part,
                'subPart' => $request->subPart ?? null,
            ];
        }
        
        $vocalise = Vocalise::create($vocaliseData);

        return response()->json([
            'success' => true,
            'message' => 'Vocalise créée avec succès',
            'data' => $vocalise
        ]);
    }

    /**
     * Mettre à jour une vocalise
     */
    public function updateVocaliseForSectionPart(Request $request, $rubriqueId, $sectionId, $vocaliseId)
    {
        $vocalise = Vocalise::where('rubrique_section_id', $sectionId)
            ->findOrFail($vocaliseId);
        
        $section = RubriqueSection::findOrFail($sectionId);
        $hasStructure = $section->structure && count($section->structure) > 0;
        
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'pupitre_id' => 'sometimes|required|exists:chorale_pupitres,id',
            'part' => $hasStructure ? 'nullable|string' : 'nullable|string',
            'subPart' => 'nullable|string',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a|max:10240',
        ]);
        
        // Vérifier que le pupitre appartient à la chorale si fourni
        if ($request->has('pupitre_id')) {
            $user = Auth::user();
            $chorale = $user->chorale;
            ChoralePupitre::where('id', $request->pupitre_id)
                ->where('chorale_id', $chorale->id)
                ->firstOrFail();
        }
        
        $data = $request->only(['title', 'description', 'pupitre_id']);
        
        // Gérer le fichier audio
        if ($request->hasFile('audio_file')) {
            // Supprimer l'ancien fichier s'il existe
            if ($vocalise->audio_path) {
                Storage::disk('public')->delete($vocalise->audio_path);
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
        
        $vocalise->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Vocalise mise à jour avec succès',
            'data' => $vocalise
        ]);
    }

    /**
     * Supprimer une vocalise
     */
    public function destroyVocaliseForSectionPart($rubriqueId, $sectionId, $vocaliseId)
    {
        $vocalise = Vocalise::where('rubrique_section_id', $sectionId)
            ->findOrFail($vocaliseId);
        
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
}
