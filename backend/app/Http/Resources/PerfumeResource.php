<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerfumeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'official_description' => $this->when($request->route('perfume') !== null, $this->official_description),
            'concentration' => $this->concentration,
            'volume_ml' => $this->volume_ml,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'image_url' => $this->image_url,
            'marketed_gender' => $this->marketed_gender,
            'intensity' => $this->intensity,
            'source' => [
                'url' => $this->source_url,
                'name' => $this->source_name,
                'last_verified_at' => $this->last_verified_at?->toDateString(),
            ],
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'main_aroma_category' => new AromaCategoryResource($this->whenLoaded('mainAromaCategory')),
            'aroma_tags' => AromaTagResource::collection($this->whenLoaded('aromaTags')),
            'occasions' => OccasionResource::collection($this->whenLoaded('occasions')),
            'notes' => NoteResource::collection($this->whenLoaded('notes')),
        ];
    }
}
