<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListPerfumesRequest;
use App\Http\Resources\PerfumeResource;
use App\Models\Perfume;
use App\Queries\PerfumeCatalogQuery;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PerfumeController extends Controller
{
    public function index(ListPerfumesRequest $request, PerfumeCatalogQuery $catalogQuery): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $perPage = (int) ($filters['per_page'] ?? 12);

        $perfumes = $catalogQuery->query($filters);

        return PerfumeResource::collection($perfumes->paginate($perPage)->withQueryString());
    }

    public function show(Perfume $perfume): PerfumeResource
    {
        abort_unless($perfume->data_status === 'published', 404);

        $perfume->load(['brand', 'mainAromaCategory', 'aromaTags', 'occasions', 'notes']);

        return new PerfumeResource($perfume);
    }
}
