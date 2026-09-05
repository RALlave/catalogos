<?php

namespace App\Http\Controllers\Api\Public;

use App\Enums\StatType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\TrackRequest;
use App\Services\StatService;
use Illuminate\Http\Response;

class TrackController extends Controller
{
    public function __construct(private readonly StatService $stats) {}

    /**
     * Registra un gesto del visitante que la API no puede ver sola: hoy, el
     * botón de compartir.
     *
     * Responde 204 siempre: es telemetría, y el catálogo no debe romperse ni
     * enterarse de que la tienda o el producto no existen.
     */
    public function __invoke(TrackRequest $request, string $slug): Response
    {
        $this->stats->track(
            $request,
            $slug,
            StatType::from($request->validated('type')),
            $request->validated('product_slug'),
        );

        return response()->noContent();
    }
}
