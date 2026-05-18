<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::query()
            ->withCount(['perfumes' => fn ($query) => $query->where('data_status', 'published')])
            ->orderBy('name')
            ->get();

        return BrandResource::collection($brands);
    }

    public function show(Brand $brand): BrandResource
    {
        $brand->load(['perfumes' => fn ($query) => $query
            ->where('data_status', 'published')
            ->with(['brand', 'mainAromaCategory', 'aromaTags', 'occasions'])
            ->orderBy('name')]);

        return new BrandResource($brand);
    }
}
