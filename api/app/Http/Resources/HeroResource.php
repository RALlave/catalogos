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
            'image_thumb_url' => $this->media?->url('thumb'),
            'category_id' => $this->category_id,
            'eyebrow' => $this->eyebrow,
            'title' => $this->title,
            'text' => $this->text,
            'button_text' => $this->button_text,
            'link' => $this->link,
            'align' => $this->align,
            'order' => $this->order,
            'active' => $this->active,
            'created_at' => $this->created_at,
        ];
    }
}
