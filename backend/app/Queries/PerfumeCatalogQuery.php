<?php

namespace App\Queries;

use App\Models\Perfume;
use App\Support\AromaCategoryCatalog;
use Illuminate\Database\Eloquent\Builder;

class PerfumeCatalogQuery
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Perfume>
     */
    public function query(array $filters): Builder
    {
        return Perfume::query()
            ->where('data_status', 'published')
            ->with(['brand', 'mainAromaCategory', 'aromaTags', 'occasions'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->whereRaw('LOWER(name) LIKE ?', ['%'.mb_strtolower($search).'%']);
            })
            ->when($filters['brand'] ?? null, fn (Builder $query, string $brand): Builder => $query
                ->whereHas('brand', fn (Builder $brandQuery) => $brandQuery->where('slug', $brand)))
            ->when($filters['aroma_category'] ?? null, fn (Builder $query, string $category): Builder => $query
                ->whereHas('mainAromaCategory', fn (Builder $categoryQuery) => $categoryQuery->whereIn('slug', AromaCategoryCatalog::filterSlugs($category))))
            ->when($filters['aroma_tag'] ?? null, fn (Builder $query, string $tag): Builder => $query
                ->whereHas('aromaTags', fn (Builder $tagQuery) => $tagQuery->where('slug', $tag)))
            ->when($filters['occasion'] ?? null, fn (Builder $query, string $occasion): Builder => $query
                ->whereHas('occasions', fn (Builder $occasionQuery) => $occasionQuery->where('slug', $occasion)))
            ->when(array_key_exists('price_min', $filters), function (Builder $query) use ($filters): void {
                $query->whereNotNull('price_max')->where('price_max', '>=', (int) $filters['price_min']);
            })
            ->when(array_key_exists('price_max', $filters), function (Builder $query) use ($filters): void {
                $query->whereNotNull('price_min')->where('price_min', '<=', (int) $filters['price_max']);
            })
            ->orderBy('name');
    }
}
