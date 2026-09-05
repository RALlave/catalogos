<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Store;
use App\Services\CatalogCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Tira la caché pública de una tienda cada vez que se guarda algo suyo.
 *
 * Va como observador y no como llamada dentro de cada servicio para que no
 * dependa del camino: vale igual si el cambio llega del panel del dueño, del
 * superadmin, de un seeder o de tinker.
 *
 * Lo que NO cubre son los `update` masivos por query builder, que no disparan
 * eventos de modelo: los reordenamientos invalidan a mano (ver `reorder()` en
 * los servicios).
 */
class CatalogCacheObserver
{
    public function __construct(private readonly CatalogCache $cache) {}

    public function saved(Model $model): void
    {
        $this->flush($model);
    }

    public function deleted(Model $model): void
    {
        $this->flush($model);
    }

    private function flush(Model $model): void
    {
        // La tienda se pasa entera: puede venir con el slug recién cambiado.
        if ($model instanceof Store) {
            $this->cache->forgetStore($model);

            return;
        }

        $slug = $this->slug($model);

        if ($slug !== null) {
            $this->cache->forget($slug);
        }
    }

    private function slug(Model $model): ?string
    {
        $storeId = $model instanceof ProductImage
            ? Product::whereKey($model->product_id)->value('store_id')
            : $model->getAttribute('store_id');

        if ($storeId === null) {
            return null;
        }

        return Store::whereKey($storeId)->value('slug');
    }
}
