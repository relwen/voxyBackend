<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\RubriqueSection;
use App\Models\Partition;

class SearchController extends Controller
{
    /**
     * Recherche globale dans toutes les catégories
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez fournir un terme de recherche',
                'data' => null
            ], 400);
        }

        $user = Auth::user();
        $choraleId = $user?->chorale_id;

        // Rechercher dans les partitions
        $partitions = Partition::where(function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->when($choraleId, function($q) use ($choraleId) {
                $q->whereHas('category', function($query) use ($choraleId) {
                    $query->where('chorale_id', $choraleId);
                });
            })
            ->with(['category', 'pupitre'])
            ->limit(50)
            ->get()
            ->map(function($partition) {
                return [
                    'id' => $partition->id,
                    'title' => $partition->title,
                    'description' => $partition->description,
                    'category_id' => $partition->category_id,
                    'category_name' => $partition->category?->name,
                    'pupitre_id' => $partition->pupitre_id,
                    'pupitre_name' => $partition->pupitre?->nom,
                    'files' => $partition->files ?? [],
                    'created_at' => $partition->created_at,
                    'updated_at' => $partition->updated_at,
                ];
            });

        // Rechercher dans les vocalises (partitions de type vocalise)
        $vocalises = Partition::where(function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->whereHas('category', function($q) {
                $q->where('name', 'Vocalises');
            })
            ->when($choraleId, function($q) use ($choraleId) {
                $q->whereHas('category', function($query) use ($choraleId) {
                    $query->where('chorale_id', $choraleId);
                });
            })
            ->with(['category', 'pupitre'])
            ->limit(50)
            ->get()
            ->map(function($vocalise) {
                return [
                    'id' => $vocalise->id,
                    'title' => $vocalise->title,
                    'description' => $vocalise->description,
                    'voice_part' => $vocalise->pupitre?->nom ?? 'Tous',
                    'files' => $vocalise->files ?? [],
                    'created_at' => $vocalise->created_at,
                    'updated_at' => $vocalise->updated_at,
                ];
            });

        // Rechercher dans les messes (sections de la rubrique Messes)
        $messesRubrique = Category::where('name', 'Messes')
            ->when($choraleId, function($q) use ($choraleId) {
                $q->where('chorale_id', $choraleId);
            })
            ->first();

        $messes = collect([]);
        if ($messesRubrique) {
            $messes = RubriqueSection::where('category_id', $messesRubrique->id)
                ->where(function($q) use ($query) {
                    $q->where('nom', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->with(['partitions.pupitre'])
                ->limit(50)
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
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                    ];
                });
        }

        // Rechercher dans les chants de messe (partitions liées aux messes)
        $chants = Partition::where(function($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->whereNotNull('rubrique_section_id')
            ->when($choraleId, function($q) use ($choraleId) {
                $q->whereHas('category', function($query) use ($choraleId) {
                    $query->where('chorale_id', $choraleId);
                });
            })
            ->with(['category', 'pupitre', 'rubriqueSection'])
            ->limit(50)
            ->get()
            ->map(function($chant) {
                return [
                    'id' => $chant->id,
                    'section_id' => $chant->rubrique_section_id,
                    'titre' => $chant->title,
                    'description' => $chant->description,
                    'messe_nom' => $chant->rubriqueSection?->nom,
                    'ordre' => $chant->order ?? 0,
                    'active' => true,
                    'audio_path' => $chant->audio_path,
                    'pdf_path' => $chant->pdf_path,
                    'image_path' => $chant->image_path,
                    'audio_files' => $this->getFilesByType($chant->files ?? [], 'audio'),
                    'pdf_files' => $this->getFilesByType($chant->files ?? [], 'pdf'),
                    'image_files' => $this->getFilesByType($chant->files ?? [], 'image'),
                    'soprano_files' => $this->getFilesByPupitre($chant->files ?? [], 'Soprano'),
                    'alto_files' => $this->getFilesByPupitre($chant->files ?? [], 'Alto'),
                    'tenor_files' => $this->getFilesByPupitre($chant->files ?? [], 'Ténor'),
                    'basse_files' => $this->getFilesByPupitre($chant->files ?? [], 'Basse'),
                    'tutti_files' => $this->getFilesByPupitre($chant->files ?? [], 'Tutti'),
                    'created_at' => $chant->created_at,
                    'updated_at' => $chant->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'partitions' => $partitions,
                'vocalises' => $vocalises,
                'messes' => $messes,
                'chants' => $chants,
            ],
            'total' => $partitions->count() + $vocalises->count() + $messes->count() + $chants->count(),
        ]);
    }

    /**
     * Filtrer les fichiers par type
     */
    private function getFilesByType($files, $type)
    {
        if (empty($files) || !is_array($files)) {
            return [];
        }

        return collect($files)
            ->filter(function($file) use ($type) {
                $fileType = $file['type'] ?? '';
                return strpos($fileType, $type) !== false;
            })
            ->pluck('path')
            ->values()
            ->toArray();
    }

    /**
     * Filtrer les fichiers par pupitre
     */
    private function getFilesByPupitre($files, $pupitre)
    {
        if (empty($files) || !is_array($files)) {
            return [];
        }

        return collect($files)
            ->filter(function($file) use ($pupitre) {
                $filePupitre = $file['pupitre'] ?? $file['voice_part'] ?? '';
                return strcasecmp($filePupitre, $pupitre) === 0;
            })
            ->pluck('path')
            ->values()
            ->toArray();
    }
}
