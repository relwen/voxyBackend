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
    ];

    /**
     * Relation avec les partitions
     */
    public function partitions()
    {
        return $this->hasMany(Partition::class);
    }

}
