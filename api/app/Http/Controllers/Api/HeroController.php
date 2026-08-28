<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hero\StoreHeroRequest;
use App\Http\Requests\Hero\UpdateHeroRequest;
use App\Http\Resources\HeroResource;
use App\Models\Hero;
use App\Models\Store;
use App\Services\HeroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HeroController extends Controller
{
    public function __construct(private readonly HeroService $heroes) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $heroes = $this->userStore($request)
            ->heroes()
            ->with('media')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        return HeroResource::collection($heroes);
    }

    public function store(StoreHeroRequest $request): JsonResponse
    {
        $hero = $this->heroes->create($this->userStore($request), $request->validated());

        return response()->json(['hero' => new HeroResource($hero->load('media'))], 201);
    }

    public function show(Request $request, Hero $hero): JsonResponse
    {
        Gate::authorize('view', $hero);

        return response()->json(['hero' => new HeroResource($hero->load('media'))]);
    }

    public function update(UpdateHeroRequest $request, Hero $hero): JsonResponse
    {
        Gate::authorize('update', $hero);

        $hero = $this->heroes->update($hero, $request->validated());

        return response()->json(['hero' => new HeroResource($hero->load('media'))]);
    }

    public function destroy(Request $request, Hero $hero): JsonResponse
    {
        Gate::authorize('delete', $hero);

        $this->heroes->delete($hero);

        return response()->json(['message' => __('Hero deleted.')]);
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
