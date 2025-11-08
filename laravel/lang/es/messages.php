<?php

return [
    'validation' => [
        'error' => 'Error de validación. Por favor, revisa los campos proporcionados.',
    ],

    'user' => [
        'registered_success' => 'Usuario registrado exitosamente.',
        'EMAIL_ALREADY_EXISTS' => 'El correo electrónico :email ya está registrado.',
        'INVALID_EMAIL_FORMAT' => 'El correo electrónico tiene un formato inválido. Debe tener el formato email@email.com',
        'INVALID_USER_NAME' => 'El nombre es obligatorio y debe tener entre 3 y 50 caracteres.',
        'INVALID_PASSWORD' => 'La contraseña debe contener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial (@$!%*?&).',
        'MISSING_USER_NAME' => 'El nombre es obligatorio y debe tener entre 3 y 50 caracteres.',
        'EMPTY_USER_ID' => 'Error interno: ID de usuario no generado.',
        'INVALID_USER_ID_FORMAT' => 'El ID de usuario tiene un formato inválido: :value',
    ],

    'unexpected_error' => 'Ha ocurrido un error inesperado. Por favor, inténtalo de nuevo más tarde.',
];