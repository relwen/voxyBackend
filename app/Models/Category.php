<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
        'chorale_id',
        'structure_type',
        'structure_config',
    ];

    protected $casts = [
        'structure_config' => 'array',
    ];

    /**
     * Relation avec les partitions
     */
    public function partitions()
    {
        return $this->hasMany(Partition::class);
    }

    /**
     * Relation avec la chorale
     */
    public function chorale()
    {
        return $this->belongsTo(Chorale::class);
    }

    /**
     * Relation avec toutes les sections de la rubrique (pour compatibilité)
     */
    public function sections()
    {
        return $this->hasMany(RubriqueSection::class)->ordered();
    }

    /**
     * Scope pour obtenir les catégories globales (sans chorale)
     */
    public function scopeGlobal($query)
    {
        return $query->whereNull('chorale_id');
    }

    /**
     * Scope pour obtenir les catégories d'une chorale
     */
    public function scopeForChorale($query, $choraleId)
    {
        return $query->where('chorale_id', $choraleId);
    }

    /**
     * Vérifier si la rubrique utilise des sections
     */
    public function hasSections(): bool
    {
        return in_array($this->structure_type, ['with_sections', 'with_dossiers']);
    }

    /**
     * Vérifier si la rubrique utilise des dossiers
     */
    public function hasDossiers(): bool
    {
        return $this->structure_type === 'with_dossiers';
    }

    /**
     * Obtenir les dossiers (sections de type 'dossier')
     */
    public function dossiers()
    {
        return $this->hasMany(RubriqueSection::class)
            ->where('type', 'dossier')
            ->whereNull('dossier_id')
            ->ordered();
    }

    /**
     * Obtenir les sections directes (sans dossier)
     */
    public function directSections()
    {
        return $this->hasMany(RubriqueSection::class)
            ->where(function($query) {
                $query->where('type', 'section')
                      ->orWhereNull('type'); // Pour les anciennes sections sans type
            })
            ->whereNull('dossier_id')
            ->ordered();
    }
}
