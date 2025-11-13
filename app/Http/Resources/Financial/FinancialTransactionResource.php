<?php

namespace App\Http\Resources\Financial;

use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\Customer\CustomerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialTransactionResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'type'                   => $this->type,
            'amount'                 => $this->amount ? number_format((float) $this->amount, 2, '.', '') : null,
            'description'            => $this->description,
            'transaction_date'       => $this->transaction_date?->format('Y-m-d'),
            'due_date'               => $this->due_date?->format('Y-m-d'),
            'status'                 => $this->status,
            'payment_method'         => $this->payment_method,
            'document_number'        => $this->document_number,
            'notes'                  => $this->notes,
            'attachments'            => $this->attachments,
            'id_financial_account'   => $this->id_financial_account,
            'id_financial_category'  => $this->id_financial_category,
            'id_customer'            => $this->id_customer,
            'id_appointment'         => $this->id_appointment,
            'id_transfer_account'    => $this->id_transfer_account,
            'created_at'             => $this->created_at?->toIso8601String(),
            'updated_at'             => $this->updated_at?->toIso8601String(),

            // Relacionamentos
            'account'                => FinancialAccountResource::make($this->whenLoaded('account')),
            'category'               => FinancialCategoryResource::make($this->whenLoaded('category')),
            'customer'               => CustomerResource::make($this->whenLoaded('customer')),
            'appointment'            => AppointmentResource::make($this->whenLoaded('appointment')),
            'transfer_account'       => FinancialAccountResource::make($this->whenLoaded('transferAccount')),
        ];
    }
}
