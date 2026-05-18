<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NoteResource extends JsonResource
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
            'description_simple' => $this->description_simple,
            'note_family' => $this->note_family,
            'position' => $this->whenPivotLoaded('perfume_notes', fn () => $this->pivot->position),
        ];
    }
}
