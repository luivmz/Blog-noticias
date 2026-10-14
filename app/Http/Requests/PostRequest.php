<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return ['title' => 'required|string|min:5|max:150', 'summary' => 'required|string|min:10|max:300', 'content' => 'required|string|min:30', 'category_id' => 'required|exists:categories,id', 'status' => 'required|in:draft,published'];
    }

    public function attributes(): array
    {
        return ['title' => 'título', 'summary' => 'resumen', 'content' => 'contenido', 'category_id' => 'categoría', 'status' => 'estado'];
    }

    public function messages(): array
    {
        return ['required' => 'El campo :attribute es obligatorio.', 'min' => 'El campo :attribute debe tener al menos :min caracteres.', 'max' => 'El campo :attribute no debe superar :max caracteres.', 'string' => 'El campo :attribute debe ser texto.', 'exists' => 'Selecciona una categoría válida.', 'in' => 'Selecciona un estado válido.'];
    }
}
