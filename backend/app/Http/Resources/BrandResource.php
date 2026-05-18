<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
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
            'description' => $this->description,
            'official_website' => $this->official_website,
            'logo_url' => $this->logo_url,
            'perfumes_count' => $this->whenCounted('perfumes'),
            'perfumes' => PerfumeResource::collection($this->whenLoaded('perfumes')),
        ];
    }
}
