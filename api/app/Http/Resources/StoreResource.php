<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'logo_media_id' => $this->logo_media_id,
            'logo' => $this->logoMedia?->path,
            /* Always drawn small, in the panel and in the catalog header. */
            'logo_url' => $this->logoMedia?->url('thumb'),
            'cover_media_id' => $this->cover_media_id,
            'cover' => $this->coverMedia?->path,
            'cover_url' => $this->coverMedia?->url(),
            'palette' => $this->palette,
            'radius' => $this->radius,
            'nav' => $this->nav,
            'banner' => $this->banner,
            'hero_effect' => $this->hero_effect,
            'cart_enabled' => $this->cart_enabled,
            'waitlist_enabled' => $this->waitlist_enabled,
            'description' => $this->description,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'industry' => $this->industry,
            'whatsapp' => $this->whatsapp,
            'phone' => $this->phone,
            'email' => $this->email,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'tiktok' => $this->tiktok,
            'website' => $this->website,
            'address' => $this->address,
            'map_url' => $this->map_url,
            'city' => $this->city,
            'country' => $this->country,
            'currency' => $this->currency,
            'schedules' => $this->schedules,
            'active' => $this->active,
            'public_url' => config('app.frontend_url').'/'.$this->slug,
            'created_at' => $this->created_at,
        ];
    }
}
