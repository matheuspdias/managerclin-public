<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryProductResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'id_category'           => $this->id_category,
            'id_supplier'           => $this->id_supplier,
            'name'                  => $this->name,
            'code'                  => $this->code,
            'barcode'               => $this->barcode,
            'description'           => $this->description,
            'unit'                  => $this->unit,
            'current_stock'         => $this->current_stock ? number_format((float) $this->current_stock, 2, '.', '') : null,
            'minimum_stock'         => $this->minimum_stock ? number_format((float) $this->minimum_stock, 2, '.', '') : null,
            'maximum_stock'         => $this->maximum_stock ? number_format((float) $this->maximum_stock, 2, '.', '') : null,
            'cost_price'            => $this->cost_price ? number_format((float) $this->cost_price, 2, '.', '') : null,
            'sale_price'            => $this->sale_price ? number_format((float) $this->sale_price, 2, '.', '') : null,
            'expiry_date'           => $this->expiry_date?->format('Y-m-d'),
            'batch_number'          => $this->batch_number,
            'storage_location'      => $this->storage_location,
            'requires_prescription' => $this->requires_prescription,
            'controlled_substance'  => $this->controlled_substance,
            'active'                => $this->active,
            'created_at'            => $this->created_at?->toIso8601String(),
            'updated_at'            => $this->updated_at?->toIso8601String(),

            // Relacionamentos
            'category'              => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'supplier'              => $this->whenLoaded('supplier', function () {
                return [
                    'id'   => $this->supplier->id,
                    'name' => $this->supplier->name,
                ];
            }),
        ];
    }
}
