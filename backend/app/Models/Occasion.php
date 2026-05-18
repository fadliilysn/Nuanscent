<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Occasion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function perfumes(): BelongsToMany
    {
        return $this->belongsToMany(Perfume::class, 'perfume_occasion');
    }
}
