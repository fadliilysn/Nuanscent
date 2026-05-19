<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecommendationRequest;
use App\Http\Resources\RecommendationResource;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    public function store(RecommendationRequest $request, RecommendationService $recommendations): JsonResponse
    {
        $results = $recommendations->recommend($request->validated());

        return response()->json([
            'recommendations' => RecommendationResource::collection($results)->resolve($request),
        ]);
    }
}
