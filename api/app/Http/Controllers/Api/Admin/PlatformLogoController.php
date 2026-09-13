<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformLogoRequest;
use App\Services\PlatformService;
use Illuminate\Http\JsonResponse;

class PlatformLogoController extends Controller
{
    public function __construct(private readonly PlatformService $platform) {}

    /**
     * `$variant` lo fija la ruta: `auth` para las pantallas de acceso, `panel`
     * para la barra lateral e `icon` para el ícono cuadrado de la app.
     */
    public function store(PlatformLogoRequest $request, string $variant): JsonResponse
    {
        $this->platform->saveLogo($variant, $request->file('image'));

        return response()->json(['logos' => $this->platform->logos()]);
    }

    public function destroy(string $variant): JsonResponse
    {
        $this->platform->deleteLogo($variant);

        return response()->json(['logos' => $this->platform->logos()]);
    }
}
