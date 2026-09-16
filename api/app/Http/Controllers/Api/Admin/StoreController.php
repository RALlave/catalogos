<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminStoreRequest;
use App\Http\Requests\Admin\UpdateAdminStoreRequest;
use App\Http\Resources\AdminStoreResource;
use App\Models\Store;
use App\Services\Admin\StoreProvisionService;
use App\Services\StoreService;
use App\Services\StoreTrashService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    public function __construct(
        private readonly StoreProvisionService $provision,
        private readonly StoreService $stores,
        private readonly StoreTrashService $trash,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $stores = Store::query()
            ->with(['user', 'logoMedia', 'coverMedia'])
            ->withCount(['categories', 'products'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($query) => $query->where('email', 'like', "%{$search}%"));
                });
            })
            ->when(
                $request->string('status')->toString() === 'trash',
                fn ($query) => $query->whereNotNull('trashed_at')->latest('trashed_at'),
                fn ($query) => $query->whereNull('trashed_at')
                    ->when($request->string('status')->toString() === 'published', fn ($query) => $query->where('active', true))
                    ->when($request->string('status')->toString() === 'hidden', fn ($query) => $query->where('active', false)),
            )
            ->latest('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return AdminStoreResource::collection($stores)->additional(['counts' => $this->counts()]);
    }

    /**
     * Lo que muestra cada opción del select de estados. "Todas" no suma las
     * de la papelera: esas tienen su propia opción.
     *
     * @return array{all: int, published: int, hidden: int, trash: int}
     */
    private function counts(): array
    {
        $row = Store::query()
            ->selectRaw('SUM(trashed_at IS NULL) AS all_count')
            ->selectRaw('SUM(trashed_at IS NULL AND active = 1) AS published')
            ->selectRaw('SUM(trashed_at IS NULL AND active = 0) AS hidden')
            ->selectRaw('SUM(trashed_at IS NOT NULL) AS trash')
            ->toBase()
            ->first();

        return [
            'all' => (int) $row->all_count,
            'published' => (int) $row->published,
            'hidden' => (int) $row->hidden,
            'trash' => (int) $row->trash,
        ];
    }

    public function show(Store $store): JsonResponse
    {
        $store->load('user')->loadCount(['categories', 'products']);

        return response()->json(['store' => new AdminStoreResource($store)]);
    }

    public function store(StoreAdminStoreRequest $request): JsonResponse
    {
        $store = $this->provision->provision($request->validated());

        $store->load('user')->loadCount(['categories', 'products']);

        return response()->json(['store' => new AdminStoreResource($store)], 201);
    }

    public function update(UpdateAdminStoreRequest $request, Store $store): JsonResponse
    {
        $store = $this->stores->update($store, $request->validated());

        $store->load('user')->loadCount(['categories', 'products']);

        return response()->json(['store' => new AdminStoreResource($store)]);
    }

    /**
     * Toggle the public visibility of the catalog.
     */
    public function active(Request $request, Store $store): JsonResponse
    {
        $request->validate(['active' => ['required', 'boolean']]);

        $store = $this->stores->update($store, ['active' => $request->boolean('active')]);

        $store->load('user')->loadCount(['categories', 'products']);

        return response()->json(['store' => new AdminStoreResource($store)]);
    }

    /**
     * Mueve la tienda a la papelera: el catálogo deja de verse, el dueño sigue
     * entrando a su panel.
     */
    public function trash(Store $store): JsonResponse
    {
        $store = $this->trash->trash($store);

        $store->load('user')->loadCount(['categories', 'products']);

        return response()->json(['store' => new AdminStoreResource($store)]);
    }

    public function restore(Store $store): JsonResponse
    {
        $store = $this->trash->restore($store);

        $store->load('user')->loadCount(['categories', 'products']);

        return response()->json(['store' => new AdminStoreResource($store)]);
    }

    /**
     * Borrado definitivo: la tienda, su dueño, sus datos y sus archivos. Sólo
     * desde la papelera, para que no se pueda borrar de un clic una tienda
     * que está viva.
     */
    public function destroy(Store $store): JsonResponse
    {
        if (! $store->isTrashed()) {
            return response()->json([
                'message' => 'Primero hay que mover la tienda a la papelera.',
            ], 422);
        }

        $this->trash->destroy($store);

        return response()->json(null, 204);
    }
}
