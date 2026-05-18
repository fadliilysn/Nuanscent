<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AromaCategoryResource;
use App\Http\Resources\AromaTagResource;
use App\Http\Resources\OccasionResource;
use App\Models\AromaCategory;
use App\Models\AromaTag;
use App\Models\Occasion;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ReferenceDataController extends Controller
{
    public function aromaCategories(): AnonymousResourceCollection
    {
        return AromaCategoryResource::collection(AromaCategory::query()->orderBy('id')->get());
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
