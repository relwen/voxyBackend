<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vocalise extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'pupitre_id',
        'audio_path',
        'chorale_id',
        'rubrique_section_id',
        'vocalise_part',
    ];

    /**
     * Attributs à ajouter à la sérialisation JSON
     */
    protected $appends = ['audio_url'];

    /**
     * Les attributs qui doivent être castés.
     */
    protected $casts = [
        'vocalise_part' => 'array',
    ];

    /**
     * Relation avec la chorale
     */
    public function chorale()
    {
        return $this->belongsTo(Chorale::class);
    }

    /**
     * Relation avec la section de rubrique (dossier/section)
     */
    public function rubriqueSection()
    {
        return $this->belongsTo(RubriqueSection::class, 'rubrique_section_id');
    }

    /**
     * Relation avec le pupitre
     */
    public function pupitre()
    {
        return $this->belongsTo(ChoralePupitre::class, 'pupitre_id');
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
     * Scope pour filtrer par pupitre
     */
    public function scopeForPupitre($query, $pupitreId)
    {
        return $query->where('pupitre_id', $pupitreId);
    }

    /**
     * Scope pour filtrer par chorale
     */
    public function scopeForChorale($query, $choraleId)
    {
        return $query->where('chorale_id', $choraleId);
    }
}
