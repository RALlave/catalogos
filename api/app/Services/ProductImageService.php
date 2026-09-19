<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductImageService
{
    public function __construct(
        private readonly MediaService $media,
        private readonly CatalogCache $cache,
    ) {}

    /**
     * Subir desde el producto también alimenta la biblioteca: el archivo entra
     * como media de la tienda y el producto solo la referencia.
     *
     * Uploading from here only builds the product variants: the gallery never
     * shows a photo wider than the card. Picking one of these for the banner
     * later stretches the card, because the original is not kept.
     *
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, ProductImage>
     */
    public function storeMany(Product $product, array $files): Collection
    {
        $media = $this->media->storeMany($product->store, $files, 'product');

        return $this->attach($product, $media->pluck('id')->all());
    }

    /**
     * Sumar a la galería imágenes que ya están en la biblioteca. Las repetidas
     * se ignoran: una imagen no se muestra dos veces en el mismo producto.
     *
     * @param  array<int, int>  $mediaIds
     * @return Collection<int, ProductImage>
     */
    public function attach(Product $product, array $mediaIds): Collection
    {
        $order = (int) $product->images()->max('order') + 1;
        $taken = $product->images()->pluck('media_id')->all();

        foreach ($mediaIds as $mediaId) {
            if (in_array($mediaId, $taken, true)) {
                continue;
            }

            $product->images()->create([
                'media_id' => $mediaId,
                'order' => $order,
            ]);

            $taken[] = $mediaId;
            $order++;
        }

        return $product->images()->get();
    }

    /**
     * El clon comparte las imágenes del original: se copian las referencias, no
     * los archivos.
     */
    public function copyMany(Product $source, Product $target): void
    {
        foreach ($source->images as $image) {
            $target->images()->create([
                'media_id' => $image->media_id,
                'order' => $image->order,
            ]);
        }
    }

    /**
     * Swaps the photo of one gallery slot for another library image. The slot
     * keeps its position, so the main photo stays the main one. The previous
     * image is cleaned up like a removal: gone from disk if nothing else uses it.
     */
    public function replace(ProductImage $image, int $mediaId): ProductImage
    {
        $previous = $image->media;

        if ($previous?->id === $mediaId) {
            return $image;
        }

        $image->update(['media_id' => $mediaId]);

        if ($previous !== null && ! $previous->isInUse()) {
            $this->media->delete($previous);
        }

        return $image;
    }

    /**
     * Takes the image out of the product. If nothing else shows it (another
     * product, a hero, the logo or the cover), the file and its library entry
     * go too: an unused image is only disk garbage.
     */
    public function delete(ProductImage $image): void
    {
        $media = $image->media;

        $image->delete();

        if ($media !== null && ! $media->isInUse()) {
            $this->media->delete($media);
        }
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        $product = ProductImage::whereKey($ids)->value('product_id');

        DB::transaction(function () use ($ids): void {
            foreach ($ids as $position => $id) {
                ProductImage::where('id', $id)->update(['order' => $position]);
            }
        });

        /* El `update` masivo no dispara los eventos del modelo, así que la
           caché pública se invalida a mano. */
        if ($product !== null) {
            $this->cache->forgetStore(Product::findOrFail($product)->store);
        }
    }

    /**
     * Imágenes de la biblioteca que son de esta tienda.
     *
     * @param  array<int, int>  $mediaIds
     */
    public function belongToStore(Product $product, array $mediaIds): bool
    {
        return Media::where('store_id', $product->store_id)
            ->whereIn('id', $mediaIds)
            ->count() === count(array_unique($mediaIds));
    }
}
