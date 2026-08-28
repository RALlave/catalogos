<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductCloneController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    public function __invoke(Request $request, Product $product): JsonResponse
    {
        Gate::authorize('view', $product);

        $copy = $this->products->duplicate($product);

        return response()->json([
            'product' => new ProductResource($copy->load(['category', 'images.media'])),
        ], 201);
    }
}
