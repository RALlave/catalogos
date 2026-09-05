<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\StatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DashboardController extends Controller
{
    private const LATEST = 5;

    public function __construct(private readonly StatService $stats) {}

    public function __invoke(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        $latest = $store->products()
            ->with(['category', 'images.media'])
            ->latest('id')
            ->take(self::LATEST)
            ->get();

        return response()->json([
            'metrics' => [
                'products' => [
                    'total' => $store->products()->count(),
                    'visible' => $store->products()->where('visible', true)->count(),
                    'featured' => $store->products()->where('featured', true)->count(),
                    'sold_out' => $store->products()->where('sold_out', true)->count(),
                ],
                'categories' => [
                    'total' => $store->categories()->count(),
                    'active' => $store->categories()->where('active', true)->count(),
                ],
            ],
            'stats' => $this->stats->report($store),
            'latest_products' => ProductResource::collection($latest),
        ]);
    }
}
