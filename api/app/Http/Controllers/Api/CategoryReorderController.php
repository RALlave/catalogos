<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\ReorderCategoriesRequest;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryReorderController extends Controller
{
    public function __construct(private readonly CategoryService $categories) {}

    public function __invoke(ReorderCategoriesRequest $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        $ids = $request->validated()['ids'];

        if ($store->categories()->whereIn('id', $ids)->count() !== count($ids)) {
            throw new AccessDeniedHttpException('Some of the categories do not belong to the store.');
        }

        $this->categories->reorder($store, $ids);

        return response()->json(['message' => __('Categories reordered.')]);
    }
}
