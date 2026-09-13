<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlatformLogoResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            /* El que se le pone a una tienda recién creada. */
            'is_default' => $this->is_default,
            'url' => $this->url(),
            /* Las grillas dibujan el chico; la vista previa, el grande. */
            'thumb_url' => $this->url('thumb'),
            'srcset' => $this->srcset(),
            'size' => $this->size,
            'width' => $this->width,
            'height' => $this->height,
            'created_at' => $this->created_at,
        ];
    }
}
