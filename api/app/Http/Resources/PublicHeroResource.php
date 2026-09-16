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
            'image_srcset' => $this->media?->srcset(),
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'text' => $this->text,
            'button_text' => $this->button_text,
            'button_href' => $this->buttonHref(),
            'align' => $this->align,
        ];
    }

    /**
     * A destination that would open nothing — a hidden or deleted category,
     * or the featured section turned off — goes to the products grid.
     */
    private function buttonHref(): string
    {
        $links = config('catalog.hero_links');

        if ($this->link === 'category') {
            return $this->category?->active
                ? '/?cat='.rawurlencode($this->category->slug).'#products'
                : $links['products'];
        }

        if ($this->link === 'featured' && ! $this->store?->featured_enabled) {
            return $links['products'];
        }

        return $links[$this->link] ?? $links['products'];
    }
}
