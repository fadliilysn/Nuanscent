<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AromaTag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_polarizing',
    ];

    protected $casts = [
        'is_polarizing' => 'boolean',
    ];

    public function perfumes(): BelongsToMany
    {
        return $this->belongsToMany(Perfume::class, 'perfume_aroma_tag');
    }
}
