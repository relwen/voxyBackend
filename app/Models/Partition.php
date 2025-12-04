<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use App\Helpers\FileHelper;
use App\Models\ChoralePupitre;

class Partition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'files', // Champ unifié pour tous les fichiers
        'category_id',
        'rubrique_section_id', // Section de la rubrique (messe)
        'messe_part', // Référence à la partie de messe (ex: {"part": "Kyrié", "subPart": null})
        'reference_id',
        'messe_id',
        'chorale_id',
        'pupitre_id', // Relation vers chorale_pupitres
        'pupitre', // Gardé pour migration
        // Anciens champs gardés pour rétrocompatibilité
        'audio_path',
        'pdf_path',
        'image_path',
        'audio_files',
        'pdf_files',
        'image_files',
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'files' => 'array',
        'messe_part' => 'array',
        // Anciens champs gardés pour rétrocompatibilité
        'audio_files' => 'array',
        'pdf_files' => 'array',
        'image_files' => 'array',
    ];

    /**
     * Relation avec la catégorie
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relation avec la référence
     */
    public function reference()
    {
        return $this->belongsTo(Reference::class);
    }

    /**
     * Relation avec la chorale
     */
    public function chorale()
    {
        return $this->belongsTo(Chorale::class);
    }

    /**
     * Relation avec la messe
     */
    public function messe()
    {
        return $this->belongsTo(Messe::class);
    }

    /**
     * Relation avec le pupitre
     */
    public function pupitre()
    {
        return $this->belongsTo(ChoralePupitre::class, 'pupitre_id');
    }

    /**
     * Relation avec la section de rubrique (messe)
     */
    public function rubriqueSection()
    {
        return $this->belongsTo(RubriqueSection::class, 'rubrique_section_id');
    }

    /**
     * Accessor pour l'URL complète du fichier audio
     */
    public function getAudioUrlAttribute()
    {
        if ($this->audio_path) {
            return asset('storage/' . $this->audio_path);
        }
        return null;
    }

    /**
     * Accessor pour l'URL complète du fichier PDF
     */
    public function getPdfUrlAttribute()
    {
        if ($this->pdf_path) {
            return asset('storage/' . $this->pdf_path);
        }
        return null;
    }

    /**
     * Accessor pour l'URL complète de l'image
     */
    public function getImageUrlAttribute()
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return null;
    }

    /**
     * Accessor pour les URLs des fichiers audio multiples
     */
    public function getAudioUrlsAttribute()
    {
        if (!$this->audio_files) {
            return [];
        }
        
        return collect($this->audio_files)->map(function ($path) {
            return asset('storage/' . $path);
        })->toArray();
    }

    /**
     * Accessor pour les URLs des fichiers PDF multiples
     */
    public function getPdfUrlsAttribute()
    {
        if (!$this->pdf_files) {
            return [];
        }
        
        return collect($this->pdf_files)->map(function ($path) {
            return asset('storage/' . $path);
        })->toArray();
    }

    /**
     * Accessor pour les URLs des images multiples
     */
    public function getImageUrlsAttribute()
    {
        if (!$this->image_files) {
            return [];
        }
        
        return collect($this->image_files)->map(function ($path) {
            return asset('storage/' . $path);
        })->toArray();
    }

    /**
     * Accessor pour obtenir tous les fichiers avec leurs métadonnées
     */
    public function getFilesWithMetadataAttribute()
    {
        try {
            if (!$this->files) {
                return [];
            }

            return collect($this->files)->map(function ($item) {
                try {
                    // Gérer le cas où $item est un tableau ou une chaîne
                    $path = is_array($item) ? ($item['path'] ?? $item['name'] ?? '') : $item;
                    
                    // Si le chemin est vide, retourner null ou un tableau vide
                    if (empty($path)) {
                        return null;
                    }
                    
                    return [
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                        'name' => basename($path),
                        'type' => FileHelper::getFileType($path),
                        'icon' => FileHelper::getFileIcon($path),
                        'color_class' => FileHelper::getFileColorClass($path),
                        'type_label' => FileHelper::getFileTypeLabel($path),
                    ];
                } catch (\Exception $e) {
                    // En cas d'erreur, retourner les informations de base
                    $path = is_array($item) ? ($item['path'] ?? $item['name'] ?? '') : $item;
                    if (empty($path)) {
                        return null;
                    }
                    return [
                        'path' => $path,
                        'url' => asset('storage/' . $path),
                        'name' => basename($path),
                        'type' => 'unknown',
                        'icon' => 'file',
                        'color_class' => 'gray',
                        'type_label' => 'Fichier',
                    ];
                }
            })->filter()->values()->toArray();
        } catch (\Exception $e) {
            // En cas d'erreur globale, retourner un tableau vide
            Log::error('Erreur dans getFilesWithMetadataAttribute: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Accessor pour obtenir les fichiers par type
     */
    public function getFilesByTypeAttribute()
    {
        if (!$this->files) {
            return [
                FileHelper::TYPE_AUDIO => [],
                FileHelper::TYPE_PDF => [],
                FileHelper::TYPE_IMAGE => [],
                FileHelper::TYPE_VIDEO => [],
                FileHelper::TYPE_DOCUMENT => [],
                FileHelper::TYPE_OTHER => [],
            ];
        }

        $grouped = [
            FileHelper::TYPE_AUDIO => [],
            FileHelper::TYPE_PDF => [],
            FileHelper::TYPE_IMAGE => [],
            FileHelper::TYPE_VIDEO => [],
            FileHelper::TYPE_DOCUMENT => [],
            FileHelper::TYPE_OTHER => [],
        ];

        foreach ($this->files as $item) {
            // Gérer le cas où $item est un tableau ou une chaîne
            $path = is_array($item) ? ($item['path'] ?? $item['name'] ?? '') : $item;
            
            if (empty($path)) {
                continue;
            }
            
            $type = FileHelper::getFileType($path);
            $grouped[$type][] = [
                'path' => $path,
                'url' => asset('storage/' . $path),
                'name' => basename($path),
                'icon' => FileHelper::getFileIcon($path),
                'color_class' => FileHelper::getFileColorClass($path),
            ];
        }

        return $grouped;
    }

    /**
     * Obtenir les fichiers d'un type spécifique
     */
    public function getFilesByType(string $type): array
    {
        if (!$this->files) {
            return [];
        }

        return collect($this->files)
            ->map(function ($item) {
                // Gérer le cas où $item est un tableau ou une chaîne
                return is_array($item) ? ($item['path'] ?? $item['name'] ?? '') : $item;
            })
            ->filter(function ($path) {
                return !empty($path);
            })
            ->filter(function ($path) use ($type) {
                return FileHelper::getFileType($path) === $type;
            })
            ->map(function ($path) {
                return [
                    'path' => $path,
                    'url' => asset('storage/' . $path),
                    'name' => basename($path),
                    'icon' => FileHelper::getFileIcon($path),
                    'color_class' => FileHelper::getFileColorClass($path),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obtenir tous les fichiers avec leurs URLs
     */
    public function getFilesUrlsAttribute()
    {
        if (!$this->files) {
            return [];
        }

        return collect($this->files)
            ->map(function ($item) {
                // Gérer le cas où $item est un tableau ou une chaîne
                $path = is_array($item) ? ($item['path'] ?? $item['name'] ?? '') : $item;
                return !empty($path) ? asset('storage/' . $path) : null;
            })
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope pour filtrer par chorale
     */
    public function scopeForChorale($query, $choraleId)
    {
        return $query->where('chorale_id', $choraleId);
    }

    /**
     * Scope pour filtrer par type de fichier
     */
    public function scopeWithAudio($query)
    {
        return $query->whereNotNull('audio_path');
    }

    public function scopeWithPdf($query)
    {
        return $query->whereNotNull('pdf_path');
    }

    public function scopeWithImage($query)
    {
        return $query->whereNotNull('image_path');
    }
}