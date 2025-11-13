<?php

namespace App\Http\Resources\Financial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialAccountResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'type'            => $this->type,
            'bank_name'       => $this->bank_name,
            'account_number'  => $this->account_number,
            'initial_balance' => $this->initial_balance ? number_format((float) $this->initial_balance, 2, '.', '') : null,
            'current_balance' => $this->current_balance ? number_format((float) $this->current_balance, 2, '.', '') : null,
            'is_active'       => $this->is_active,
            'description'     => $this->description,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
