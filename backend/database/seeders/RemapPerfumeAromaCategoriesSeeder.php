<?php

namespace Database\Seeders;

use App\Models\AromaCategory;
use App\Models\Perfume;
use App\Support\AromaCategoryCatalog;
use Illuminate\Database\Seeder;

class RemapPerfumeAromaCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        if (AromaCategory::query()->whereIn('slug', AromaCategoryCatalog::publicSlugs())->count() < count(AromaCategoryCatalog::publicSlugs())) {
            $this->call(AromaCategorySeeder::class);
        }

        $categories = AromaCategory::query()
            ->whereIn('slug', AromaCategoryCatalog::publicSlugs())
            ->get()
            ->keyBy('slug');

        Perfume::query()
            ->with(['mainAromaCategory', 'aromaTags'])
            ->whereHas('mainAromaCategory', fn ($query) => $query->whereIn('slug', AromaCategoryCatalog::acceptedSlugs()))
            ->chunkById(100, function ($perfumes) use ($categories): void {
                foreach ($perfumes as $perfume) {
                    $currentSlug = $perfume->mainAromaCategory?->slug;

                    if ($currentSlug === null) {
                        continue;
                    }

                    $targetSlug = AromaCategoryCatalog::resolvePrimarySlug(
                        $currentSlug,
                        $perfume->aromaTags->pluck('slug')->all(),
                    );
                    $targetCategory = $categories[$targetSlug] ?? null;

                    if ($targetCategory && $perfume->main_aroma_category_id !== $targetCategory->id) {
                        $perfume->forceFill([
                            'main_aroma_category_id' => $targetCategory->id,
                        ])->save();
                    }
                }
            });
    }
}
