<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\StatType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicStoreResource;
use App\Models\Store;
use App\Services\CatalogCache;
use App\Services\StatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(
        private readonly CatalogCache $cache,
        private readonly StatService $stats,
    ) {}

    public function show(Request $request, string $slug): JsonResponse
    {
        /* Fuera del `remember`: adentro sólo se ejecuta la primera vez y las
           visitas servidas desde la caché no se contarían. */
        $this->stats->track($request, $slug, StatType::Visit);

        /* Un slug que no existe no se cachea: la excepción sale del closure y
           `remember` no llega a guardar nada. */
        $data = $this->cache->remember($slug, 'store', function () use ($slug): array {
            $store = Store::with(['activeCategories', 'activeHeroes.media', 'activeHeroes.category', 'logoMedia', 'coverMedia'])
                ->where('slug', $slug)
                ->public()
                ->firstOrFail();

            /* El botón del hero mira si la vitrina está prendida: se le pasa la
               tienda ya cargada en vez de pedirla otra vez por cada hero. */
            $store->activeHeroes->each->setRelation('store', $store);

            /* Por JSON y no con `resolve()`: ese aplana un solo nivel y deja
               `heroes` y `categories` adentro como objetos Resource, que la
               caché guarda serializados para devolverlos rotos. */
            return ['store' => json_decode((new PublicStoreResource($store))->toJson(), true)];
        });

        return response()->json($data);
    }
}
