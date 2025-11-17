<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'audio_path',
        'pdf_path',
        'image_path',
        'audio_files',
        'pdf_files',
        'image_files',
        'category_id',
        'reference_id',
        'chorale_id',
    ];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
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