<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lo mínimo para nombrar un producto desde otra pantalla: la lista de espera
 * y los pedidos no necesitan la ficha entera.
 */
class PublicProductSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'sold_out' => $this->sold_out,
        ];
    }
}
