<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuideResource;
use App\Models\Guide;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GuideController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $guides = Guide::query()
            ->where('status', 'published')
            ->orderByRaw('COALESCE(published_at, updated_at, created_at) DESC')
            ->get();

        return GuideResource::collection($guides);
    }

    public function show(Guide $guide): GuideResource
    {
        abort_unless($guide->status === 'published', 404);

        return new GuideResource($guide);
    }
}
