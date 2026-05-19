<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerfumeVariant extends Model
{
    protected $fillable = [
        'perfume_id',
        'label',
        'volume_ml',
        'price',
    ];

    protected $casts = [
        'volume_ml' => 'integer',
        'price' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saved(function (PerfumeVariant $variant): void {
            $variant->perfume?->refreshPriceRangeFromVariants(clearWhenNoVariants: true);
        });

        static::deleted(function (PerfumeVariant $variant): void {
            $variant->perfume?->refreshPriceRangeFromVariants(clearWhenNoVariants: true);
        });
    }

    public function perfume(): BelongsTo
    {
        return $this->belongsTo(Perfume::class);
    }
}
