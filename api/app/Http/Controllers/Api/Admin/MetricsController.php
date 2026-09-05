<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminStoreResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\StatService;
use Illuminate\Http\JsonResponse;

class MetricsController extends Controller
{
    public function __construct(private readonly StatService $stats) {}

    public function __invoke(): JsonResponse
    {
        $latestStores = Store::query()
            ->with('user')
            ->withCount(['categories', 'products'])
            ->latest('id')
            ->take(5)
            ->get();

        return response()->json([
            'metrics' => [
                'stores' => [
                    'total' => Store::count(),
                    'active' => Store::where('active', true)->count(),
                    'inactive' => Store::where('active', false)->count(),
                ],
                'users' => [
                    'total' => User::role(UserRole::StoreOwner->value)->count(),
                    'suspended' => User::role(UserRole::StoreOwner->value)->whereNotNull('suspended_at')->count(),
                ],
                'categories' => Category::count(),
                'products' => [
                    'total' => Product::count(),
                    'visible' => Product::where('visible', true)->count(),
                    'featured' => Product::where('featured', true)->count(),
                ],
            ],
            'top_stores' => $this->stats->topStores(),
            'days' => StatService::PERIOD,
            'latest_stores' => AdminStoreResource::collection($latestStores),
        ]);
    }
}
