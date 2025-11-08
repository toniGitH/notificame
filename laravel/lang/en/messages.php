<?php

return [

    'validation' => [
        'error' => 'Validation error. Please check the provided fields.',
    ],

    'user' => [
        'registered_success' => 'User registered successfully.',
        'EMAIL_ALREADY_EXISTS' => 'The email :email is already registered.',
        'EMPTY_USER_ID' => 'Internal error: User ID not generated.',
        'INVALID_USER_ID_FORMAT' => 'The user ID has an invalid format: :value',
        'INVALID_EMAIL_FORMAT' => 'The email address has an invalid format. It must have the format email@email.com',
        'MISSING_USER_NAME' => 'The name is required and must be between 3 and 50 characters.',
        'INVALID_USER_NAME' => 'The name is required and must be between 3 and 50 characters.',
        'INVALID_PASSWORD' => 'The password must contain at least 8 characters, one uppercase letter, one lowercase letter, one number and one special character (@$!%*?&).',
    ],

    'unexpected_error' => 'An unexpected error has occurred. Please try again later.',
];