<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para el registro de usuarios.
 * 
 * NO realiza validaciones, solo sanitización básica y confirmación de password.
 * Todas las validaciones de negocio se realizan en el dominio.
 * 
 * IMPORTANTE: La única validación que se mantiene es 'confirmed' para password,
 * porque es específica de la interfaz HTTP (compara dos campos del request).
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['confirmed'], // Solo validar que password y password_confirmation coincidan
        ];
    }

    /**
     * Prepara los datos antes de la validación.
     * Asegura que los campos existan aunque vengan vacíos.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->input('name', ''),
            'email' => $this->input('email', ''),
            'password' => $this->input('password', ''),
        ]);
    }
}