<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\GuideController;
use App\Http\Controllers\Api\PerfumeController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\ReferenceDataController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:public-api')->group(function (): void {
    Route::get('brands', [BrandController::class, 'index']);
    Route::get('brands/{brand:slug}', [BrandController::class, 'show'])
        ->where('brand', '[A-Za-z0-9-]+');

    Route::get('perfumes', [PerfumeController::class, 'index']);
    Route::get('perfumes/{perfume:slug}', [PerfumeController::class, 'show'])
        ->where('perfume', '[A-Za-z0-9-]+');

    Route::get('aroma-categories', [ReferenceDataController::class, 'aromaCategories']);
    Route::get('aroma-tags', [ReferenceDataController::class, 'aromaTags']);
    Route::get('occasions', [ReferenceDataController::class, 'occasions']);

    Route::get('guides', [GuideController::class, 'index']);
    Route::get('guides/{guide:slug}', [GuideController::class, 'show'])
        ->where('guide', '[A-Za-z0-9-]+');
});

Route::post('recommendations', [RecommendationController::class, 'store'])
    ->middleware('throttle:recommendations');
