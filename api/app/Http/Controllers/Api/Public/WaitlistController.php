<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreWaitlistEntryRequest;
use App\Models\Store;
use Illuminate\Http\JsonResponse;

class WaitlistController extends Controller
{
    /**
     * Anota a alguien en la lista de espera de un producto agotado.
     *
     * Sólo se acepta si el producto está efectivamente agotado: la lista de
     * espera de algo que se puede comprar no tiene sentido, y así el
     * formulario no se puede usar como buzón de datos.
     */
    public function store(StoreWaitlistEntryRequest $request, string $slug): JsonResponse
    {
        $store = Store::where('slug', $slug)
            ->where('active', true)
            ->where('waitlist_enabled', true)
            ->firstOrFail();

        $product = $store->products()
            ->where('slug', $request->validated('product_slug'))
            ->where('visible', true)
            ->where('sold_out', true)
            ->firstOrFail();

        /* Si la misma persona vuelve a anotarse se actualiza su fila en vez
           de duplicarla, y el aviso pendiente se reabre. */
        $store->waitlistEntries()->updateOrCreate(
            [
                'product_id' => $product->id,
                'phone' => $request->validated('phone'),
            ],
            [
                'name' => $request->validated('name'),
                'notified_at' => null,
            ],
        );

        return response()->json([
            'message' => 'Te avisamos por WhatsApp cuando vuelva a estar disponible.',
        ], 201);
    }
}
