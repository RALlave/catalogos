<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HeroResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'media_id' => $this->media_id,
            'image_url' => $this->media?->url(),
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'text' => $this->text,
            'order' => $this->order,
            'active' => $this->active,
            'created_at' => $this->created_at,
        ];
    }
}
