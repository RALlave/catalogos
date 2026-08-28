<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Registra lo que el cliente mandó por WhatsApp.
     *
     * El precio y el nombre se leen de la base, nunca de lo que llegó en la
     * petición: el catálogo es público y cualquiera puede escribir el cuerpo.
     * Lo único que se acepta del cliente es qué producto y cuántos.
     *
     * @param  array<int, array{slug: string, quantity: int}>  $items
     */
    public function create(Store $store, array $items): Order
    {
        $quantities = collect($items)
            ->mapWithKeys(fn (array $item) => [$item['slug'] => (int) $item['quantity']]);

        $products = $store->products()
            ->whereIn('slug', $quantities->keys())
            ->where('visible', true)
            ->get();

        abort_if($products->isEmpty(), 422, 'Ninguno de los productos está disponible.');

        return DB::transaction(function () use ($store, $products, $quantities) {
            $lines = $products->map(fn (Product $product) => [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->sale_price ?? $product->price,
                'quantity' => $quantities[$product->slug],
            ]);

            /* Sin ningún producto con precio el total queda en null: es
               distinto de cero y así se ve que la tienda no publica precios. */
            $priced = $lines->whereNotNull('price');

            $order = $store->orders()->create([
                'items_count' => $lines->sum('quantity'),
                'total' => $priced->isEmpty()
                    ? null
                    : $priced->sum(fn (array $line) => $line['price'] * $line['quantity']),
                'currency' => $store->currency,
            ]);

            $order->items()->createMany($lines->all());

            return $order->load('items');
        });
    }
}
