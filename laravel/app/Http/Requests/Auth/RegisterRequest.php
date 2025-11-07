<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * Request para el registro de usuarios.
 *  
 * ESTRATEGIA DE VALIDACIÓN EN DOS CAPAS:
 * 
 * 1. RegisterRequest: Primera barrera (HTTP)
 *    - Filtra peticiones obviamente inválidas
 *    - Reduce carga en la capa de aplicación
 *    - Responde rápido al cliente
 * 
 * 2. Value Objects: Segunda barrera (Dominio)
 *    - Garantiza que ningún dato inválido entre al sistema
 *    - Protege contra llamadas desde otros puntos de entrada
 *    - Reglas de negocio canónicas
 * 
 * PRIMERA BARRERA DE DEFENSA:
 * Replica TODAS las reglas de validación del dominio para evitar que lleguen
 * peticiones inválidas al caso de uso.
 * 
 * FORMATO DE RESPUESTA:
 * Utiliza el mismo formato que las excepciones de dominio:
 * {
 *   "message": "Error de validación. Por favor, revisa los campos proporcionados.",
 *   "errors": {
 *     "field": ["mensaje1", "mensaje2"]
 *   }
 * }
 * 
 * Este formato es idéntico al que devuelve Handler::errorResponse() para
 * excepciones de dominio, garantizando consistencia para el frontend.
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
            // Validación del nombre
            'name' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    // Validación personalizada: el nombre no puede estar vacío después de trim
                    if (trim($value) === '') {
                        $fail(__('messages.user.MISSING_USER_NAME'));
                    }
                },
            ],

            // Validación del email
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    // Validación adicional: debe contener un punto en el dominio
                    // (Replica la lógica de UserEmail::ensureIsValidEmail)
                    if (!str_contains($value, '.')) {
                        $fail(__('messages.user.INVALID_EMAIL_FORMAT', ['email' => $value]));
                    }
                },
            ],

            // Validación de la contraseña
            'password' => [
                'required',
                'string',
                'confirmed', // Validación de password_confirmation (específica de HTTP)
                'min:8', // Longitud mínima
                // Validaciones personalizadas que replican UserPassword::ensureIsValidPassword
                function ($attribute, $value, $fail) {
                    $errors = [];

                    // Debe contener al menos una mayúscula
                    if (!preg_match('/[A-Z]/', $value)) {
                        $errors[] = __('messages.password.PASSWORD_MISSING_UPPERCASE');
                    }

                    // Debe contener al menos una minúscula
                    if (!preg_match('/[a-z]/', $value)) {
                        $errors[] = __('messages.password.PASSWORD_MISSING_LOWERCASE');
                    }

                    // Debe contener al menos un número
                    if (!preg_match('/[0-9]/', $value)) {
                        $errors[] = __('messages.password.PASSWORD_MISSING_NUMBER');
                    }

                    // Debe contener al menos un carácter especial
                    if (!preg_match('/[@$!%*?&]/', $value)) {
                        $errors[] = __('messages.password.PASSWORD_MISSING_SPECIAL');
                    }

                    // Si hay errores, fallar con todos los mensajes
                    foreach ($errors as $error) {
                        $fail($error);
                    }
                },
            ],

            // password_confirmation es requerido pero no necesita validaciones adicionales
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Mensajes personalizados para algunas validaciones básicas.
     * Las validaciones personalizadas ya usan los mensajes del dominio.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('messages.user.MISSING_USER_NAME'),
            'email.required' => __('messages.user.INVALID_EMAIL_FORMAT', ['email' => '']),
            'email.email' => __('messages.user.INVALID_EMAIL_FORMAT', ['email' => $this->input('email', '')]),
            'password.required' => __('messages.password.PASSWORD_TOO_SHORT', ['min' => 8]),
            'password.min' => __('messages.password.PASSWORD_TOO_SHORT', ['min' => 8]),
            'password.confirmed' => 'La contraseña y su confirmación no coinciden.',
            'password_confirmation.required' => 'La confirmación de contraseña es obligatoria.',
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
            'password_confirmation' => $this->input('password_confirmation', ''),
        ]);
    }

    /**
     * Maneja un intento de validación fallido.
     * 
     * CRÍTICO: Sobrescribe el comportamiento por defecto de Laravel para
     * devolver el MISMO formato que las excepciones de dominio.
     * 
     * Formato devuelto:
     * {
     *   "message": "Error de validación. Por favor, revisa los campos proporcionados.",
     *   "errors": {
     *     "field": ["mensaje1", "mensaje2"]
     *   }
     * }
     * 
     * Este formato es IDÉNTICO al de Handler::errorResponse()
     */
    protected function failedValidation(Validator $validator)
    {
        // Obtener errores agrupados por campo (formato: ['field' => ['msg1', 'msg2']])
        $errors = $validator->errors()->toArray();
        
        // Construir respuesta con el mismo formato que las excepciones de dominio
        throw new HttpResponseException(
            new JsonResponse([
                'message' => __('messages.validation.error'), // Mismo mensaje que Handler
                'errors' => $errors // Mismo formato que Handler
            ], 422) // Mismo código de estado que Handler para validaciones
        );
    }
}
