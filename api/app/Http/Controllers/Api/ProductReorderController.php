<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ReorderProductsRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductReorderController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function __invoke(ReorderProductsRequest $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        $ids = $request->validated()['ids'];

        if ($store->products()->whereIn('id', $ids)->count() !== count($ids)) {
            throw new AccessDeniedHttpException('Some of the products do not belong to the store.');
        }

        $this->products->reorder($store, $ids);

        return response()->json(['message' => __('Products reordered.')]);
    }
}
