<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChoralePupitre extends Model
{
    use HasFactory;

    protected $fillable = [
        'chorale_id',
        'nom',
        'description',
        'color',
        'icon',
        'order',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Relation avec la chorale
     */
    public function chorale()
    {
        return $this->belongsTo(Chorale::class);
    }

    /**
     * Relation avec les partitions
     */
    public function partitions()
    {
        return $this->hasMany(Partition::class);
    }

    /**
     * Relation avec les vocalises
     */
    public function vocalises()
    {
        return $this->hasMany(Vocalise::class);
    }

    /**
     * Scope pour ordonner par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('nom', 'asc');
    }

    /**
     * Scope pour obtenir le pupitre par défaut
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
