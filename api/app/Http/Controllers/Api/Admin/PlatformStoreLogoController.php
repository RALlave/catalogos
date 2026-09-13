<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformStoreLogoRequest;
use App\Http\Resources\PlatformLogoResource;
use App\Models\PlatformLogo;
use App\Services\PlatformLogoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * La galería de logos que el superadmin deja lista para que cada tienda elija
 * el suyo. Es aparte de la marca de la plataforma: eso son tres logos fijos y
 * esto es una lista abierta.
 */
class PlatformStoreLogoController extends Controller
{
    public function __construct(private readonly PlatformLogoService $logos) {}

    public function index(): AnonymousResourceCollection
    {
        return PlatformLogoResource::collection($this->logos->all());
    }

    public function store(PlatformStoreLogoRequest $request): JsonResponse
    {
        $logo = $this->logos->store($request->file('image'), $request->input('name'));

        return response()->json(['logo' => new PlatformLogoResource($logo)], 201);
    }

    public function update(PlatformStoreLogoRequest $request, PlatformLogo $platformLogo): JsonResponse
    {
        $logo = $this->logos->update($platformLogo, $request->validated());

        return response()->json(['logo' => new PlatformLogoResource($logo)]);
    }

    /**
     * Marcarlo como el logo con el que arranca una tienda nueva. Devuelve la
     * lista entera: marcar uno desmarca al anterior.
     */
    public function markDefault(PlatformLogo $platformLogo): AnonymousResourceCollection
    {
        $this->logos->setDefault($platformLogo);

        return PlatformLogoResource::collection($this->logos->all());
    }

    public function destroy(PlatformLogo $platformLogo): JsonResponse
    {
        $this->logos->delete($platformLogo);

        return response()->json(['message' => __('Logo deleted.')]);
    }
}
