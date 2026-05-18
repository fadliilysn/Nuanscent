<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AromaCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function perfumes(): HasMany
    {
        return $this->hasMany(Perfume::class, 'main_aroma_category_id');
    }
}
