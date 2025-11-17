<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'couleur',
        'icone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Relation avec les références
     */
    public function references()
    {
        return $this->hasMany(Reference::class)->ordered();
    }

    /**
     * Scope pour ordonner par nom
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('nom', 'asc');
    }
}