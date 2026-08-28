<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicProductResource;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request, string $slug): AnonymousResourceCollection
    {
        $store = $this->store($slug);

        $products = $store->products()
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

        return PublicProductResource::collection($products);
    }

    public function show(string $slug, string $productSlug): JsonResponse
    {
        $product = $this->store($slug)
            ->products()
            ->where('slug', $productSlug)
            ->where('visible', true)
            ->with(['category', 'images.media'])
            ->firstOrFail();

        return response()->json(['product' => new PublicProductResource($product)]);
    }

    private function store(string $slug): Store
    {
        return Store::where('slug', $slug)->where('active', true)->firstOrFail();
    }
}
