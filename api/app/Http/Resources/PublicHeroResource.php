<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicHeroResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'image_url' => $this->media?->url(),
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'text' => $this->text,
        ];
    }
}
