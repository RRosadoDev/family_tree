<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MoveDescendantsRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'child_move_id' => 'required|exists:people,id',
            'new_father_id' => 'nullable|exists:people,id',
        ];
    }

    public function messages(): array {
        return [
            'child_move_id.required' => 'El ID de la persona a mover es obligatorio.',
            'child_move_id.exists' => 'La persona con el ID especificado para mover no existe.',
            'new_father_id.required' => 'El ID del nuevo padre es obligatorio.',
            'new_father_id.exists' => 'La persona con el ID especificado como nuevo padre no existe.',
        ];
    }
}
