<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Messages
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'error' => 'Validation error. Please check the provided fields.',
        'name_required' => 'The name field is required.',
        'name_min' => 'The name must be at least 3 characters.',
        'email_required' => 'The email field is required.',
        'email_invalid' => 'Please enter a valid email address.',
        'password_required' => 'The password field is required.',
        'password_min' => 'The password must be at least 8 characters.',
        'password_confirmation' => 'The password confirmation does not match.',
        'password_strength' => 'The password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Messages
    |--------------------------------------------------------------------------
    */
    'user' => [
        'registered_success' => 'User registered successfully.',
        'email_already_exists' => 'The email is already registered.',
        'empty_id' => 'Internal error: User ID not generated.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    */
    'errors' => [
        'email_already_exists' => 'Duplicate email',
    ],

    /*
    |--------------------------------------------------------------------------
    | General System Messages
    |--------------------------------------------------------------------------
    */
    'unexpected_error' => 'An unexpected error has occurred. Please try again later.',

];
