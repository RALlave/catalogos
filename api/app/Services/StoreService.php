<?php

namespace App\Services;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class StoreService
{
    public function __construct(private readonly MediaService $media) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): Store
    {
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug($data['name']);

        // Refreshed so the database defaults (active) reach the response.
        return $user->store()->create($data)->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Store $store, array $data): Store
    {
        $store->update($data);

        return $store->refresh();
    }

    /**
     * Build a slug from the store name, adding a numeric suffix when it is taken.
     * Reserved slugs are treated as taken: the store lives at the root of the
     * domain and cannot shadow a platform route.
     */
    public function uniqueSlug(string $name): string
    {
        $reserved = config('catalog.reserved_slugs');

        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while (in_array($slug, $reserved, true) || Store::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Subir el logo o la portada también los deja en la biblioteca, listos para
     * reutilizarse.
     */
    public function saveImage(Store $store, UploadedFile $file, string $field): Store
    {
        return $this->setImage($store, $this->media->store($store, $file)->id, $field);
    }

    /**
     * Elegir el logo o la portada de una imagen que ya está en la biblioteca.
     */
    public function setImage(Store $store, ?int $mediaId, string $field): Store
    {
        $store->update([$field.'_media_id' => $mediaId]);

        return $store->refresh();
    }

    /**
     * La imagen se saca de la tienda pero sigue en la biblioteca.
     */
    public function deleteImage(Store $store, string $field): Store
    {
        return $this->setImage($store, null, $field);
    }
}
