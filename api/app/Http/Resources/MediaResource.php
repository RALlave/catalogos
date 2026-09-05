<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Relación ya resuelta en el usuario del request: no pega a la base por imagen.
        $store = $request->user()?->store;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'alt' => $this->alt,
            'path' => $this->path,
            'url' => $this->url(),
            /* The library grid shows the small one; the picker preview, the big one. */
            'thumb_url' => $this->url('thumb'),
            'srcset' => $this->srcset(),
            'mime' => $this->mime,
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            /* Quién la está usando: el panel avisa a qué productos afecta el borrado. */
            'used_by' => $this->whenLoaded('products', fn () => $this->products
                ->map(fn ($product) => ['id' => $product->id, 'name' => $product->name])
                ->values()),
            /* Los heros que la muestran: borrarla los deja sin foto, no los borra. */
            'used_in_heroes' => $this->whenLoaded('heroes', fn () => $this->heroes
                ->map(fn ($hero) => ['id' => $hero->id, 'title' => $hero->title])
                ->values()),
            'used_as_logo' => $store?->logo_media_id === $this->id,
            'used_as_cover' => $store?->cover_media_id === $this->id,
            'created_at' => $this->created_at,
        ];
    }
}
