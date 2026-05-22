<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuideResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->excerpt,
            'status' => $this->status,
            'published_at' => $this->published_at?->toISOString(),
            'body' => $this->when($request->route('guide') !== null, $this->body),
        ];
    }
}
