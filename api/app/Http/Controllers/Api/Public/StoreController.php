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
            $store = Store::with(['activeCategories', 'activeHeroes.media', 'logoMedia', 'coverMedia'])
                ->where('slug', $slug)
                ->where('active', true)
                ->firstOrFail();

            return ['store' => (new PublicStoreResource($store))->resolve()];
        });

        return response()->json($data);
    }
}
