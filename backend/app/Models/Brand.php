<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'official_website',
        'logo_url',
    ];

    public function perfumes(): HasMany
    {
        return $this->hasMany(Perfume::class);
    }
}
