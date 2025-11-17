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
        'voice_part',
        'audio_path',
        'chorale_id',
    ];

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
     * Scope pour filtrer par partie vocale
     */
    public function scopeForVoicePart($query, $voicePart)
    {
        return $query->where('voice_part', $voicePart);
    }

    /**
     * Scope pour filtrer par chorale
     */
    public function scopeForChorale($query, $choraleId)
    {
        return $query->where('chorale_id', $choraleId);
    }
}
