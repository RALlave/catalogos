<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'description' => $this->description,
            'specs' => $this->specs,
            'benefits' => $this->benefits,
            'badges' => $this->badges,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'featured' => $this->featured,
            'sold_out' => $this->sold_out,
            'is_new' => $this->is_new,
            'category' => new PublicCategoryResource($this->whenLoaded('category')),
            /* One entry per photo, with the candidates for the srcset. */
            'images' => $this->whenLoaded('images', fn () => $this->images
                ->map(fn ($image) => $image->media?->responsive())
                ->filter()
                ->values()
                ->all()
            ),
        ];
    }
}
