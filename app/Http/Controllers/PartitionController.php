<?php

namespace App\Http\Controllers;

use App\Models\Partition;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Helpers\FileHelper;

class PartitionController extends Controller
{
    /**
     * Display a listing of the partitions.
     */
    public function index(Request $request)
    {
        $query = Partition::with(['category', 'chorale'])->orderBy('created_at', 'desc');
        
        // Filtrer par catégorie si spécifiée
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $partitions = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $partitions->map(function ($partition) {
                return array_merge($partition->toArray(), [
                    'files_with_metadata' => $partition->files_with_metadata,
                    'category_name' => $partition->category->name ?? null,
                    'chorale_name' => $partition->chorale->name ?? null,
                ]);
            })
        ]);
    }

    /**
     * Store a newly created partition in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'chorale_id' => 'required|exists:chorales,id',
            'files.*' => 'nullable|file|max:20480', // 20MB max pour tous les types
        ]);

        $data = $request->except(['files']);

        // Préparer les données pour le nommage des fichiers
        $messeNom = null;
        $partie = null;
        $subPartie = null;
        $pupitreNom = null;

        // Récupérer le nom de la messe si rubrique_section_id est fourni
        if ($request->has('rubrique_section_id') && !empty($request->rubrique_section_id)) {
            $rubriqueSection = \App\Models\RubriqueSection::find($request->rubrique_section_id);
            if ($rubriqueSection) {
                $messeNom = $rubriqueSection->nom;
            }
        }

        // Récupérer la partie si messe_part est fourni
        if ($request->has('messe_part')) {
            $messePart = is_array($request->messe_part) ? $request->messe_part : json_decode($request->messe_part, true);
            if ($messePart) {
                $partie = $messePart['part'] ?? null;
                $subPartie = $messePart['subPart'] ?? null;
            }
        }

        // Récupérer le nom du pupitre
        if ($request->has('pupitre_id') && !empty($request->pupitre_id)) {
            $pupitre = \App\Models\ChoralePupitre::find($request->pupitre_id);
            if ($pupitre) {
                $pupitreNom = $pupitre->nom;
            }
        }

        // Traiter tous les fichiers de manière unifiée avec nommage personnalisé
        if ($request->hasFile('files')) {
            $filePaths = [];
            foreach ($request->file('files') as $file) {
                // Générer le nom de fichier personnalisé
                $customFileName = FileHelper::generatePartitionFileName(
                    $file,
                    null,
                    $messeNom,
                    $partie,
                    $subPartie,
                    $pupitreNom
                );
                
                // Déterminer le chemin de stockage selon le type de fichier
                $storagePath = FileHelper::getStoragePath($file->getClientOriginalName());
                
                // Stocker le fichier avec le nom personnalisé
                $path = $file->storeAs($storagePath, $customFileName, 'public');
                $filePaths[] = $path;
            }
            $data['files'] = $filePaths;
        }

        $partition = Partition::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Partition created successfully.',
            'data' => array_merge($partition->toArray(), [
                'files_with_metadata' => $partition->files_with_metadata,
                'category_name' => $partition->category->name ?? null,
                'chorale_name' => $partition->chorale->name ?? null,
            ])
        ], 201);
    }

    /**
     * Display the specified partition.
     */
    public function show(Partition $partition)
    {
        return response()->json([
            'success' => true,
            'data' => array_merge($partition->toArray(), [
                'files_with_metadata' => $partition->files_with_metadata,
                'category_name' => $partition->category->name ?? null,
                'chorale_name' => $partition->chorale->name ?? null,
            ])
        ]);
    }

    /**
     * Update the specified partition in storage.
     */
    public function update(Request $request, Partition $partition)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'chorale_id' => 'required|exists:chorales,id',
            'files.*' => 'nullable|file|max:20480',
            'remove_files' => 'nullable|array',
        ]);

        $data = $request->except(['files', 'remove_files']);

        // Gérer la suppression de fichiers
        if ($request->has('remove_files') && $partition->files) {
            $filesToRemove = $request->input('remove_files');
            $currentFiles = $partition->files ?? [];
            
            foreach ($filesToRemove as $index) {
                if (isset($currentFiles[$index])) {
                    Storage::disk('public')->delete($currentFiles[$index]);
                    unset($currentFiles[$index]);
                }
            }
            
            $data['files'] = array_values($currentFiles);
        }

        // Ajouter de nouveaux fichiers avec nommage personnalisé
        if ($request->hasFile('files')) {
            // Charger les relations nécessaires pour le nommage
            $partition->load(['rubriqueSection', 'pupitre']);
            
            // Mettre à jour les données de la partition pour le nommage
            $partition->fill($data);
            
            // Récupérer les fichiers existants pour éviter les doublons
            $existingFiles = $data['files'] ?? $partition->files ?? [];
            
            $newFilePaths = [];
            foreach ($request->file('files') as $file) {
                // Générer le nom de fichier personnalisé basé sur la partition
                $customFileName = FileHelper::generatePartitionFileName($file, $partition);
                
                // Déterminer le chemin de stockage selon le type de fichier
                $storagePath = FileHelper::getStoragePath($file->getClientOriginalName());
                
                // S'assurer que le nom de fichier est unique
                $uniqueFileName = FileHelper::ensureUniqueFileName($customFileName, array_merge($existingFiles, $newFilePaths), $storagePath);
                
                // Stocker le fichier avec le nom personnalisé unique
                $path = $file->storeAs($storagePath, $uniqueFileName, 'public');
                
                // Vérifier que le fichier n'est pas déjà dans la liste (éviter les doublons)
                if (!in_array($path, array_merge($existingFiles, $newFilePaths))) {
                    $newFilePaths[] = $path;
                }
            }
            
            // Fusionner avec les fichiers existants (sans doublons)
            $data['files'] = array_merge($existingFiles, $newFilePaths);
        }

        $partition->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Partition updated successfully.',
            'data' => array_merge($partition->toArray(), [
                'files_with_metadata' => $partition->files_with_metadata,
                'category_name' => $partition->category->name ?? null,
                'chorale_name' => $partition->chorale->name ?? null,
            ])
        ]);
    }

    /**
     * Remove the specified partition from storage.
     */
    public function destroy(Partition $partition)
    {
        // Supprimer tous les fichiers associés (nouveau système unifié)
        if ($partition->files) {
            foreach ($partition->files as $item) {
                // Gérer le cas où $item est un tableau ou une chaîne
                $path = is_array($item) ? ($item['path'] ?? $item['name'] ?? '') : $item;
                if (!empty($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $partition->delete();

        return response()->json([
            'success' => true,
            'message' => 'Partition deleted successfully.'
        ], 200);
    }

    /**
     * Get partitions for synchronization, optionally newer than a given timestamp.
     */
    public function getForSync(Request $request)
    {
        $lastSync = $request->get('last_sync', '1970-01-01 00:00:00');
        
        $partitions = Partition::with(['category', 'chorale'])
            ->where('updated_at', '>', $lastSync)
            ->orderBy('updated_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $partitions->map(function ($partition) {
                return array_merge($partition->toArray(), [
                    'files_with_metadata' => $partition->files_with_metadata,
                    'category_name' => $partition->category->name ?? null,
                    'chorale_name' => $partition->chorale->name ?? null,
                ]);
            }),
            'last_sync' => now()->toDateTimeString()
        ]);
    }

    /**
     * Download a specific file for a partition.
     */
    public function downloadFile($id, $fileIndex)
    {
        $partition = Partition::findOrFail($id);
        
        if (!$partition->files || !isset($partition->files[$fileIndex])) {
            return response()->json(['success' => false, 'message' => 'File not found.'], 404);
        }

        $item = $partition->files[$fileIndex];
        // Gérer le cas où $item est un tableau ou une chaîne
        $filePath = is_array($item) ? ($item['path'] ?? $item['name'] ?? '') : $item;
        
        if (empty($filePath) || !Storage::disk('public')->exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'File not found on server.'], 404);
        }

        $path = Storage::disk('public')->path($filePath);
        $filename = basename($filePath);

        return Response::download($path, $filename);
    }
}