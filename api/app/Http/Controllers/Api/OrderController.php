<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $this->userStore($request)
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(self::PER_PAGE);

        return OrderResource::collection($orders);
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        return response()->json(['order' => new OrderResource($order->load('items.product'))]);
    }

    /**
     * Qué se pide más. No es un módulo de estadísticas: es la única lectura
     * agregada que hace falta para responder esa pregunta desde el panel.
     * Se agrupa por nombre y no por producto porque las líneas de un producto
     * borrado también cuentan.
     */
    public function top(Request $request): JsonResponse
    {
        $products = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.store_id', $this->userStore($request)->id)
            ->groupBy('order_items.name')
            ->select('order_items.name', DB::raw('SUM(order_items.quantity) as quantity'))
            ->orderByDesc('quantity')
            ->limit(10)
            ->get();

        return response()->json(['products' => $products]);
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
