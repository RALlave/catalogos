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
    public function __construct(private readonly MediaService $media) {}

    /**
     * Subir desde el producto también alimenta la biblioteca: el archivo entra
     * como media de la tienda y el producto solo la referencia.
     *
     * @param  array<int, UploadedFile>  $files
     * @return Collection<int, ProductImage>
     */
    public function storeMany(Product $product, array $files): Collection
    {
        $media = $this->media->storeMany($product->store, $files);

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
     * Quita la imagen del producto. El archivo sigue en la biblioteca.
     */
    public function delete(ProductImage $image): void
    {
        $image->delete();
    }

    /**
     * @param  array<int, int>  $ids
     */
    public function reorder(array $ids): void
    {
        DB::transaction(function () use ($ids): void {
            foreach ($ids as $position => $id) {
                ProductImage::where('id', $id)->update(['order' => $position]);
            }
        });
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
