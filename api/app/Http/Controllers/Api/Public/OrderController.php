<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Store;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    /**
     * Registra el pedido que el cliente está por mandar por WhatsApp.
     *
     * Es anónimo a propósito: no se le pide ni nombre ni teléfono. Lo que
     * queda guardado es qué se pidió, para que el dueño lo vea en el panel.
     */
    public function store(StoreOrderRequest $request, string $slug): JsonResponse
    {
        $store = Store::where('slug', $slug)
            ->where('active', true)
            ->where('cart_enabled', true)
            ->firstOrFail();

        $order = $this->orders->create($store, $request->validated('items'));

        return response()->json(['order' => new OrderResource($order)], 201);
    }
}
