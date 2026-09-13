<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlatformLogoResource;
use App\Services\PlatformLogoService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Los logos que el superadmin dejó para elegir, tal como los ve el dueño de una
 * tienda: sólo lectura.
 */
class StoreLogoController extends Controller
{
    public function __construct(private readonly PlatformLogoService $logos) {}

    public function index(): AnonymousResourceCollection
    {
        return PlatformLogoResource::collection($this->logos->all());
    }
}
