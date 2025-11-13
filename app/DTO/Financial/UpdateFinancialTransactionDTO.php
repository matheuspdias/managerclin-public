<?php

namespace App\DTO\Financial;

use Illuminate\Http\Request;

class UpdateFinancialTransactionDTO
{
    public function __construct(
        public string $type,
        public float $amount,
        public string $description,
        public string $transaction_date,
        public int $id_financial_account,
        public int $id_financial_category,
        public ?string $due_date = null,
        public string $status = 'PENDING',
        public ?string $payment_method = null,
        public ?string $document_number = null,
        public ?string $notes = null,
        public ?int $id_customer = null,
        public ?int $id_appointment = null,
        public ?int $id_transfer_account = null,
        public ?array $attachments = null,
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            type: $request->input('type'),
            amount: (float) $request->input('amount'),
            description: $request->input('description'),
            transaction_date: $request->input('transaction_date'),
            id_financial_account: (int) $request->input('id_financial_account'),
            id_financial_category: (int) $request->input('id_financial_category'),
            due_date: $request->input('due_date'),
            status: $request->input('status', 'PENDING'),
            payment_method: $request->input('payment_method'),
            document_number: $request->input('document_number'),
            notes: $request->input('notes'),
            id_customer: $request->input('id_customer') ? (int) $request->input('id_customer') : null,
            id_appointment: $request->input('id_appointment') ? (int) $request->input('id_appointment') : null,
            id_transfer_account: $request->input('id_transfer_account') ? (int) $request->input('id_transfer_account') : null,
            attachments: $request->input('attachments'),
        );
    }
}