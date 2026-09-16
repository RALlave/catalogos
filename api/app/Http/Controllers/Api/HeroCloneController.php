<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hero\StoreHeroRequest;
use App\Http\Resources\HeroResource;
use App\Models\Hero;
use App\Services\HeroService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class HeroCloneController extends Controller
{
    public function __construct(private readonly HeroService $heroes) {}

    public function __invoke(Request $request, Hero $hero): JsonResponse
    {
        Gate::authorize('view', $hero);

        /* A copy is one more hero: the same cap as creating one. */
        if ($hero->store->heroes()->count() >= StoreHeroRequest::MAX_HEROES) {
            throw ValidationException::withMessages([
                'hero' => __('The store already has the maximum of :max heroes.', ['max' => StoreHeroRequest::MAX_HEROES]),
            ]);
        }

        $copy = $this->heroes->duplicate($hero);

        return response()->json(['hero' => new HeroResource($copy->load('media'))], 201);
    }
}
