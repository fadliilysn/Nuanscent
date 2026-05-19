<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Perfume extends Model
{
    protected $fillable = [
        'brand_id',
        'name',
        'slug',
        'short_description',
        'official_description',
        'concentration',
        'volume_ml',
        'price_min',
        'price_max',
        'image_url',
        'marketed_gender',
        'intensity',
        'main_aroma_category_id',
        'source_url',
        'source_name',
        'last_verified_at',
        'data_status',
    ];

    protected $casts = [
        'volume_ml' => 'integer',
        'price_min' => 'integer',
        'price_max' => 'integer',
        'last_verified_at' => 'date',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function mainAromaCategory(): BelongsTo
    {
        return $this->belongsTo(AromaCategory::class, 'main_aroma_category_id');
    }

    public function aromaTags(): BelongsToMany
    {
        return $this->belongsToMany(AromaTag::class, 'perfume_aroma_tag');
    }

    public function notes(): BelongsToMany
    {
        return $this->belongsToMany(Note::class, 'perfume_notes')
            ->withPivot('position');
    }

    public function occasions(): BelongsToMany
    {
        return $this->belongsToMany(Occasion::class, 'perfume_occasion');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PerfumeVariant::class);
    }

    public function refreshPriceRangeFromVariants(bool $clearWhenNoVariants = false): void
    {
        if (! $this->variants()->exists()) {
            if ($clearWhenNoVariants) {
                $this->forceFill([
                    'price_min' => null,
                    'price_max' => null,
                ])->saveQuietly();
            }

            return;
        }

        $prices = $this->variants()
            ->whereNotNull('price')
            ->pluck('price');

        $this->forceFill([
            'price_min' => $prices->min(),
            'price_max' => $prices->max(),
        ])->saveQuietly();
    }
}
