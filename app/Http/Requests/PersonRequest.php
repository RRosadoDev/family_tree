<?php

namespace App\Http\Requests;

use App\Models\Person;
use Illuminate\Foundation\Http\FormRequest;

class PersonRequest extends FormRequest {
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
        // Si hay al menos una persona en la tabla, el padre es obligatorio
        $fatherRequired = Person::where('is_active', true)->exists() ? 'required' : 'nullable';
        return [
            'id' => 'sometimes|integer|exists:people,id',
            'name' => 'required|string|max:150',
            'father_id' => $fatherRequired . '|exists:people,id',
        ];
    }

    public function messages(): array {
        return [
            'id.integer' => 'El ID debe ser un número entero.',
            'id.exists' => 'La persona con el ID especificado no existe.',
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.max' => 'El nombre no debe exceder los 150 caracteres.',
            'father_id.exists' => 'El padre especificado no existe.',
            'father_id.required' => 'Debe especificarse un padre si ya existe al menos una persona.',
        ];
    }
}
