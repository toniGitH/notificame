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
        'INVALID_EMAIL_FORMAT' => 'The email address :email has an invalid format.',
        'MISSING_USER_NAME' => 'The user name is required.',
    ],

    'password' => [
        'PASSWORD_TOO_SHORT' => 'The password must be at least :min characters long.',
        'PASSWORD_MISSING_UPPERCASE' => 'The password must contain at least one uppercase letter.',
        'PASSWORD_MISSING_LOWERCASE' => 'The password must contain at least one lowercase letter.',
        'PASSWORD_MISSING_NUMBER' => 'The password must contain at least one number.',
        'PASSWORD_MISSING_SPECIAL' => 'The password must contain at least one special character (@$!%*?&).',
    ],

    'unexpected_error' => 'An unexpected error has occurred. Please try again later.',
];