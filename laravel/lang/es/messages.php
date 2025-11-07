<?php

return [

    'validation' => [
        'error' => 'Error de validación. Por favor, revisa los campos proporcionados.',
    ],

    'user' => [
        'registered_success' => 'Usuario registrado correctamente.',
        'EMAIL_ALREADY_EXISTS' => 'El correo electrónico :email ya está registrado.',
        'EMPTY_USER_ID' => 'Error interno: ID de usuario no generado.',
        'INVALID_USER_ID_FORMAT' => 'El ID de usuario tiene un formato inválido: :value',
        'INVALID_EMAIL_FORMAT' => 'El correo electrónico :email tiene un formato inválido.',
        'MISSING_USER_NAME' => 'El nombre de usuario es obligatorio.',
    ],

    'password' => [
        'PASSWORD_TOO_SHORT' => 'La contraseña debe tener al menos :min caracteres.',
        'PASSWORD_MISSING_UPPERCASE' => 'La contraseña debe contener al menos una letra mayúscula.',
        'PASSWORD_MISSING_LOWERCASE' => 'La contraseña debe contener al menos una letra minúscula.',
        'PASSWORD_MISSING_NUMBER' => 'La contraseña debe contener al menos un número.',
        'PASSWORD_MISSING_SPECIAL' => 'La contraseña debe contener al menos un carácter especial (@$!%*?&).',
    ],

    'unexpected_error' => 'Ha ocurrido un error inesperado. Inténtalo de nuevo más tarde.',
];