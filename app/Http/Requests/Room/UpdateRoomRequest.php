<?php

namespace App\Http\Requests\Room;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
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
        return [
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'O campo nome deve ser uma string.',
            'name.max' => 'O campo nome não pode ter mais de 255 caracteres.',
            'location.string' => 'O campo localização deve ser uma string.',
            'location.max' => 'O campo localização não pode ter mais de 255 caracteres.',
        ];
    }
}
