<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chorale extends Model
{
    protected $fillable = [
        'name',
        'description',
        'location'
    ];

    /**
     * Relation avec les utilisateurs
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Relation avec les partitions
     */
    public function partitions(): HasMany
    {
        return $this->hasMany(Partition::class);
    }

    /**
     * Relation avec les vocalises
     */
    public function vocalises(): HasMany
    {
        return $this->hasMany(Vocalise::class);
    }

    /**
     * Relation avec les pupitres de la chorale
     */
    public function pupitres(): HasMany
    {
        return $this->hasMany(ChoralePupitre::class)->ordered();
    }

    /**
     * Relation avec les catégories de la chorale
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('name');
    }

    /**
     * Obtenir le pupitre par défaut (Tutti)
     */
    public function getDefaultPupitre()
    {
        return $this->pupitres()->where('is_default', true)->first();
    }
}
