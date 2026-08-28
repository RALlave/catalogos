<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\AttachProductImagesRequest;
use App\Http\Requests\Product\ReorderProductImagesRequest;
use App\Http\Requests\Product\StoreProductImagesRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProductImageController extends Controller
{
    public function __construct(private readonly ProductImageService $images) {}

    public function store(StoreProductImagesRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        $this->images->storeMany($product, $request->file('images'));

        return response()->json([
            'product' => new ProductResource($this->fresh($product)),
        ], 201);
    }

    /**
     * Sumar a la galería imágenes que ya están en la biblioteca de la tienda.
     */
    public function attach(AttachProductImagesRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        $mediaIds = $request->validated()['media_ids'];

        if (! $this->images->belongToStore($product, $mediaIds)) {
            throw new AccessDeniedHttpException('Some of the images do not belong to the store.');
        }

        $this->images->attach($product, $mediaIds);

        return response()->json([
            'product' => new ProductResource($this->fresh($product)),
        ], 201);
    }

    public function destroy(Request $request, Product $product, ProductImage $image): JsonResponse
    {
        Gate::authorize('update', $product);

        if ($image->product_id !== $product->id) {
            throw new AccessDeniedHttpException('The image does not belong to the product.');
        }

        $this->images->delete($image);

        return response()->json([
            'product' => new ProductResource($this->fresh($product)),
        ]);
    }

    public function reorder(ReorderProductImagesRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        $ids = $request->validated()['ids'];

        if ($product->images()->whereIn('id', $ids)->count() !== count($ids)) {
            throw new AccessDeniedHttpException('Some of the images do not belong to the product.');
        }

        $this->images->reorder($ids);

        return response()->json([
            'product' => new ProductResource($this->fresh($product)),
        ]);
    }

    private function fresh(Product $product): Product
    {
        return $product->load(['category', 'images.media']);
    }
}
