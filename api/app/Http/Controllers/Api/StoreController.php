<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\StoreResource;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoreController extends Controller
{
    public function __construct(private readonly StoreService $stores) {}

    public function show(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        return response()->json(['store' => new StoreResource($store)]);
    }

    public function store(StoreStoreRequest $request): JsonResponse
    {
        if ($request->user()->store) {
            throw new ConflictHttpException('The user already has a store.');
        }

        $store = $this->stores->create($request->user(), $request->validated());

        return response()->json(['store' => new StoreResource($store)], 201);
    }

    public function update(UpdateStoreRequest $request): JsonResponse
    {
        $store = $request->user()->store;

        if (! $store) {
            throw new NotFoundHttpException('The user does not have a store yet.');
        }

        $store = $this->stores->update($store, $request->validated());

        return response()->json(['store' => new StoreResource($store)]);
    }
}
