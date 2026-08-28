<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Store;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $this->userStore($request)
            ->categories()
            ->withCount('products')
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categories->create($this->userStore($request), $request->validated());

        return response()->json(['category' => new CategoryResource($category)], 201);
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('view', $category);

        return response()->json([
            'category' => new CategoryResource($category->loadCount('products')),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        Gate::authorize('update', $category);

        $category = $this->categories->update($category, $request->validated());

        return response()->json([
            'category' => new CategoryResource($category->loadCount('products')),
        ]);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        Gate::authorize('delete', $category);

        $this->categories->delete($category);

        return response()->json(['message' => __('Category deleted.')]);
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
