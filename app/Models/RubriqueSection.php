<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RubriqueSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'dossier_id',
        'nom',
        'description',
        'order',
        'type', // 'dossier' ou 'section'
        'structure', // Structure JSON récursive pour les parties
    ];

    protected $casts = [
        'order' => 'integer',
        'structure' => 'array',
    ];

    /**
     * Relation avec la rubrique (catégorie)
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Relation avec les partitions
     */
    public function partitions()
    {
        return $this->hasMany(Partition::class, 'rubrique_section_id');
    }

    /**
     * Relation avec le dossier parent (si c'est une section dans un dossier)
     */
    public function dossier()
    {
        return $this->belongsTo(RubriqueSection::class, 'dossier_id');
    }

    /**
     * Relation avec les sections enfants (si c'est un dossier)
     */
    public function sections()
    {
        return $this->hasMany(RubriqueSection::class, 'dossier_id')->ordered();
    }

    /**
     * Vérifier si c'est un dossier
     */
    public function isDossier(): bool
    {
        return $this->type === 'dossier';
    }

    /**
     * Vérifier si c'est une section
     */
    public function isSection(): bool
    {
        return $this->type === 'section';
    }
    
    /**
     * Obtenir le nombre de partitions
     */
    public function getPartitionsCountAttribute()
    {
        return $this->partitions()->count();
    }

    /**
     * Scope pour ordonner par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('nom', 'asc');
    }
}
