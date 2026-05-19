<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecommendationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $perfume = $this->resource['perfume'];

        return [
            'id' => $perfume->id,
            'slug' => $perfume->slug,
            'name' => $perfume->name,
            'image_url' => $perfume->image_url,
            'price_min' => $perfume->price_min,
            'price_max' => $perfume->price_max,
            'brand' => $perfume->brand ? [
                'id' => $perfume->brand->id,
                'name' => $perfume->brand->name,
                'slug' => $perfume->brand->slug,
            ] : null,
            'main_aroma_category' => $perfume->mainAromaCategory ? [
                'id' => $perfume->mainAromaCategory->id,
                'name' => $perfume->mainAromaCategory->name,
                'slug' => $perfume->mainAromaCategory->slug,
            ] : null,
            'match_score' => $this->resource['match_percentage'],
            'match_percentage' => $this->resource['match_percentage'],
            'matched_reasons' => $this->resource['matched_reasons'],
            'blind_buy_caution' => $this->resource['blind_buy_caution'],
            'aroma_tags' => $perfume->aromaTags->map(fn ($tag): array => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'is_polarizing' => $tag->is_polarizing,
            ])->values(),
            'occasions' => $perfume->occasions->map(fn ($occasion): array => [
                'id' => $occasion->id,
                'name' => $occasion->name,
                'slug' => $occasion->slug,
            ])->values(),
            'data_limitations' => $this->resource['data_limitations'],
        ];
    }
}
