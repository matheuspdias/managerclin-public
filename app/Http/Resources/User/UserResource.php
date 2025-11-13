<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'id_role'  => $this->id_role,
            'name'     => $this->name,
            'email'    => $this->email,
            'phone'    => $this->phone,
            'crm'      => $this->crm,
            'image'    => $this->image,
            'image_url' => $this->image_url,
            'is_owner' => $this->is_owner,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relacionamentos
            'role'     => $this->whenLoaded('role', function () {
                return [
                    'id'   => $this->role->id,
                    'name' => $this->role->name,
                    'type' => $this->role->type,
                ];
            }),
        ];
    }
}
