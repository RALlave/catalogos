<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class AdminStoreResource extends StoreResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'owner' => new AdminUserResource($this->whenLoaded('user')),
            'categories_count' => $this->whenCounted('categories'),
            'products_count' => $this->whenCounted('products'),
            'updated_at' => $this->updated_at,
        ]);
    }
}
