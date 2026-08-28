<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'media_id' => $this->media_id,
            'path' => $this->media?->path,
            'url' => $this->media?->url(),
            'alt' => $this->media?->alt,
            'order' => $this->order,
        ];
    }
}
