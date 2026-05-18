<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Note extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'description_simple',
        'note_family',
    ];

    public function perfumes(): BelongsToMany
    {
        return $this->belongsToMany(Perfume::class, 'perfume_notes')
            ->withPivot('position');
    }
}
