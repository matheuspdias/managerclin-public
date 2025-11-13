<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $validPlanKeys = array_keys(config('services.stripe.prices.plans', []));

        return [
            'plan_id' => 'nullable|string|in:' . implode(',', $validPlanKeys),
            'additional_users' => 'nullable|integer|min:0|max:100',
            'payment_method_id' => 'nullable|string|regex:/^pm_[a-zA-Z0-9]+$/',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.in' => 'O plano selecionado não é válido.',
            'additional_users.max' => 'O número máximo de usuários adicionais é 100.',
            'payment_method_id.regex' => 'O método de pagamento fornecido não é válido.',
        ];
    }
}
