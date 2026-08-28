<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly ProductImageService $images) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Store $store, array $data): Product
    {
        $data['slug'] = $data['slug'] ?? $this->uniqueSlug($store, $data['name']);
        $data['order'] = $data['order'] ?? $this->nextOrder($store);

        // Refreshed so the database defaults (visible, featured) reach the response.
        return $store->products()->create($data)->refresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh();
    }

    /**
     * Copy the product with its gallery. The clone is born hidden and at the end
     * of the order so it never reaches the catalog before being reviewed.
     */
    public function duplicate(Product $product): Product
    {
        return DB::transaction(function () use ($product): Product {
            $store = $product->store;
            $name = Str::limit($product->name.' (copia)', 255, '');

            $copy = $store->products()->create([
                'category_id' => $product->category_id,
                'name' => $name,
                'slug' => $this->uniqueSlug($store, $name),
                'sku' => $product->sku,
                'description' => $product->description,
                'specs' => $product->specs,
                'benefits' => $product->benefits,
                'badges' => $product->badges,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'featured' => false,
                'visible' => false,
                'sold_out' => $product->sold_out,
                'order' => $this->nextOrder($store),
            ]);

            $this->images->copyMany($product, $copy);

            return $copy->refresh();
        });
    }

    /**
     * Las imágenes viven en la biblioteca de la tienda: borrar el producto solo
     * suelta las referencias, los archivos quedan disponibles para otro.
     */
    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * Hand out the `order` values these products already had, following the new
     * sequence. Reordering one page of the listing only swaps positions between
     * the products it received and leaves the rest of the store untouched.
     *
     * @param  array<int, int>  $ids
     */
    public function reorder(Store $store, array $ids): void
    {
        DB::transaction(function () use ($store, $ids): void {
            $this->normalizeOrder($store);

            $slots = $store->products()
                ->whereIn('id', $ids)
                ->orderBy('order')
                ->pluck('order')
                ->all();

            foreach ($ids as $position => $id) {
                Product::where('id', $id)->update(['order' => $slots[$position]]);
            }
        });
    }

    /**
     * The swap needs every product to hold a different `order`, so a store with
     * repeated values is rewritten as a 1..N sequence before touching anything.
     */
    private function normalizeOrder(Store $store): void
    {
        $orders = $store->products()->pluck('order');

        if ($orders->count() === $orders->unique()->count()) {
            return;
        }

        $ids = $store->products()->orderBy('order')->orderBy('id')->pluck('id');

        foreach ($ids as $position => $id) {
            Product::where('id', $id)->update(['order' => $position + 1]);
        }
    }

    /**
     * Filters shared by the admin listing.
     *
     * @param  Builder<Product>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Product>
     */
    public function applyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when(isset($filters['category_id']), fn (Builder $q) => $q->where('category_id', $filters['category_id']))
            ->when(isset($filters['visible']), fn (Builder $q) => $q->where('visible', filter_var($filters['visible'], FILTER_VALIDATE_BOOLEAN)))
            ->when(isset($filters['featured']), fn (Builder $q) => $q->where('featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN)))
            ->when(isset($filters['search']), fn (Builder $q) => $q->where('name', 'like', '%'.$filters['search'].'%'));
    }

    /**
     * Build a slug from the product name, adding a numeric suffix when the store already uses it.
     */
    public function uniqueSlug(Store $store, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 2;

        while ($store->products()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function nextOrder(Store $store): int
    {
        return (int) $store->products()->max('order') + 1;
    }
}
