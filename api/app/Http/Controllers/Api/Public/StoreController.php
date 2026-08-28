<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicStoreResource;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class StoreController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $store = Store::with(['activeCategories', 'activeHeroes.media', 'logoMedia', 'coverMedia'])
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        return response()->json(['store' => new PublicStoreResource($store)]);
    }
}
