<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'sku' => $this->sku,
            'description' => $this->description,
            'specs' => $this->specs,
            'benefits' => $this->benefits,
            'badges' => $this->badges,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'featured' => $this->featured,
            'visible' => $this->visible,
            'sold_out' => $this->sold_out,
            'order' => $this->order,
            'main_image_url' => $this->mainImageUrl(),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at,
        ];
    }

    /**
     * First image of the gallery, handy for the listings that do not load the whole set.
     */
    private function mainImageUrl(): ?string
    {
        $image = $this->relationLoaded('images')
            ? $this->images->first()
            : $this->images()->with('media')->first();

        return $image?->media?->url();
    }
}
