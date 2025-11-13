<?php

namespace App\Http\Resources\Financial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialCategoryResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'color'       => $this->color,
            'icon'        => $this->icon,
            'is_active'   => $this->is_active,
            'description' => $this->description,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
