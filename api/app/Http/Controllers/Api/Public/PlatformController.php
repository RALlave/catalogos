<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Services\PlatformService;
use Illuminate\Http\JsonResponse;

class PlatformController extends Controller
{
    public function __construct(private readonly PlatformService $platform) {}

    /**
     * Marca de la plataforma. Va sin autenticar porque quien la pide son las
     * pantallas de acceso: ahí todavía no hay sesión que autenticar.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json(['logos' => $this->platform->logos()]);
    }
}
