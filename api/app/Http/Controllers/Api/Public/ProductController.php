<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\StatType;
use App\Http\Controllers\Controller;
use App\Http\Resources\PublicProductResource;
use App\Models\Store;
use App\Services\CatalogCache;
use App\Services\StatService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Lo único que puede viajar en la URL del listado. Cualquier otra cosa
     * (un `utm_source` pegado al link de WhatsApp, por ejemplo) sale sin
     * caché: entraría en los enlaces de paginación de la respuesta guardada.
     */
    private const QUERY = ['category', 'search', 'page'];

    public function __construct(
        private readonly CatalogCache $cache,
        private readonly StatService $stats,
    ) {}

    public function index(Request $request, string $slug): JsonResponse
    {
        if (! $this->cacheable($request)) {
            return response()->json($this->products($request, $slug));
        }

        $key = 'products:'.$request->string('category').':'.$request->integer('page', 1);

        return response()->json(
            $this->cache->remember($slug, $key, fn (): array => $this->products($request, $slug))
        );
    }

    public function show(Request $request, string $slug, string $productSlug): JsonResponse
    {
        /* Fuera del `remember`: adentro sólo se ejecuta la primera vez y las
           vistas servidas desde la caché no se contarían. */
        $this->stats->track($request, $slug, StatType::ProductView, $productSlug);

        $data = $this->cache->remember($slug, 'product:'.$productSlug, function () use ($slug, $productSlug): array {
            $product = $this->store($slug)
                ->products()
                ->where('slug', $productSlug)
                ->where('visible', true)
                ->with(['category', 'images.media'])
                ->firstOrFail();

            return ['product' => (new PublicProductResource($product))->resolve()];
        });

        return response()->json($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function products(Request $request, string $slug): array
    {
        $products = $this->store($slug)
            ->products()
            ->where('visible', true)
            ->with(['category', 'images.media'])
            ->when($request->filled('category'), fn (Builder $query) => $query->whereHas(
                'category',
                fn (Builder $q) => $q->where('slug', $request->string('category'))
            ))
            ->when($request->filled('search'), fn (Builder $query) => $query->where(
                'name',
                'like',
                '%'.$request->string('search').'%'
            ))
            ->orderByDesc('featured')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return PublicProductResource::collection($products)->response()->getData(true);
    }

    /**
     * La búsqueda es texto libre: cachearla dejaría una entrada por término y
     * un bot pidiendo `?search=aaa`, `?search=aab`… llenaría el disco. El
     * filtro por categoría y la paginación sí se cachean.
     */
    private function cacheable(Request $request): bool
    {
        return ! $request->filled('search')
            && empty(array_diff(array_keys($request->query()), self::QUERY));
    }

    private function store(string $slug): Store
    {
        return Store::where('slug', $slug)->where('active', true)->firstOrFail();
    }
}
