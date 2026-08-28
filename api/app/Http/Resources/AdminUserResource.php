<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'roles' => $this->getRoleNames(),
            'suspended' => $this->isSuspended(),
            'suspended_at' => $this->suspended_at,
            'store' => new AdminStoreResource($this->whenLoaded('store')),
            'created_at' => $this->created_at,
        ];
    }
}
