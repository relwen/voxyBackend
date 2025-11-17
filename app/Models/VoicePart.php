<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoicePart extends Model
{
    protected $fillable = [
        'name',
        'partition_id',
        'pdf_path',
        'audio_path'
    ];

    /**
     * Relation avec la partition
     */
    public function partition(): BelongsTo
    {
        return $this->belongsTo(Partition::class);
    }
}
