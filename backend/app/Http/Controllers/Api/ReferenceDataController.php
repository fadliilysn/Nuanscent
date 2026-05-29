<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AromaCategoryResource;
use App\Http\Resources\AromaTagResource;
use App\Http\Resources\OccasionResource;
use App\Models\AromaCategory;
use App\Models\AromaTag;
use App\Models\Occasion;
use App\Support\AromaCategoryCatalog;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferenceDataController extends Controller
{
    public function aromaCategories(): AnonymousResourceCollection
    {
        $orderedSlugs = AromaCategoryCatalog::publicSlugs();
        $categories = AromaCategory::query()
            ->whereIn('slug', $orderedSlugs)
            ->get()
            ->sortBy(fn (AromaCategory $category): int => array_search($category->slug, $orderedSlugs, true))
            ->values();

        return AromaCategoryResource::collection($categories);
    }

    public function aromaTags(): AnonymousResourceCollection
    {
        return AromaTagResource::collection(AromaTag::query()->orderBy('name')->get());
    }

    public function occasions(): AnonymousResourceCollection
    {
        return OccasionResource::collection(Occasion::query()->orderBy('name')->get());
    }
}
