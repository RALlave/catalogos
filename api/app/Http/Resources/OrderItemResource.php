<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'quantity' => $this->quantity,

            /* Null cuando el producto se borró después del pedido: la línea
               sigue existiendo, pero ya no hay a dónde enlazar. */
            'product_slug' => $this->whenLoaded('product', fn () => $this->product?->slug),
        ];
    }
}
