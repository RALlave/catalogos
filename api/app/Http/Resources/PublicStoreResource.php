<?php

namespace App\Http\Resources;

use App\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicStoreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'logo_url' => $this->logoMedia?->url(),
            'cover_url' => $this->coverMedia?->url(),
            'theme' => $this->theme(),
            'hero_effect' => $this->hero_effect,
            'heroes' => PublicHeroResource::collection($this->whenLoaded('activeHeroes')),
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
            'categories' => PublicCategoryResource::collection($this->whenLoaded('activeCategories')),
        ];
    }

    /**
     * El tema resuelto: la paleta con sus colores y las tres opciones de
     * forma, que el catálogo aplica como atributos data-* en el <html>.
     *
     * @return array<string, mixed>
     */
    private function theme(): array
    {
        $themes = app(ThemeService::class);

        return [
            'palette' => $this->palette,
            'colors' => $themes->colors($this->palette),
            'radius' => $themes->option('radius', $this->radius),
            'nav' => $themes->option('nav', $this->nav),
            'banner' => $themes->option('banner', $this->banner),
        ];
    }
}
