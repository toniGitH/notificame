<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mensajes de Validación
    |--------------------------------------------------------------------------
    */
    'required' => 'El campo :attribute es obligatorio.',
    'email' => [
        'invalid_format' => 'El campo :attribute debe tener un formato válido (ejemplo@dominio.com).',
        'missing_dot' => 'El campo :attribute debe incluir un dominio con punto (ejemplo@dominio.com).',
    ],
    'min' => [
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'max' => [
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'confirmed' => 'La confirmación de :attribute no coincide.',
    'unique' => 'El :attribute ya está registrado.',
    'string' => 'El campo :attribute debe ser una cadena de texto.',
    'regex' => [
        'password' => 'La :attribute debe incluir al menos una letra mayúscula, una minúscula, un número y un carácter especial.',
        'email' => 'El :attribute debe tener un formato válido (ejemplo@dominio.com).',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'nombre',
        'email' => 'correo electrónico',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de contraseña',
    ],
];