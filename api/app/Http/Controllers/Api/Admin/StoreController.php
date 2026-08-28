<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminStoreRequest;
use App\Http\Requests\Admin\UpdateAdminStoreRequest;
use App\Http\Resources\AdminStoreResource;
use App\Models\Store;
use App\Services\Admin\StoreProvisionService;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    public function __construct(
        private readonly StoreProvisionService $provision,
        private readonly StoreService $stores,
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
            ->when($request->filled('active'), fn ($query) => $query->where('active', $request->boolean('active')))
            ->latest('id')
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return AdminStoreResource::collection($stores);
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
}
