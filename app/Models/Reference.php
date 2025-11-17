<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'order_position',
        'messe_id',
    ];

    /**
     * Relation avec la messe
     */
    public function messe()
    {
        return $this->belongsTo(Messe::class);
    }

    /**
     * Relation avec les partitions
     */
    public function partitions()
    {
        return $this->hasMany(Partition::class);
    }

    /**
     * Scope pour ordonner par position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_position', 'asc')->orderBy('name', 'asc');
    }

    /**
     * Scope pour filtrer par messe
     */
    public function scopeForMesse($query, $messeId)
    {
        return $query->where('messe_id', $messeId);
    }
}