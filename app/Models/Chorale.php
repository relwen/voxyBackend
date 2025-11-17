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
}
