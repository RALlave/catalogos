<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    private const PER_PAGE = 20;

    public function __construct(private readonly ProductService $products) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->where('store_id', $this->userStore($request)->id)
            ->with(['category', 'images.media']);

        $products = $this->products
            ->applyFilters($query, $request->only('category_id', 'visible', 'featured', 'search'))
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($this->userStore($request), $request->validated());

        return response()->json([
            'product' => new ProductResource($product->load(['category', 'images.media'])),
        ], 201);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        Gate::authorize('view', $product);

        return response()->json([
            'product' => new ProductResource($product->load(['category', 'images.media'])),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        $product = $this->products->update($product, $request->validated());

        return response()->json([
            'product' => new ProductResource($product->load(['category', 'images.media'])),
        ]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        Gate::authorize('delete', $product);

        $this->products->delete($product);

        return response()->json(['message' => __('Product deleted.')]);
    }

    private function userStore(Request $request): Store
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        return $store;
    }
}
