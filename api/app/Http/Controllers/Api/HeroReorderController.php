<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hero\ReorderHeroesRequest;
use App\Services\HeroService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HeroReorderController extends Controller
{
    public function __construct(private readonly HeroService $heroes) {}

    public function __invoke(ReorderHeroesRequest $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        $ids = $request->validated()['ids'];

        if ($store->heroes()->whereIn('id', $ids)->count() !== count($ids)) {
            throw new AccessDeniedHttpException('Some of the heroes do not belong to the store.');
        }

        $this->heroes->reorder($store, $ids);

        return response()->json(['message' => __('Heroes reordered.')]);
    }
}
