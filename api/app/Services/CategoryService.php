<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(private readonly CatalogCache $cache) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Store $store, array $data): Category
    {
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug($store, $data['name']);
        $data['order'] = $data['order'] ?? $this->nextOrder($store);

        // Refreshed so the database defaults (active) reach the response.
        return $store->categories()->create($data)->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    /**
     * Categories keep their products, which are left without a category.
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * Hand out the `order` values these categories already had, following the
     * new sequence. Reordering a filtered list only swaps positions between the
     * categories it received and leaves the rest of the store untouched.
     *
     * @param  array<int, int>  $ids
     */
    public function reorder(Store $store, array $ids): void
    {
        DB::transaction(function () use ($store, $ids): void {
            $this->normalizeOrder($store);

            $slots = $store->categories()
                ->whereIn('id', $ids)
                ->orderBy('order')
                ->pluck('order')
                ->all();

            foreach ($ids as $position => $id) {
                Category::where('id', $id)->update(['order' => $slots[$position]]);
            }
        });

        /* El reordenamiento escribe con `update` masivo, que no dispara los
           eventos del modelo: la caché pública se invalida a mano. */
        $this->cache->forgetStore($store);
    }

    /**
     * The swap needs every category to hold a different `order`, so a store with
     * repeated values is rewritten as a 1..N sequence before touching anything.
     */
    private function normalizeOrder(Store $store): void
    {
        $orders = $store->categories()->pluck('order');

        if ($orders->count() === $orders->unique()->count()) {
            return;
        }

        $ids = $store->categories()->orderBy('order')->orderBy('id')->pluck('id');

        foreach ($ids as $position => $id) {
            Category::where('id', $id)->update(['order' => $position + 1]);
        }
    }

    /**
     * Build a slug from the category name, adding a numeric suffix when the store already uses it.
     */
    public function uniqueSlug(Store $store, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while ($store->categories()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function nextOrder(Store $store): int
    {
        return (int) $store->categories()->max('order') + 1;
    }
}
